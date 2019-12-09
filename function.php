<?PHP
// michele.furlan@unipd.it  20 novembre 2019
require_once("./config.php");

function OpenCon() {   // Crea connessione al database
  $conn = @new mysqli($GLOBALS['servername'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['dbname']);
  if($conn->connect_error) 
      die("Connessione al database fallita: ".$conn->connect_error);
   
return $conn;
} // fine funzione connessione al database

function debug($str) {   // Solo per debug applicazione
$handle = fopen("debug.txt", 'a'); 	
  fwrite($handle, $str."\r\n");
  fclose($handle); 	
}  

function clean_dato($str) {     // addslaslashes per inserimento nel database
	return addslashes(trim($_REQUEST[$str]));
}

function test_is_image($tabella, $tabella_richiesta, $campo, $nomefile, $attr_img=FALSE) {   // se FALSE massima dimensione - se TRUE solo miniatura
$str = "";  
$nomefile = $tabella[$tabella_richiesta]["campi"][$campo]["attributi"]["cartella_upload"]. DIRECTORY_SEPARATOR .$nomefile;
  if(file_exists($nomefile) && exif_imagetype($nomefile)) {
     list($width, $height, $type, $attr) = getimagesize($nomefile);
       if($attr_img) {  // Fisso un limite massimo di dimensione per l'anteprima - larghezza 250px
		  if($width > 300) 
			 $attr = "width='350' height='".intval((350/$width) * $height)."'"; 
		  return "<BR /><BR /><IMG class=\"center_elemento_imma\" src=\"$nomefile\" $attr alt=\"$attr\" />";
	   }
       else {
		  $larghezza_max_miniatura = $tabella[$tabella_richiesta]["campi"][$campo]["attributi"]["larghezza_max_miniatura"];  
		  $attr = "width=$larghezza_max_miniatura height=".intval(($larghezza_max_miniatura/$width)*$height)."";  
   		  return "<IMG class='center_elemento_imma' src='".addslashes($nomefile)."' $attr alt='$attr' />";   // Genero la miniatura per l'anteprima in tabella
	   }	  
  }	 
  else  
     return "";	 // Non e' un immagine
} // fine test_is_image() 


function check_figlia_record($conn, $tabella_padre, $id) { // test se c'e' in tabella la figlia e se e' popolata
// in $id record di tabella padre da esaminare - ritorna il numero di righe
$tabelle = $GLOBALS['tabella'];
$esito = 0;

 if(!empty($tabelle[$tabella_padre]["figlia"])) {
    // Non posso eliminare record se ci sono tabelle figlie collegate
	  $result = @$conn->query("SELECT count(*) AS totale FROM ".$tabelle[$tabelle[$tabella_padre]["figlia"]]["nometabella"]." WHERE ".$tabelle[$tabelle[$tabella_padre]["figlia"]]["chiave_padre"]."=$id");
      $esito = $result->fetch_assoc();
	  $esito = $esito["totale"];
	  mysqli_free_result($result);
 }				 

return $esito;	
} // check_figlia_record

// tabella_tabellafiglia_figliaidpadre_nomecampo_id_azione 
function info_vedi_tabella($str) {  // splitta in un array 6 valori
// tabella_nomecampo_id_azione 	
$res = array(); 
$ar = array(); 
$ar = explode("-", $str);

  $res['tabella'] = check_alias_table($ar[0]) ? $GLOBALS['tabella'][check_alias_table($ar[0])]['nometabella'] : $GLOBALS['tabella'][$ar[0]]['nometabella'];
  $res['valore'] = $ar[1];
  $res['id'] = $ar[2];
  $res['azione'] = $ar[3];
	
return $res;	 
}  // fine function info_vedi_tabella


function check_alias_table($tbl) {  // la funzione testa se nella tabella alla fine e presente la keyword ALIAS se si resitituisce il nome tabella - se NO una stringa vuota
   if(substr($tbl, -5, 5) === "ALIAS")   // confronto stretto case sensitive	
	  return substr($tbl, 0, -5);
   else
      return ""; 	   
}  // fine function check_alias_table


function calcola_campo_calcolato_tabella($conn, $tabella, $id_td_campo) { // in id_td_campo  nometabella-nomecampo-id_riga-azione
$dati = info_vedi_tabella($id_td_campo);	
$str = "SELECT ".$tabella[$dati['tabella']]["campi"][$dati['valore']]["formula"]." AS ".$dati['valore']." FROM ".$dati['tabella']." WHERE id=".$dati['id']. " LIMIT 1";

  $result = @$conn->query($str);  
    if($result->num_rows == 1) {  // Solo un record per volta e' accettato
        $row = $result->fetch_assoc();
        $str = $row[$dati['valore']];
    }	 
  mysqli_free_result($result);	

return $str;  
} // fine function calcola_campo_calcolato_tabella	

function update_campo_tabella($conn, $id_input, $valore) {
// devo separare i campi		
$dati = info_vedi_tabella($id_input);
   $str = "UPDATE ".$dati['tabella']." SET ".$dati['valore']."='".htmlspecialchars($valore, ENT_QUOTES, "ISO-8859-1")."' WHERE id=".$dati['id']." LIMIT 1"; 

   if(@!$conn->query($str))
      return "Aggiornamento $id_input fallito !";	   
   else  
      return "";  // Nessun errore
}  // fine function update_campo_tabella

function genera_lista_campi_calcolati($tabella_richiesta, $tabella) {  // Se esistono campi calcolati li compongo per la select in genera_tabella
$str = ""; 
 
 foreach($tabella[$tabella_richiesta]["campi"] as $campo => $valore)  //$key => $value
   if($valore["tipo"] == "calcolato")
      $str .= ",".$valore["formula"]." AS ".$campo; 

return "*$str"; 
} // fine function genera_lista_campi_calcolati


function genera_tabella_alias($conn, $tabella_richiesta, $tabella, $id_tabella_padre) {   // genera la tabella richiesta in forma verticale senza aggiunta di pulsanti add record o elimina
$str = "<TABLE class=\"aliastable\">\n";
$result = @$conn->query("SELECT ".genera_lista_campi_calcolati($tabella_richiesta, $tabella)." FROM ".$tabella[$tabella_richiesta]['nometabella']." WHERE id = $id_tabella_padre LIMIT 1"); // query base da eseguire - LIMIT 1 massimo una riga puo' esistere

  if($result->num_rows > 0) {
	$row = $result->fetch_assoc();
        foreach($row as $key => $value) {
    		if($key == "id" OR !isset($tabella[$tabella_richiesta]['campi'][$key]))   // Se il campo non e' specificato in config.php non lo considero editabile
			    $str .= "<TR><TD>".ucfirst($key)."</TD><TD>$value</TD></TR>\n";
		}  // fine ciclo foreach

	 $str .= genera_campo_tabella($tabella_richiesta, $tabella, $row, TRUE);
     $str .= "<TH colspan=\"2\">Tabella: ".strtoupper($tabella_richiesta)."</TH></TR>\n";
  }  // fine if num_rows

  mysqli_free_result($result);
  
return $str."</TABLE>\n";
} // fine funzione genera_tabella_alias


function genera_campo_tabella($tabella_richiesta, $tabella, $riga, $alias_table = FALSE, $ultimo_record_editabile = FALSE) {   // Per singolo campo di ogni riga genera il controllo di input 
$str = ""; $str_query = "";	
$str_alias_tr_end = $alias_table ? "</TR>\n" : "";   // Necessario perche' i campi nella tabella alias in verticale
$str_alias_tr_start = "";
$str_alias_flag = $alias_table ? "ALIAS" : "";

   foreach($tabella[$tabella_richiesta]["campi"] as $campo => $valore)  {  // produco i campi editabili
    if($alias_table)
		$str_alias_tr_start = "<TR><TD>".$valore["etichetta"]."</TD>";
	if((isset($valore["editable"]) && $valore["editable"]) OR $valore["tipo"]=="calcolato" OR $ultimo_record_editabile OR ($valore["tipo"]=="file" AND strlen($riga[$campo]) > 1)) {
     switch($valore["tipo"]) { 
        case 'calcolato' : {   // I campi calcolati non sono editabili ma vanno solo aggiornati
          $str .= "$str_alias_tr_start<TD class=\"td_campo_calcolato\" id=\"".$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-campocalcolato\">".$riga[$campo]."</TD>$str_alias_tr_end";  
	    break;
       }  // fine case file	
       case 'file' : {
   	        $str_query = (strlen($riga[$campo]) > 1) ? test_is_image($tabella, $tabella_richiesta, $campo, $riga[$campo], FALSE) : "";  // Se non e' un immagine ritorna un valore vuoto
  	           if(!$str_query)  
                  $str_query = "<INPUT class=\"pulsante_file center_elemento_imma\" type=\"button\" value='".(strlen($riga[$campo]) > 1 ? $riga[$campo] : "UPLOAD FILE")."' />"; 
				 
	        $str .= "$str_alias_tr_start<TD id='".$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-listafile' onClick=\"upload_file(this.id".($ultimo_record_editabile ? ",'ultimorecord'" : "").");\">$str_query</TD>$str_alias_tr_end";  
	   break;
       }  // fine case file		
	   case 'select' : {
	       $str .= "$str_alias_tr_start<TD><SELECT id='".$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-edit' onChange=\"update_campo(this.id)\">\n";
	       $str .= "<OPTION value=\"\"".((!trim(strlen($riga[$campo])) OR !in_array($riga[$campo], array_values($valore["attributi"]))) ? " selected=\"selected\"" : "").">".$valore["attributi"]["default_vuoto"]."</OPTION>\n"; 
		        foreach($valore["attributi"] as $valore_select => $testo_select) 
	                   if($valore_select != "default_vuoto")
					      $str .= "<OPTION value=\"$valore_select\"".($valore_select == $riga[$campo] ? " selected=\"selected\"" : "").">$testo_select</OPTION>\n";
	            // Fine foreach SELECT  
	        $str .= "</SELECT></TD>$str_alias_tr_end";	
       break;
       } // Fine case select	 
	   case 'textarea' : {
            $str .= "$str_alias_tr_start<TD><TEXTAREA class=\"blue-input\" rows=\"".$valore["attributi"]["rows"]."\" cols=\"".ceil($valore["attributi"]["cols"] * ($alias_table ? 1.5:1))."\" onChange=\"update_campo(this.id);\" id='"
	             .$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-edit' maxlength=\"".$valore["attributi"]["maxlength"]."\">".$riga[$campo]."</TEXTAREA></TD>$str_alias_tr_end";  
       break;
	   }  // fine case 'textarea'
	   case 'date' : {
			   $str_query = $valore["attributi"]["pattern"] ? "pattern=\"".$valore["attributi"]["pattern"]."\" title=\"".addslashes($valore["attributi"]["error_pattern"])."\"" : "";
     		   $str .= "$str_alias_tr_start<TD style=\"text-align:center;\"><INPUT $str_query class=\"blue-input\" onChange=\"update_campo(this.id);\" type=\"date\" id='".$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-edit' value='".$riga[$campo]
			        ."' min=\"".$valore["attributi"]["min"]."\" max=\"".$valore["attributi"]["max"]."\" /></TD>$str_alias_tr_end";  
       break;
	   }  // fine case 'date'	   
	   case 'booleano' : {  // Nel booleano il valore puo' essere 0==false oppure 1==true
			 $str .= "$str_alias_tr_start<TD style=\"text-align:center;\"><INPUT class=\"blue-input\" onChange=\"this.value=(this.checked ? 1 : 0);update_campo(this.id);\" type=\"checkbox\" id='"
	              .$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-edit' value='".$riga[$campo]."' ".($riga[$campo] ? "checked" : "")." /></TD>$str_alias_tr_end";  
       break;
       }	   
	   case 'text' : {  // Se l'utente ha inserito un espressione regolare la trovo nell attributo pattern 
			   $str_query = $valore["attributi"]["pattern"] ? "pattern=\"".$valore["attributi"]["pattern"]."\" title=\"".addslashes($valore["attributi"]["error_pattern"])."\"" : "";
     		   $str .= "$str_alias_tr_start<TD><INPUT $str_query class=\"blue-input\" size=\"".ceil($valore["attributi"]["size"] * ($alias_table ? 1.5:1))."\" onChange=\"update_campo(this.id);\" type=\"text\" id='"
	               .$tabella_richiesta."$str_alias_flag-".$campo."-".$riga["id"]."-edit' value='".$riga[$campo]."' maxlength=\"".$valore["attributi"]["maxlength"]."\" /></TD>$str_alias_tr_end";  
       break;
	   }  // fine case 'text'
	   default:   // Ipotizzo che il campo non sia editabile per default
            $str .= "$str_alias_tr_start<TD>".$riga[$campo]."</TD>$str_alias_tr_end";	
    	
     }  // fine switch
	} // Fine if check editabilita del campo
    else 
      $str .= "$str_alias_tr_start<TD>".$riga[$campo]."</TD>$str_alias_tr_end";	 // campo non editabile	
  }  // Fine foreach
  
return $str;   
} // fine function genera_campo_tabella


function genera_tabella($conn, $tabella_richiesta, $tabella, $id_tabella_padre, $pagina="prima", $filtro, $ordine) {   // sono tre le tabelle possibili
// la funzione si limita a generare la tabella richiesta	
// tabella_nomecampo_id_azione
$str = "<TABLE align='left' style='border-color:".$GLOBALS['tabella_css_color'][(array_search($tabella_richiesta, array_keys($tabella)) % 3)].";'>";
$str_query = "SELECT ".genera_lista_campi_calcolati($tabella_richiesta, $tabella)." FROM ".$tabella[$tabella_richiesta]['nometabella']; // query base da eseguire
$str_div_tag = "<DIV><INPUT class=\"xml_excel_button\" type=\"button\" id=\"$tabella_richiesta-inviatabellainexcel\" value=\"&nbsp;xls Excel&nbsp;\" onClick=\"download_excel_table(this.id)\" /></DIV>";
$str_query_cond = $str_query_ord = $tmp = "";
$nrecord = $npagine = $startpagina = $conta_record = 0;
$ultimo_record = FALSE;
  
  if($pagina == "ultimaedit") {
	  $pagina = "ultima"; $ultimo_record = TRUE;
  }	  
	  
// Se in tabella_padre c'e' un chiave esterna devo filtrare la tabella
	if(!empty($tabella[$tabella_richiesta]['chiave_padre'])) 
        $str_query_cond = " WHERE ".$tabella[$tabella_richiesta]['chiave_padre']."=".$id_tabella_padre;	 

    if($tabella[$tabella_richiesta]['pulsanti']['filtro']) {
		if(isset($filtro["id_filtro"]) AND (strlen(trim($filtro["valore"])) >0 )) {  // Aggiunge la riga per filtrare
          $tmp = explode("-", $filtro["id_filtro"]);  // Nome del campo richiesto nel filtro
          $tmp = $tmp[1];	
     		if($tabella[$tabella_richiesta]["campi"][$tmp]["tipo"] == "calcolato") 
	     	    $tmp = $tabella[$tabella_richiesta]["campi"][$tmp]["formula"];
	
		  $tmp = $tmp." LIKE '%".$filtro["valore"]."%'";    // Nome del campo 
		  $str_query_cond .= ($str_query_cond) ? " AND $tmp" : " WHERE $tmp";
	    }
        if(isset($ordine["id_ordine"])  AND (strlen(trim($ordine["valore"])) >= 3 )) {  // Aggiunge la riga per filtrare 
           $tmp = explode("-", $ordine["id_ordine"]);
		   $str_query_ord = " ORDER BY ".$tmp[1]." ".$ordine["valore"];   // Nome del campo e valore ASC o DESC
        }
		else 
		   $str_query_ord = " ORDER BY id ASC";  // Forzo l'ordine del campo ID A->Z per permettere l'editing dell'ultimo campo aggiunto con ADD record nel caso in cui l'editing fosse impedito 	  
	} // Fine if pulsanti filtro
	
// conteggio dei record in tabella		   
	 $tmp = "SELECT count(*) AS totale FROM ".$tabella[$tabella_richiesta]['nometabella'].$str_query_cond;
	 $result = @$conn->query($tmp);	 
	 if($conn->error)
		 return "Query fallita: ".$tmp;
	 
	 $nrecord = $result->fetch_assoc();
	 $nrecord = $nrecord["totale"];  
     mysqli_free_result($result);

     $str .= "<TR id=\"$tabella_richiesta-numerorecord-$nrecord\">".(empty($tabella[$tabella_richiesta]['figlia']) ? "" : "<TH>VIEW</TH>").($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? "<TH>FULL</TH>" : "")."<TH>ID</TH>";
		foreach($tabella[$tabella_richiesta]["campi"] as $campo => $valore)  //$key => $value
		     $str .= "<TH>".$valore["etichetta"]."</TH>";
	
    if($tabella[$tabella_richiesta]['pulsanti']['add'])	
	   $str .= "<TH><INPUT class=\"pulsante\" type=\"button\" value=\"NEW\" id='".$tabella_richiesta."-NONE-".$id_tabella_padre."-aggiungirecord' onClick=\"add_record(this.id);\"></TH></TR>\n";
	else  
	   $str .= "<TH>NO ADD</TH></TR>\n";	
 
    if($tabella[$tabella_richiesta]['pulsanti']['filtro']) {   // Aggiunge la riga per filtrare ed ordinare 
		$str .="<TR style=\"background-color:#cbffcb;\"><TD colspan='".(1 + (empty($tabella[$tabella_richiesta]['figlia']) ? 0 : 1) + ($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? 1 : 0))."'>$str_div_tag</TD>";
		   foreach($tabella[$tabella_richiesta]["campi"] as $campo => $valore)  {  // produco i campi filtrabile
	    // Pulsanti ordinamento	 
     		 $tmp = $tabella_richiesta."-".$campo."-".$id_tabella_padre."-ASC-ordinacampo";		
		     $str .= "<TD class=\"nowrap_td\"><INPUT class=\"".((isset($ordine["id_ordine"]) && $tmp == $ordine["id_ordine"]) ? "pulsante_ord_set" : "pulsante_ord")."\" type=\"button\" id=\"$tmp\" onClick=\"ordina_campo(this.id);\"".((isset($ordine["id_ordine"]) && $tmp == $ordine["id_ordine"]) ? " alt=\"".$ordine["valore"]."\"" : "")." value=\"&#708;\" />";		     
	         $tmp = $tabella_richiesta."-".$campo."-".$id_tabella_padre."-DESC-ordinacampo";
			 $str .= "<INPUT class=\"".((isset($ordine["id_ordine"]) && $tmp == $ordine["id_ordine"]) ? "pulsante_ord_set" : "pulsante_ord")."\" type=\"button\" id=\"$tmp\" onClick=\"ordina_campo(this.id);\"".((isset($ordine["id_ordine"]) && $tmp == $ordine["id_ordine"]) ? " alt=\"".$ordine["valore"]."\"" : "")." value=\"&#709;\" />";
        // Campo filtro  solo se size_filtro > 0 		     
			if($valore["size_filtro"]) { 
			   $tmp = $tabella_richiesta."-".$campo."-".$id_tabella_padre."-filtracampo";
               $str_div_tag = (isset($filtro["id_filtro"]) AND $tmp == $filtro["id_filtro"] AND isset($filtro["valore"]) AND trim($filtro["valore"])) ? TRUE : FALSE;			   
			   if($valore["tipo"] == "booleano")  // Filtro particolare per il checkbox field type
			       $str .= "&nbsp;&nbsp;<SPAN ".($str_div_tag ? " class=\"input_filtro_attivo\"" : "")."><INPUT class=\"blue-input\" onChange=\"this.value=(this.checked ? 1 : 0);filtro_campo(this.id);\" type=\"checkbox\" id='$tmp'"
	                    ." value='".($str_div_tag  ? $filtro["valore"] : 0)."' ".($str_div_tag ? ($filtro["valore"] ? "checked" : "") : "")."/><SPAN>";
			   else 
			      $str .= "&nbsp;<SPAN ".($str_div_tag ? " class=\"input_filtro_attivo\"" : "")."><INPUT class=\"blue-input\" size=\"".$valore["size_filtro"]."\" onChange=\"filtro_campo(this.id);\" type=\"text\" id='$tmp'"
	                   ." value='".((isset($filtro["id_filtro"]) && $tmp == $filtro["id_filtro"]) ? $filtro["valore"] : "")."' ".((isset($valore["attributi"]["pattern"]) AND $valore["attributi"]["pattern"]) ? " pattern=\"".$valore["attributi"]["pattern"]."\"" : "" )." /></SPAN>";
			}
			$str .= "</TD>";
		  }  // fine foreach
	    $str .="<TD><DIV>&nbsp;</DIV></TD><TR>\n";
	} // fine if filtro tabella	
   
// per recuperare il numero di record - nell' aggiungere un record devo andare nell'ultima pagina ma verifico di non essere oltre
  if($tabella[$tabella_richiesta]["paginazione"] > 0) {
   $npagine = intval(ceil($nrecord / $tabella[$tabella_richiesta]["paginazione"]));  
	  if($pagina == "ultima") {
	      $startpagina = ($nrecord > $tabella[$tabella_richiesta]["paginazione"]) ? ($nrecord - $tabella[$tabella_richiesta]["paginazione"]) : 0;
		  $pagina = $npagine;
	  }
      elseif(is_numeric($pagina) and (($pagina * $tabella[$tabella_richiesta]["paginazione"]) > $nrecord)) {
          $pagina = $npagine;
		  $startpagina = (($nrecord - $tabella[$tabella_richiesta]["paginazione"]) > -1) ? ($nrecord - $tabella[$tabella_richiesta]["paginazione"]) : (0);	  
	  }	  
      elseif(is_numeric($pagina)) {
	      $startpagina = ($pagina -1) * $tabella[$tabella_richiesta]["paginazione"]; 
	  }	  
      else {  // Se $pagina non e' un numero o e' "prima"
	      $pagina = 1;
		  $startpagina = 0;
      }	  
  } // fine if "paginazione"

 $tmp = $str_query.$str_query_cond.$str_query_ord.(($tabella[$tabella_richiesta]["paginazione"] > 0) ? " LIMIT $startpagina, ".$tabella[$tabella_richiesta]["paginazione"] : "");
 $result = @$conn->query($tmp);	 
 if($conn->error)
	return "Query fallita: ".$tmp;  
   
	if ($result->num_rows > 0) {
		 while($row = $result->fetch_assoc()) {  // output data of each row
  		    $conta_record++;  // Primo record esaminato
			
		   if(!empty($tabella[$tabella_richiesta]["figlia"]))  // > 0 per cut tabella studenti in $row["id"] id tabella padre - se non ci sono record nella tabella figlia non ha senso il pulsante di espansione
				$str_div_tag = "<TD><DIV class=\"espandi_tabella\" id='".$tabella[$tabella_richiesta]["figlia"]."-$tabella_richiesta-".$row["id"]."-veditabella'>+</DIV></TD>";				   
		   else 
                $str_div_tag = "";  // Reset per utilizzo successivo				
		   $str .= "<TR>$str_div_tag".($tabella[$tabella_richiesta]['pulsanti']['verticale'] ? "<TD><DIV class=\"espandi_tabella_alias\" id='".$tabella_richiesta."ALIAS-$tabella_richiesta-".$row["id"]."-veditabella'>+</DIV></TD>" : "")."<TD>".$row["id"]."</TD>";
           $str .= genera_campo_tabella($tabella_richiesta, $tabella, $row, FALSE, ($conta_record == $result->num_rows AND $ultimo_record));
		
  		   if(!empty($tabella[$tabella_richiesta]["figlia"])) 
                $str_query = check_figlia_record($conn, $tabella_richiesta, $row["id"]);
		   else
                $str_query = "NO SUB";  // Non ci sono tabelle figlie			   
         
      	   $str_div_tag = "$tabella_richiesta-$id_tabella_padre-".$row["id"]."-eliminarecord";
		   if((0 == $str_query || "NO SUB" == $str_query) && $tabella[$tabella_richiesta]['pulsanti']['delete']) {
	   	        $str .= "<TD alt=\"$str_div_tag\" nodel=\"true\"><INPUT class=\"pulsante_x\" id=\"$str_div_tag\" type=\"button\" value=\"&nbsp;X&nbsp;\" onClick=\"elimina_record(this.id);\" title=\"ELIMINA IL RECORD\" /></TD></TR>\n";
		   }
		   elseif ($tabella[$tabella_richiesta]['pulsanti']['delete']) 
				   $str .= "<TD alt=\"$str_div_tag\" nodel=\"true\">SUB:&nbsp;<B>".$str_query."</B></TD></TR>\n"; 
		   else  // Non e' prevista la cancellazione dei record
               	   $str .= "<TD alt=\"$str_div_tag\" nodel=\"false\">SUB:&nbsp;<B>".$str_query."</B></TD></TR>\n"; // Attributo nodel == true per permettere il pusante di eliminazione del record)		
      }  // fine while

		 // Ora traccio i pulsanti	 di navigazione della parte bassa se e' stata richiesta la paginazione 
	  if($tabella[$tabella_richiesta]["paginazione"] > 0 AND $tabella[$tabella_richiesta]["paginazione"] < $nrecord ) {
		// Numero totale delle pagine in $npagine	
		// id = nometabella-npag-idpadre-paginazione		
		$pagina = ($pagina > $npagine ? $npagine : $pagina);
		$str .= "<TR><TD style=\"text-align:center;\" colspan='". (2 + (empty($tabella[$tabella_richiesta]['figlia']) ? 0 : 1) + ($tabella[$tabella_richiesta]["pulsanti"]["verticale"] ? 1 : 0) + count($tabella[$tabella_richiesta]["campi"])). "'>N. record: $nrecord&nbsp;&nbsp;"
               ."<INPUT class=\"pulsante_dir\" onClick=\"paginatore_tabella(this.id);\" type=\"button\" value=\"&nbsp;<<&nbsp;\" id='$tabella_richiesta-prima-".$id_tabella_padre."-paginazione' />&nbsp;"
			   ."<INPUT class=\"pulsante_dir\" onClick=\"paginatore_tabella(this.id);\" type=\"button\" value=\"&nbsp;<&nbsp;\" id='$tabella_richiesta-".(($pagina - 1) < 1 ? 1 : ($pagina -1))."-$id_tabella_padre-paginazione' />&nbsp;"
			   ."Pagina&nbsp;<INPUT alt=\"$pagina\" class=\"blue-input\" onChange=\"paginatore_tabella(this.id);\" type=\"text\" size=\"3\" value=\"".$pagina."\" id='$tabella_richiesta-campolibero-$id_tabella_padre-paginazione' />&nbsp;"
			   ."di <SPAN id=\"".$tabella_richiesta."-npagine-".$npagine."\">$npagine</SPAN>&nbsp;"
			   ."<INPUT class=\"pulsante_dir\" onClick=\"paginatore_tabella(this.id);\" type=\"button\" value=\"&nbsp;>&nbsp;\" id='$tabella_richiesta-".(($pagina + 1) >= $npagine ? $npagine : ($pagina +1))."-$id_tabella_padre-paginazione' />&nbsp;"
			   ."<INPUT class=\"pulsante_dir\" onClick=\"paginatore_tabella(this.id);\" type=\"button\" value=\"&nbsp;>>&nbsp;\" id='$tabella_richiesta-ultima-".$id_tabella_padre."-paginazione' />"		  
		       ."</TD></TR>\n";
	  }  // fine if check paginazione
	 }  // Fine if $result->num_rows
    mysqli_free_result($result);

return $str."</TABLE>\n";
}  // fine funzione genera tabella

function aggiungi_record_tabella($conn, $tabella_richiesta, $tabella, $id_tabella_padre) {
// $tabella_richiesta == tabella dove aggiungere il record nuovo, $tabella == il model con le tabelle, $id_tabella_padre == chiave esterna	
$str = "INSERT INTO ".$tabella[$tabella_richiesta]['nometabella']." ";
$valori = ""; $campi = "";

    foreach($tabella[$tabella_richiesta]["campi"] as $campo => $valore)  // composizione campi default
        if(isset($valore["attributi"]["default_value"]) AND $valore["attributi"]["default_value"]) {  // se il valore e' settato e previsto
           $campi = $campo.",";
		   $valori = "'".addslashes(trim($valore["attributi"]["default_value"]))."',";
		} 
    if($valori) {  // Se esiste almeno un valore di default tra i campi tolgo la virgola finale separatrice
	    $campi = substr($campi, -1) == "," ? substr($campi, 0, -1) : $campi; 
	    $valori = substr($valori, -1) == "," ? substr($valori, 0, -1) : $valori;
	}	
	if($id_tabella_padre == 0) // Non c'e' chiave esterna da aggiungere
	   $str .= "($campi) VALUES($valori)"; // Inserisco una riga con i valori di default
	else 
	   $str .= "(".$tabella[$tabella_richiesta]["chiave_padre"].($campi ? ",$campi" : "").") VALUES($id_tabella_padre".($valori ? ",$valori" : "").")";  // Inserisco solo un valore vuoto	
	
	if (@$conn->query($str) === TRUE)
		return true;
	else 
        return false;		 
}  // fine function aggiungi_record_tabella


/**
 * Supplementary json_encode in case php version is < 5.2 (taken from http://gr.php.net/json_encode)
 */
if(!function_exists('json_encode')) {
    function json_encode($a=false)  {
        if(is_null($a)) return 'null';
        if($a === false) return 'false';
        if($a === true) return 'true';
        if(is_scalar($a)) {
            if(is_float($a))  {  // Always use "." for floats.
                return floatval(str_replace(",", ".", strval($a)));
            }
            if(is_string($a))  {
                static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                return '"'. str_replace($jsonReplaces[0], $jsonReplaces[1], $a) .'"';
            }
            else
            return $a;
        }
        $isList = true;
        for($i = 0, reset($a); $i < count($a); $i++, next($a))  {
            if(key($a) !== $i)  {
                $isList = false;
                break;
            }
        }
        $result = array();
        if($isList) {
            foreach($a as $v) 
			         $result[] = json_encode($v);
            return '['. join(',', $result) .']';
        }
        else  {
            foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
            return '{'. join(',', $result) .'}';
        }
    }
} // fine function_exists('json_encode')
  

function elimina_record_tabella($conn, $tabella, $tabella_del, $id_del) {
$str_query = "SELECT * FROM ".$tabella[$tabella_del]["nometabella"]." WHERE id=$id_del LIMIT 1";	// LIMIT 1 x sicurezza
$result = @$conn->query($str_query);
 
 if($result->num_rows == 1 AND !$conn->connect_error) {  // Solo un record per volta e' accettato
     $row = $result->fetch_assoc();
 // Controllo la presenza di campi di tipo file per la rimozione del file nel file system
    foreach($tabella[$tabella_del]["campi"] as $campo => $valore)  {  // produco i campi 
        if($valore["tipo"]=="file" AND $row[$campo]) {  
           $str_query = dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore["attributi"]["cartella_upload"].(DIRECTORY_SEPARATOR).$row[$campo];		   
		   $str_query = (file_exists($str_query) AND unlink($str_query));  // Non emetto errori 
		}	
    } // fine foreach 
	 
	 mysqli_free_result($result);
     if(TRUE === (@$conn->query("DELETE FROM ".$tabella[$tabella_del]["nometabella"]." WHERE id=$id_del LIMIT 1")))   // LIMIT 1 per sicurezza
		return str_replace(array("{", "}"), "", implode("\n", explode(",", json_encode($row, JSON_FORCE_OBJECT))));
     else
	    return "\nERRORE in cancellazione record: ".$conn->error;
 }  // fine if num_rows 	
 else
     return "\nERRORE nella query: ".$str_query;
} // fine function elimina_record_tabella

?>