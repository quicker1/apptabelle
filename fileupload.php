<?PHP   // michele.furlan@unipd.it   27 novembre 2019
require_once("./function.php");
$conn = OpenCon();  // Connessione al DB

// ricevo in $_REQUEST['tbl_id_div'] in forma $tabella_richiesta."-".$campo."-".$row["id"]."-listafile'
$str = "";

if(isset($_REQUEST['tbl_id_div'])) {
  $obj = explode("-", $_REQUEST['tbl_id_div']);	
  $obj[0] = check_alias_table($obj[0]) ? check_alias_table($obj[0]) : $obj[0];  // in obj[0] nome tabella in obj[1] nome del campo  
  if(count($obj) > 1 AND isset($tabella[$obj[0]]["campi"][$obj[1]]["attributi"]["cartella_upload"])) {
      $cartella_upload_result = $tabella[$obj[0]]["campi"][$obj[1]]["attributi"]["cartella_upload"];
      $cartella_upload = dirname($_SERVER['SCRIPT_FILENAME']).(DIRECTORY_SEPARATOR).$cartella_upload_result;  // $cartella_upload serve solo per l'upload dei file
   }
}
else 
  return "<BR />Errore setting mancante o autorizzazione negata";	 
  
function emissione_form() { 
?>
<SCRIPT type="text/javascript" src="./js/jquery.form.min.js"></SCRIPT>
<SCRIPT type="text/javascript">
 function test_file_presente() {   // impedisco l' invio a vuoto di file non esistente
	 if(($('#upload_file').val().length >2))
     	 $('#id_invio_form_upload_file').removeAttr('disabled');	 
 }
 
function upload_file_progressbar() {
 var bar = $('#bar1');
 var percent = $('#percent1');
  	 
  $('#myForm').ajaxForm({
     beforeSubmit: function() {
      $("#progress_div").css("display", "block");
      var percentVal = '0%';
      bar.width(percentVal);
      percent.html(percentVal);
    },

	data: {tbl_id_div: <?PHP echo "'".$_REQUEST['tbl_id_div']."'"; ?>},
	
    uploadProgress: function(event, position, total, percentComplete) {
      var percentVal = percentComplete + '%';
      bar.width(percentVal);
      percent.html(percentVal);
    },
    
	success: function() {
      var percentVal = '100%';
      bar.width(percentVal);
      percent.html(percentVal);
    },

    complete: function(xhr) {
      if(xhr.responseText) {
          $("#output_file").html(xhr.responseText);
          upload_file(<?PHP echo "'".$_REQUEST['tbl_id_div']."'"; ?>, "uploadedfile", xhr.responseText); 
	  }
    }
  });   // fine ajaxForm 
} // fine upload_file_progressbar()
  
</SCRIPT>
  
<STYLE type="text/css" media="all">
#myForm  { 
  display: block; 
  margin: 20px auto; 
  background: #eee; 
  border-radius: 10px; 
  padding: 15px 
}
.progress {
  display:none; 
  position:relative; 
  width: 100%; 
  border: 1px solid #ddd; 
  padding: 1px; 
  border-radius: 3px; 
}
.bar { 
  background-color: #B4F5B4; 
  width:0%; 
  height:20px; 
  border-radius: 3px; 
}
.percent { 
  position:absolute; 
  display:inline-block; 
  top:3px; 
  left:45%; 
}
</STYLE>
 
<DIV class="div_contenitore_upload_form"> <!-- inizio div contenitore upload_form -->
<FORM action="./fileupload.php" id="myForm" name="frmupload" method="post" enctype="multipart/form-data">
 <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $GLOBALS["massima_dimensione_file"]; ?>" />
 <BR />Invia questo file: <input class="pulsante" type="file" id="upload_file" name="upload_file" onchange="test_file_presente();" />
 <input id="id_invio_form_upload_file" disabled="disabled" class="pulsante" type="submit" name='submit_file' value="INVIA IL FILE" onclick="upload_file_progressbar();" />
</FORM>
<div class='progress' id="progress_div">
<div class='bar' id='bar1'></div>
<div class='percent' id='percent1'>0%</div>
</div>
<div id='output_file'></div>
</DIV> <!-- fine div contenitore upload_form -->

