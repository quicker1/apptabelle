<?PHP  // michele.furlan@unipd.it  26 novembre 2019
include ("./function.php");

$conn = OpenCon();  // Connessione al DB

switch($_REQUEST['tbl_azione']) {

  case 'veditabella' : {
	if(check_alias_table($_REQUEST['tbl_nome'])) // genero la tabella verticale
	  echo genera_tabella_alias($conn, check_alias_table($_REQUEST['tbl_nome']), $tabella, $_REQUEST['tbl_id']);
	else
	  echo genera_tabella($conn, $_REQUEST['tbl_nome'], $tabella, $_REQUEST['tbl_id'], $_REQUEST['pagina'], $_REQUEST['filtro'], $_REQUEST['ordine']);
  break;
  }  // fine case veditabella
  case 'edit' : {
	 echo update_campo_tabella($conn, $_REQUEST['id_input'], clean_dato('valore'));
  break;
  }  // fine case edit
  case 'calcola' : {
	 echo calcola_campo_calcolato_tabella($conn, $tabella, $_REQUEST['id_input']);
  break;
  }  // fine case edit
  case 'aggiungirecord' : {
	  if(aggiungi_record_tabella($conn, $_REQUEST['tbl_nome'], $tabella, $_REQUEST['tbl_id']))
	     echo ""; // No problema 
      else
         echo "- ERRORE DI AGGIUNTA DEL RECORD -";		
  break;
  }  // fine case aggiungirecord
  case 'eliminarerecord' : {
	 // ritorna il record eliminato 
     echo elimina_record_tabella($conn, $tabella, trim($_REQUEST['tbl_nome']), $_REQUEST['tbl_id']);
  break;
  } // fine case eliminare record

if($conn)
   @$conn->close();  // Chiusura della connessione al DB
}  // fine switch case

?>