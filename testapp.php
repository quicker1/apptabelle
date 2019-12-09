<?PHP  // michele.furlan@unipd.it  modifica del 06 novembre 2019
// Lo script controlla la corretteza della configurazione dell'ambiente e delle impostazione dell'utente
if(!isset($_REQUEST['tbl_azione']) OR $_REQUEST['tbl_azione'] != "verificaapp")
	die("<BR /><H2>Accesso consentito solo su inizializzazione corretta</H2>");

include ("./function.php");
$conn = OpenCon();  // Connessione al DB e conseguente test connessione

$chiavi["tabella"] = array("nometabella", "figlia", "chiave_padre", "campi", "pulsanti", "paginazione");

$chiavi["campi"] = array("text", "textarea", "select", "file", "date", "booleano", "calcolato");   // Tipi di campo ammissibili
$chiavi["default_campi"] = array("tipo", "etichetta", "editable", "size_filtro", "attributi");   // Tipi attributo generali sui campi

$chiavi["text"] = array("default_value", "size", "maxlength", "pattern", "error_pattern");  // Attributi obbligatori nei campi del tipi indicato
$chiavi["textarea"] = array("default_value", "rows", "cols", "maxlength");
$chiavi["date"] = array("default_value", "min", "max", "pattern", "error_pattern");
$chiavi["file"] = array("cartella_upload", "larghezza_max_miniatura");
$chiavi["booleano"] = array("default_value");	
	
function array_multichiave_esistenza($a, $b) {   // in $a chiavi da verificare - tutte devono essere presenti in $b per avere true di ritorno
  if(is_array($a) AND count($a) > 1) {           // Il confronto lo devo fare solo con una chiave
	   $test = array_shift($a);
       return array_key_exists($test, $b) ? array_multichiave_esistenza($a, $b) : $test;  // test ricorsivo
  }	 
  else  // un elemento in $a
	   return array_key_exists($a[0], $b) ? "" : $a[0];  
}

function array_multivalore_esistenza($a, $b) { // in $a valori da verificare - tutte devono essere presenti in $b per avere true di ritorno
  if(is_array($a) AND count($a) > 1) {         // Il confronto lo devo fare solo con una chiave
	   $test = array_shift($a);
       return in_array($test, $b) ? array_multivalore_esistenza($a, $b) : $test;   // test ricorsivo
  }	 
  else  // un elemento in $a
	   return in_array($a[0], $b) ? "" : $a[0];  
}


foreach($tabella as $chiavet => $valoret) {
$str = $str_attr = "";
   $str = array_multichiave_esistenza($chiavi["tabella"], $tabella[$chiavet]);  // test primo livello su tabella
   if($str) 
	  die("Manca la seguente chiave obbligatoria in tabella: <B>$chiavet</B>: $str");  
 
// Estraggo le chiavi dalla variabile campi in tabella - serve per il test select
$str = "id"; // l' ID c'e' sempre 
  foreach($tabella[$chiavet]["campi"] as $campo => $valore) { //$key => $value
   $tipo_campo[] = $valore["tipo"];  // Per la successiva verifica del tipo di campo 
     
	 if($valore["tipo"] == "calcolato")
        $str .= ",".$valore["formula"]." AS ".$campo; 
     else  
	    $str .= ",".$campo;  
	
	 if($valore["tipo"] != "select" AND $valore["tipo"] != "calcolato") {
		 $str_attr = array_multichiave_esistenza($chiavi[$valore["tipo"]], $valore["attributi"]);
         if($str_attr)
		     die("Manca il seguente attributo nel campo <B>$campo</B> tipo ".$valore["tipo"]." in tabella: <B>$chiavet</B>: $str_attr");   
     }
	 if($valore["tipo"] == "file") {
		if(!trim($valore["attributi"]["cartella_upload"]))   // Errore per attributo vuoto senza valore - serve un cartella per l'upload dei file
		   die("Manca il seguente attributo nel campo <B>$campo</B> di tipo ".$valore["tipo"]." in tabella: <B>$chiavet</B>: cartella_upload");   
		if(!file_exists(dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore["attributi"]["cartella_upload"]) OR !is_writable(dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$valore["attributi"]["cartella_upload"]))
           die("La directory di upload <B>".$valore["attributi"]["cartella_upload"]."</B> nel campo <B>$campo</B> in tabella: <B>$chiavet</B> non esiste o non e' scrivibile"); 
     }		 
  
     // Test presenza nei campi delle chiavi setting generali comuni a tutti i campi (escluso il calcolato)
    if($valore["tipo"] != "calcolato") {
    	$str_attr = array_multichiave_esistenza($chiavi["default_campi"], $tabella[$chiavet]["campi"][$campo]);
         if($str_attr) 
           die("Nella tabella <B>$chiavet</B>  mancano le seguenti chiavi di campo: <B>".$str_attr."</B>"); 
	} 
  } // fine foreach

  $str = "SELECT $str FROM ".$tabella[$chiavet]["nometabella"];
  @$conn->query($str);
     if($conn->error)
		 die("Query fallita: ".$str);
	
// Test presenza tipo di campi
  $str = array_multivalore_esistenza($tipo_campo, $chiavi["campi"]);
   if($str) 
       die("Nella tabella $chiavet i seguenti tipi di campo non sono previsti: ".$str);  
   
// Test della correttezza catena tabella padre - figlia
   if($tabella[$chiavet]["figlia"] AND !isset($tabella[$tabella[$chiavet]["figlia"]]))
       die("Non esiste in tabella <B>$chiavet</B> la tabella figlia: ".$tabella[$chiavet]["figlia"]);	

}  // Fine foreach ciclo tabella

$conn->close();  // Chiusura della connessione al DB
?>