<?PHP
}  // Fine function emissione_form 

function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

if(!function_exists('mime_content_type'))  { // Mime Type Checker
 function mime_content_type($filename, $mode=0) {
    // mode 0 = full check
    // mode 1 = extension check only
    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',
        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = strtolower(array_pop(explode('.',$filename)));

    if (function_exists('finfo_open') && $mode==0) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $filename);
        finfo_close($finfo);
        return $mimetype;

    } elseif (array_key_exists($ext, $mime_types)) {
        return $mime_types[$ext];
    } else {
        return 'application/octet-stream';
    }
 } // fine function mime_content_type
} // fine function !function_exists('mime 

function get_mime_type($file) {
      if(function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
      }
      else {
        $mimetype = mime_content_type($file);
      }
      if(empty($mimetype)) $mimetype = 'application/octet-stream';
      return $mimetype;
}  // Fine function  get_mime_type


function deliverFile($file, $contentEncoding) {   // Invio del file su richieta download
  
 if (file_exists($file) && ($filehandle = fopen($file, 'rb'))) {
	
    @header('Last-Modified: ' . date('r', filectime($file)));	
    @header('Content-Description: File Transfer');
    //Get file type and set it as Content Type
	@header('Content-Type: '.get_mime_type($file));

    //Use Content-Disposition: attachment to specify the filename
   	@header('Content-Disposition: attachment; filename="'.basename($file).'"');
	@header('Content-Transfer-Encoding: binary');

    //No cache
    @header('Expires: 0');
    @header('Cache-Control: must-revalidate');
    @header('Pragma: public');

    //Define file size
    @header('Content-Length: ' . filesize($file));
	fpassthru($filehandle);
    fclose($filehandle);
 }
 else 
	 header('HTTP/1.0 404 Not Found');
 exit();
}  // fine function deliverFile

function foldersize($dirname) {
    if (!is_dir($dirname) || !is_readable($dirname)) {
        return false;
    }
    $dirname_stack[] = $dirname;
    $size = 0;
    do {
        $dirname = array_shift($dirname_stack);
        $handle = opendir($dirname);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && is_readable($dirname.(DIRECTORY_SEPARATOR).$file)) {
                if (is_dir($dirname.(DIRECTORY_SEPARATOR).$file)) {
                    $dirname_stack[] = $dirname.(DIRECTORY_SEPARATOR).$file;
                }
                $size += filesize($dirname.(DIRECTORY_SEPARATOR).$file);
            }
        }
        closedir($handle);
    } while (count($dirname_stack) > 0);
return $size;
}

function cleanData(&$str) {
  $str = preg_replace("/\t/", "\\t", $str); // escape tab
  $str = preg_replace("/\r?\n/", "\\n", $str);    // escape new lines
   
  if($str == 't') $str = 'TRUE'; // converte 't' e 'f' in boolean 
  if($str == 'f') $str = 'FALSE';

  // force certain number/date formats to be imported as strings
  if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
  }
  // escape fields that include double quotes
  if(strstr($str, '"')) 
      $str = '"' . str_replace('"', '""', $str) . '"';
}

if(isset($_REQUEST['submit_file'])) {
  if(!isset($_FILES['upload_file']['name']) OR strlen($_FILES['upload_file']['name']) <2)
	 exit();  // Interrompo perche' l'inoltro e' vuoto - non e' stato selezionato il file
  if(file_exists($cartella_upload.(DIRECTORY_SEPARATOR).$_FILES['upload_file']['name'])) 
     $str = "<H3>Il file ".$_FILES['upload_file']['name']."&nbsp; &egrave; gi&agrave; stato caricato !</H3>";
  else {
     if($_FILES['upload_file']['size'] > $massima_dimensione_file) {  // check massima dimensione del file
          $str = "<H3>Il file non &egrave; stato inviato perch&egrave; supera la dimensione massima pari a: ".human_filesize($massima_dimensione_file, $decimals = 0)."</H3>";
     }
     else {
       // Verifica della quota riservata agli upload
        if($quota_massima > (foldersize($cartella_upload) + $_FILES['upload_file']['size'])) {
	        if(UPLOAD_ERR_OK == $_FILES['upload_file']['error'] && is_writable($cartella_upload) && move_uploaded_file($_FILES['upload_file']['tmp_name'], $cartella_upload.(DIRECTORY_SEPARATOR).$_FILES['upload_file']['name'])) { // spostamento avvenuto con successo
				 $obj = explode("-", $_REQUEST['tbl_id_div']);  // tbl_id_div lo ricevo dallo script sopra nel campo data: {}
				 $obj[0] = check_alias_table($obj[0]) ? check_alias_table($obj[0]) : $obj[0];
	             $conn->query("UPDATE ".$tabella[$obj[0]]['nometabella']." SET $obj[1] = '".addslashes($_FILES['upload_file']['name'])."' WHERE id=$obj[2] LIMIT 1");  // LIMIT 1 per sicurezza
 				 $str_image_test = test_is_image($tabella, $obj[0], $obj[1], $_FILES['upload_file']['name'], FALSE);
			     if(!$str_image_test)  // Devo aggiornare il campo nella tabella che richiama la gestione file - vedi in myscript.js upload_file function
                     $str_image_test = "<INPUT class='pulsante_file center_elemento_imma' type='button' value='".$_FILES['upload_file']['name']."' />";
				 $str .= "<SCRIPT type=\"text/javascript\">var nomefilejs=\"".$str_image_test."\";</SCRIPT>";
			}
            else {
	             $str = "<H3>Errore di invio file. Codice: ".$_FILES['upload_file']['error']."</H3><BR />";
	        }
	    } // fine if verifica dimensione cartella di upload 
	    else
		   $str = "<H3>E' stata superata la quota disco disponibile di <B>".human_filesize($quota_massima, $decimals = 0)."</B>. Avvisare l'amministratore del sistema: $email_admin</H3>";
 		}    
  }  // fine else if  verifica esistenza del file gia caricato
  echo $str;
  exit();   // interrompo dopo il submit del file
} // isset($_REQUEST['submit_file'])


if(isset($_REQUEST['tbl_id_div']) AND isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == "downloadxls") {
 $output = "";
 $filecsvnome = ini_get('upload_tmp_dir').(DIRECTORY_SEPARATOR)."fileexcel.xls";  // Percorso assoluto
 if(file_exists($filecsvnome)) unlink($filecsvnome);

  if(!$handle = fopen($filecsvnome, 'w')) 
      die("Non si riesce ad aprire il file: $filecsvnome");

  $flag = false;
  $result = @$conn->query("SELECT * FROM ".$tabella[$_REQUEST['tbl_id_div']]['nometabella']." ORDER BY ID"); 
    while($row = $result->fetch_assoc()) {
       if(!$flag) { // Prima riga con nomi delle colonne
           $output .= implode("\t", array_keys($row)) . "\r\n";
           $flag = true;
       }
       array_walk($row, 'cleanData');
       $output .= implode("\t", array_values($row)) . "\r\n";
    }   
  
    mysqli_free_result($result);
    fwrite($handle, $output);
    fclose($handle);
    deliverFile($filecsvnome, "deflate");

exit();	
} // Fine if processo invio file CSV


// $tabella_richiesta."-".$campo."-".$row["id"]."-listafile'  in tbl_id_div
if(isset($_REQUEST['tbl_id_div'])) {   // ho ricevuto il comando di gestione file - tipo azioni tipo == listafile - uploadedfile - removefile - sendfile
  $obj = explode("-", $_REQUEST['tbl_id_div']);	
  $is_alias_table = check_alias_table($obj[0]) ? true : false;
  $obj[0] = $is_alias_table ? check_alias_table($obj[0]) : $obj[0];
  $obj_norm = $obj[0]."-".$obj[1]."-".$obj[2]."-".$obj[3]; // Per aggiornare il campo in tabella 
  $result = $conn->query("SELECT $obj[1] FROM ".$tabella[$obj[0]]['nometabella']." WHERE id=$obj[2] LIMIT 1");
  $nomefile = $result->fetch_assoc();
  $nomefile = stripslashes(trim($nomefile[$obj[1]]));
  $nomefile_full = $cartella_upload.(DIRECTORY_SEPARATOR).$nomefile;
  $nomefile_status_span = "<INPUT class=\"pulsante_file center_elemento_imma\" type=\"button\" value=\"UPLOAD FILE\" />";   // Valore default se manca il file
  mysqli_free_result($result);
    if(strlen($nomefile)) {
	   if(file_exists($nomefile_full)) {  // test se il file esiste nel file system 
          if(isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == "sendfile")  // E' stato chiesto di ricevere il file
		      deliverFile($nomefile_full, "deflate");	// Content-Encoding: gzip - Content-Encoding: compress - Content-Encoding: deflate - Content-Encoding: identity - Content-Encoding: br
		  elseif(isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == "removefile")  {   // Rimozione del file 
			   if(unlink($nomefile_full)) {
                  // Rimuovo anche il path dal database
				   $conn->query("UPDATE ".$tabella[$obj[0]]['nometabella']." SET $obj[1] = '' WHERE id=$obj[2] LIMIT 1");     // LIMIT 1 per sicurezza
				   $str .= "Il file <B>$nomefile</B> &egrave; stato rimosso !";
				   $str .= emissione_form();
			   } 
               else 
                   $str .= "<BR />Non &egrave; stato possibile rimuovere il file: $nomefile";	// La gestione dell'errore e' demandata in myscript.js nella function upload_file			   
		  }
		  else {
	    	 // Genero la maschera di eliminazione del file con indicate in titolo le dimensioni ed il tipo file
              if($_REQUEST['tipoazione'] == "uploadedfile")
			      $str .= "<H3>Il file: $nomefile &egrave; stato correttamente inviato</H3>";
			  $str .= "<P>Nome file: $nomefile<BR />tipo: ".mime_content_type($nomefile_full)."<BR />dimensione: ".human_filesize(filesize($nomefile_full), $decimals = 2)."</P>"; 
	        // Genero il pulsante di download
	          $str .= "<INPUT id=\"".$_REQUEST['tbl_id_div']."-sendfile\" class=\"pulsante\" type=\"button\" onClick=\"upload_file('".$_REQUEST['tbl_id_div']."', 'sendfile');\" value=\"DOWNLOAD FILE\" />";
			  if($tabella[$obj[0]]["campi"][$obj[1]]["editable"]) // Solo se editabile posso rimuove il file
			      $str .= "&nbsp;&nbsp;<INPUT class=\"pulsante\" type=\"button\" onClick=\"upload_file('".$_REQUEST['tbl_id_div']."', 'removefile');\" value=\"RIMUOVI IL FILE\" />\n";
			  $str .= test_is_image($tabella, $obj[0], $obj[1], $nomefile, TRUE);  // Mostro l'anteprima immagine estesa
	      }
      }  // fine if file_exists
	  // Se il file non esiste nel filesytem lo rimuovo e avviso l' utente
	  else {
		  $conn->query("UPDATE ".$tabella[$obj[0]]['nometabella']." SET $obj[1] = '' WHERE id=$obj[2] LIMIT 1") ;  // LIMIT 1 per sicurezza
	      $str .= "Il file <B>$nomefile</B> non esiste nella cartella <B>$cartella_upload</B> pertanto e' stato rimosso dalla tabella ".$tabella[$obj[0]]['nometabella']."</B>";
		  $str .= "<SCRIPT type=\"text/javascript\">"
		          ."$('#' + '".$_REQUEST['tbl_id_div']."').html('$nomefile_status_span');";
				  if($is_alias_table)
				      $str .= "if($.type($('#$obj_norm')) == 'object') $('#$obj_norm').html('$nomefile_status_span');";
		  $str .= "</SCRIPT>";
	  }	  
   echo "<DIV class=\"div_contenitore_upload_testo\">$str</DIV><SPAN id='nomefile_status_span' style=\"display:none;\">$nomefile_status_span</SPAN>";  // output	  
   } // fine if strlen($nomefile)
   else {  // il file non esiste o ne chiedo l'upload  compongo per l'acquisizione con la progress bar
	   if($tabella[$obj[0]]["campi"][$obj[1]]["editable"] OR (isset($_REQUEST['tipoazione']) AND $_REQUEST['tipoazione'] == "ultimorecord"))
     	   emissione_form();
   } // fine else
} // fine if isset isset($_REQUEST['tbl_id_div']

if($conn)
    $conn->close();

?>