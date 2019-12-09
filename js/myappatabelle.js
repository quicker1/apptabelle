// michele.furlan@unipd.it   06 novembre 2019

function jqXHRformatErrore(jqXHR, exception) { 
    if (jqXHR.status === 0) 
        return ('Non connesso.<BR />Prego verificare la connessione di rete.');
    else if (jqXHR.status == 404) 
        return ('Pagina non trovata [404]');
    else if (jqXHR.status == 500) 
        return ('Errore interno al server [500].');
    else if (exception === 'parsererror') 
        return ('JSON richiesta analisi fallita.');
    else if (exception === 'timeout')
        return ('Time out error.');
    else if (exception === 'abort') 
        return ('Ajax richiesta abortita.');
    else 
        return ('Errore sconosciuto.<BR />' + jqXHR.responseText);
    
}  // fine function jqXHRformatErrore


function AvviaAppTabelle() {  // Inizializza le varibili ambiente - test file di configurazione e compatiblita' del browser
this.inizializzato = false;   // Defaut stato di corretta inizializzazione dell' applicazione
var objapp = this; // Per la visibilita' di inizializzato alle sub funzioni locali

this.append_finestra_modale = function () {  // In fase di inizializzazione
$(document.body).append('<!-- Finestra modale --><DIV class="mod_modal"><DIV class="mod_modal-content">' +
            '<SPAN class="mod_close-button">&times;</SPAN>' +
            '<DIV id="id_stato_aggioramento" class="div_aggiornamento"></DIV>' +
            '</DIV>' +
            '</DIV>' +
            '<!-- Fine finestra modale -->'	
           );	

  $(".mod_close-button").click(toggleModal);
  $(window).click(windowOnClick);
}  // fine function append_finestra_modale

this.reset_variabili = function() {
  window.id_div_aperti = undefined; window.id_div_aperti = new Array();  // Deposito id_div con il segno - che dovranno diventare chiusi prima di aprire le tabelle - array associativo
  window.z_index_new_table = undefined; window.z_index_new_table = 5;    // Le tabelle vengono aperte con z-index incrementale
  window.root_div_table = undefined; window.root_div_table = new Array(); 
} // fine function reset_variabili

this.check_html5 = function() {
var canvas_obj = document.createElement('canvas');
return canvas_obj.getContext ? true : false;
}

this.cerca_chiave_array = function($array, $val) {  // dato l'array associativo $array cerca la chiave che ha il valore $val
  for(var $chiave in $array) 
      if($array.hasOwnProperty($chiave)) 
	     if($array[$chiave] == $val) 
	         return $chiave;

return false;  // Valore $val non trovato
}  // fine function cerca_chiave_array


this.AvviaAppTabelle = function() {
 // Prima di tutto check se html5 compatibile
 if(!objapp.check_html5()) {
	 alert("Il tuo browser non e' HTML5 compatibile, la app non puo' essere inizializzata");
	 return false;
 } 
 
// Verifica della presenza di una altra istanza AvviaAppTabelle nel global space windows del browser
 for(let varnome in window)
     if(window.hasOwnProperty(varnome) && window[varnome] instanceof AvviaAppTabelle) {
		 alert("Una sola istanza di AvviaAppTabelle e' consentita\n" +
               "La variabile[: " + varnome + "  :] e' gia' istanziata");
     return false;			   
	 }		
 
// Variabili globali 
 if(typeof root_div_table === 'undefined') { // Prevengo ulteriori inizializzazioni
    objapp.reset_variabili();
    objapp.append_finestra_modale();     // Necessaria per gli avvisi utente
// Test della configurazione 	  
    $.ajaxSetup({async: false});         // Comunicazione sincrona per permettere l'attach degli eventi 
    var jqXHR = $.post("./testapp.php", {"tbl_azione":"verificaapp"}, function(data) {
	            	 if($.trim(data).length > 2)     // Errore di configurazione
		             	stato_aggiornamento(false, data); 
	                 else 
                        objapp.inizializzato = true;
                }).fail(function(xhr, err) { 
                        stato_aggiornamento(false, $(xhr.responseText).filter('title').get(0) + "<BR />" + jqXHRformatErrore(xhr, err)); 
                });	
	$.ajaxSetup({async: true});
 } // fine if
}();  // fine function costruttore AvviaAppTabelle	


this.CaricaTabella = function(dom_elem, tbl_nome) { // La funzione carica la tabella root in tbl_index dell'array root_div_table nell'elemento dom_elem e la inserisce nel dom_elem
  if(objapp.inizializzato) {
    if(!$("#" + dom_elem).length) {
        alert("Il nodo (ID) di costruzione della tabella non e' valido !\n\nNome nodo: " + dom_elem);
        return false;	  
    }	
	if(-1 != root_div_table.indexOf(tbl_nome)) {
		alert("Tabella: " + tbl_nome + " gia caricata nel div: " + dom_elem);
		return false;
	}
	
	root_div_table.push(tbl_nome);  // Elenco gli id dei DIV dove le tabelle padri verranno aperte: DIV id="tbl_nome_" + nome tabella padre
// reset tabella se aperta e sue variabili
    if("object" == $.type($("#tbl_nome_" + tbl_nome)))  
	     $("#tbl_nome_" + tbl_nome).remove(); // rimuove l'oggetto div che contiene le tabelle e le tabelle medesime
  
     $("#" + dom_elem).empty().append("<DIV style='z-index:" + z_index_new_table + ";' id='tbl_nome_" + tbl_nome + "'></DIV>");
	 vedi_tabella(tbl_nome);
  }  // fine if inizializzato
  else  { 	 
	 alert("Inizializzare prima correttamente l'applicazione !");
	 return false;
  }	 
return true;
}	// Fine metodo carica tabella
  
this.ScaricaTabella = function(tbl) {     // In tbl nome tabella master
 // cerco in root_div_table il valore della chiave che corrisponde a tbl
 var chiave = root_div_table.indexOf(tbl);
   if(-1 != chiave) { 
     	// Devo prima chiudere tutti i div aperti
		for(var i in window.id_div_aperti)
			  elimina_tabelle_aperte(i);
		$("#tbl_nome_" + obj).remove(); 
	    root_div_table.splice(chiave, 1);  // tolgo l'elemento tbl dalla root table
	    return true;
   }	
   else {
	   alert("La tabella: " + tbl + " non e' presente nel DOM del documento");
       return false;	 
   }		
}	// Fine metodo scarica tabella

this.Reset = function() {  // elimina tutte le tabelle aperte e azzera le variabili
  for(var obj in window.root_div_table) // elimino tutti i contenuti
      $("#tbl_nome_" + obj).remove(); 
 	 
  objapp.reset_variabili();	
}  // Fine function reset
}  // fine function AvviaAppTabelle


function stato_aggiornamento(stato, err) {  // Stato true per abilitare false diversamente - in err eventuale errore da segnalare all'utente
// Negli eventi post sincroni avviso l'utente dell'attesa	
 $("#id_stato_aggioramento").empty();
  if(stato) {
     toggleModal();
	 $("#id_stato_aggioramento").html("ATTENDERE AGGIORNAMENTO IN CORSO");
     $.ajaxSetup({async: false});  // Comunicazione sincrona per permettere l'attach degli eventi
  }
  else if(!stato && $.trim(err).length >2) {
	 // Se la finestra modale e' chiusa la devo aprire per visualizzare l'errore 
	 if($("DIV[class='mod_modal']").css("visibility") == "hidden")
		  toggleModal();
	 $("#id_stato_aggioramento").html("ERRORE tipo: <BR />" + err);
	 $.ajaxSetup({async: true});   // fine comunicazione sincrona
  }
  else {
	toggleModal();   
    $.ajaxSetup({async: true});   // fine comunicazione sincrona
  }  
} // Fine funzione stato aggiornamento


function togli_alias(tbl) {  // controlla se alias e lo toglie
  if(tbl.substr(-5, 5) === "ALIAS")
	 return tbl.substr(0, tbl.length -5);
  else
     return tbl;	  
} // fine function togli_alias


function is_alias_table(tbl) {  // controlla se alias e lo toglie
  if(tbl.substr(-5, 5) === "ALIAS")
	 return true;
  else
     return false;	  
} // fine function togli_alias


Array.prototype.GetMatchArray = function(valore) {   // Se figlia true check della tabella padre
var indice = 0;

	while(indice < this.length) {
        if((new RegExp("^(" + valore + "){1}(\-){1}[a-zA-Z0-9]+(\-){1}")).test(this[indice]))
		    return ++indice;
	    indice++;
	}	
return -1;
}  // Fine GetMatchArray


function elimina_tabelle_aperte(id_div) {  // Prima di aprire nuove tabelle elimino quelle aperte non figlie di quella gia' aperta
// ricevo in id_div il nome del div con tabella da aprire e analizzo se ci sono gia' tabelle aperte con lo stesso nome- forma id_div oggetti-tabellapadre-2-veditabella
var obj = id_div.split("-")[0];            // Nome tabella - comprese le ALIAS table
var id_div_out = "";
var indice = Math.max(1, id_div_aperti.GetMatchArray(obj));

	while(indice <= id_div_aperti.length) {
		   if(id_div_aperti[id_div_aperti.length -1].split("-")[0] == id_div.split("-")[1])
 			   break;  // Non posso eliminare la tabella padre
		   id_div_out = id_div_aperti.pop();
		   obj = id_div_out.split("-")[0];
		   z_index_new_table--;             // Abbasso lo z-index per l'apertura delle nuove tabelle
           $("#tbl_nome_" + obj).remove();  // Rimuovo l'elemento dal DOM del documento		
    }  // FINE while	

	if(id_div_out)  {
       $("#" + id_div_out).off().html("+").click(function(event) {
        	event.stopImmediatePropagation();
  	        apri_tabella(this.id);
       }); 
    }
} // fine function elimina_tabelle_aperte


function download_excel_table(id_tbl) {   // $tabella_richiesta-inviatabellainexcel
 var obj = id_tbl.split("-");   // nome tabella in obj[0]
 downloadFile("./fileupload.php?tbl_id_div=" + obj[0] + "&tipoazione=downloadxls");
} // fine funzione download_excel_table


function setting_tabella(tbl, id, tipo) {   // restituisco l'oggetto ricercato in id_div dati tabella interessata in tipo - ordine - filtro - pagina
var val = "";    // Valore di ritorno - "" default

 switch (tipo) {
   case "ordine":   // Recupero i valori dei pulsanti di ordinamento
     // Nell'attributo alt ho il valore attuale di id_ordine - l attributo alt e' sempre presente
      $("INPUT[type='button'][id^='" + tbl + "'][id$='-ordinacampo']").each(function() {
         if($.type($(this).attr("alt")) == "string" && $(this).attr("alt").length > 2) {
	         val = {id_ordine: $(this).attr("id"), valore: $(this).attr("alt")};	
             return false; // esco dalla funzione each - ho trovato il campo con il valore da preservare
         }	  
      });  // fine ciclo sui campi ordine
	
   break;
   case "filtro":  // Recupero i valori dei campi filtro
   // $tabella_richiesta."-".$campo."-".$id_tabella_padre."-filtracampo";
      $("INPUT[type='text'][id^='" + tbl + "'][id$='" + id + "-filtracampo']").each(function() {
         if($.trim($(this).val()).length > 0) {
	        val = {id_filtro: $(this).attr("id"), valore: $.trim($(this).val())};	
            return false; // esco dalla funzione each - ho trovato il campo il valore da preservare	
         }	  
      });  // fine ciclo sui campi filtro
 
   break;
   case "pagina":  // Recupero i valori dei campi filtro
      val = $("INPUT[type='text'][id^='" + tbl + "-campolibero-']");  // $tabella_richiesta-campolibero
      val = ($.type(val) == "object" && ($.type(parseInt(val.attr("alt"), 10)) == "number")) ? val.attr("alt") : "prima";

   break;
   default:
      val = "";
   break;
 }  // fine switch id_div

return val;
}  // fine function valore_setting_tabella


function paginatore_tabella(id_div) {  // Nella tabella corrente si sposta del numero di pagine richieste
//id_div = nometabella-npag-idpadre-paginazione	oppure solo un numero
var obj = id_div.split("-");	
var ordine = "";	
	
	if(obj[1] == "campolibero" && (isNaN(1 * $('#' + id_div).val()) || $('#' + id_div).val() < 1)) {
	   alert("Inserire un valore numerico positivo");
       $('#' + id_div).val($('#' + id_div).attr("alt"));   // undo del valore
	   return false;   // non eseguo la paginazione perche' valore pagina nel campo libero inconsistente
	}
	if(!isNaN(parseInt($('#' + id_div).val(), 10)))        // Il parametro e' quello centrale a campo libero
		obj[1] = parseInt($('#' + id_div).val(), 10);
  
vedi_tabella(obj[0], obj[2], obj[1], setting_tabella(obj[0], obj[2], "filtro"), setting_tabella(obj[0], obj[2], "ordine")); 	 
}  // fine function paginatore_tabella


function test_se_regex(str) {  // verifica la regolarita di un espressione regolare
var isValid = true;
   try {
       new RegExp(str);
   } catch(e) {
       isValid = false;
   }
return isValid;   
} // fine function test_se_regex


function update_campo(id_val) { // Si limita a vedere la tabella padre o figlia che sia
var obj = id_val.split("-");    // nometabella-nomecampo-id_riga-azione
var valore_campo = $("#" + id_val).val();

// Prima di aggiornare il dato verifico se coerente con il pattern nel caso di campi input di tipo testo
  if($.type($("#" + id_val).attr("pattern")) === "string" && $.type($("#" + id_val).attr("title")) === "string") {
    if($("#" + id_val).attr("pattern").length) {  // Se esite un contenuto nel pattern testo l'espressione regolare	
	  if(test_se_regex($("#" + id_val).attr("pattern"))) {
    	   if($("#" + id_val).prop("validity").patternMismatch) {  // patternMismatch HTML 5
			   alert("Errore tipo dato nel campo " + obj[1] + ":\n" + $("#" + id_val).attr("title"));   
               return false;
		   }  // fine if test()
	  } 	  
	  else {
		 alert("Formato dell'espressione regolare non valido !\n" + $("#" + id_val).attr("pattern"));
         return false;
	  }
    } // Fine if ("pattern").length
  }  // fine if pattern == string
	

  if(is_alias_table(obj[0])) { // E' una tabella tab alias
     id_val = togli_alias(obj[0]) + "-" + obj[1] + "-" + obj[2] + "-" + obj[3];  // normalizzo id_val
	 obj = $("#" + id_val);
	   if($.type(obj) !== "undefined") { 
		   obj.val(valore_campo);  // Set del valore gemello in tabella tabulare se esiste che automaticamente aggiorna il campo
		   cambia_checkbox(obj, valore_campo);
		   update_campo(id_val);  // L'aggiornamento lo riservo solo alla tabella tabulare - ricorsione
	   }
  }	 
  else  {
    $.post("./crud.php", { tbl_azione: "edit", id_input: id_val, valore: valore_campo}, function(data) {
        if($.trim(data).length > 2) 
	  	  alert("Errore in aggiornamento del campo:\n" + "record id_div: " +  id_val);
    }).always(function() {  // Eseguo al termine dell'aggiornamento del campo
// Se nella stessa riga sono presenti campi calcolati li devo aggiornare - anche nella alias table
      $("TD[id^='" + obj[0] + "-'][id$='-" + obj[2] + "-campocalcolato']").each(function() {
	     $(this).empty();
		 $(this).load("./crud.php", {"id_input": $(this).attr("id"), "tbl_azione": "calcola"}, function(responseTxt, statusTxt, xhr){
		      if(statusTxt == "error")
                  stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText); 
              else {
				  obj = $(this).attr("id").split("-");
                  id_val = obj[0] + "ALIAS-" + obj[1] + "-" + obj[2] + "-campocalcolato";  // normalizzo id_val
	              obj = $("#" + id_val);
	              if($.type(obj) !== "undefined") {
                   	  obj.empty();    
		              obj.html($(this).html());  // Set del valore gemello in tabella tabulare se esiste che automaticamente aggiorna il campo
                  }    
     	     }	// Fine else
		
	    });  // Fine load crud.php
     });  // fine each TD
// Vedo se esiste la tab alias aperta per l'aggiornamento
	   id_val = obj[0] + "ALIAS-" + obj[1] + "-" + obj[2] + "-" + obj[3];
	   if($.type("#" + id_val) !== "undefined") {
		   cambia_checkbox($("#" + id_val), valore_campo); 
		   $("#" + id_val).val(valore_campo);
	   }	   
     }).fail(function(xhr, err) { 
               stato_aggiornamento(false, $(xhr.responseText).filter('title').get(0) + "<BR />" + jqXHRformatErrore(xhr, err)); 
     }); // Fine $.post()
 
  }  // Fine else $.post() 	
  

  function cambia_checkbox($obj_chk, $stato) {   // Funzione interna per checkbok
	 if($obj_chk.attr("type") == "checkbox") 
	   $obj_chk.prop('checked', $stato == 1 ? true : false);
  }  
} // fine function update_campo


function downloadFile(urlToSend) {
 var req = new XMLHttpRequest();
     req.open("GET", urlToSend, true);
     req.responseType = "blob";
     req.onload = function (event) {
       var blob = new Blob([req.response], {type: "application/octet-stream"});
       var disposition = req.getResponseHeader("Content-Disposition");   // nome file in  header http
    	  if (disposition && disposition.indexOf('attachment') !== -1) {
             var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
             var matches = filenameRegex.exec(disposition);
               if (matches != null && matches[1])  
                   fileName = matches[1].replace(/['"]/g, '');
               else
				   fileName = "file_download_binary";
          }
	  var link = document.createElement('a');
          link.href = window.URL.createObjectURL(blob);
		  link.download = fileName;
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
		  window.URL.revokeObjectURL(link.href);
     };
	 req.send();
}  // fine function downloadFile


function upload_file(id_div, tipo, msg_err) {   // Apre la gestione del sistema di upload del file  
// tipo == listafile - uploadedfile - removefile - sendfile - ultimorecord (uploaded file e' il comando che mostra la schermata dopo un upload avvenuto con successo
var tipo = tipo || "listafile";  // default
var msg_err = msg_err || "";

  if(tipo == "listafile" || tipo == "ultimorecord") toggleModal();  // Attivo finestra modale - per uploadedfile e removefile sendfile e' gia' attiva
  
  if(tipo == "sendfile") {  // URL?var1=value1&var2=value2
	downloadFile("./fileupload.php?tbl_id_div=" + id_div + "&tipoazione=sendfile");
     // Nascondo il pusante di download 
	 if($.type($("#" +  id_div + "-sendfile")) == "object")
		$("#" +  id_div + "-sendfile").css("display", "none");
  }
  else {
     $("#id_stato_aggioramento").empty().load("./fileupload.php", {"tbl_id_div": id_div, "tipoazione": tipo}, function(responseTxt, statusTxt, xhr){
        if(statusTxt == "error")
           stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
        else
	      if($.type($("#nomefile_status_span") == "object")) {
			 var obj = id_div.split("-");
			 var is_alias = (is_alias_table(obj[0]) && $.type($("#" + id_div)) == "object") ? true : false;
			 var id_norm = is_alias ? (togli_alias(obj[0]) + "-" + obj[1] + "-" + obj[2] + "-" + obj[3]) : null;  // normalizzo id_val
			 if(tipo != "listafile" && tipo != "uploadedfile") {  // E stato eseguito un remove file
			     $("#" + id_div).html($("#nomefile_status_span").html());  // aggiorno il valore in tabella con il nome file
		          if(is_alias)
					  $("#" + id_norm).html($("#nomefile_status_span").html());
			 }
			 if(tipo == "uploadedfile") {
				if(typeof(nomefilejs) == "undefined" && msg_err && $.type($("#output_file")) == "object")   // Se c'e' stato un errore in upload del file lo emetto  
				   $("#output_file").html(msg_err);
                else if(typeof(nomefilejs) != "undefined")  {   // potrebbe essere una tabALIAS - devo aggiornare entrambe le tabelle
				   $("#" + id_div).html(nomefilejs);
        	    	 if(is_alias)  // E' una tabella tab alias devo aggiornare anche la tabella tabulare
                         $("#" + id_norm).html(nomefilejs);  // Set del valore gemello in tabella tabulare se esiste che automaticamente aggiorna il campo
    		       nomefilejs = undefined;   // per evitare successivo riutilizzo !
				}	
			 }  // fine if uploaded file  
		  }  // fine if == "object"   
     });  // Fine load
  }
} // fine function upload file	


function chiudi_tabella(id_div) {  // Chiude su evento mouse nei segni -
   elimina_tabelle_aperte(id_div);
   set_elimina_add_record(false);

   $("#" + id_div).mouseup(function(event) {
  	   event.stopImmediatePropagation();
       $(this).click(function(event) {
		   event.stopImmediatePropagation();
	       apri_tabella(this.id);
       });
   });  // fine evento mouseup
}  // fine function chiudi_tabella


function set_elimina_add_record(stato) { // disabilito/abilito i pulsanti di nuovo ed eliminazione record - true disabilito <> false riabilito
// aggiungirecord ed eliminarerecord tutti alla fine degli input type button
  $("input[type='button'][id$='-ordinacampo'],input[type='button'][id$='-listafile'],input[type='button'][id$='-aggiungirecord'],input[type='button'][id$='-eliminarecord'],input[id$='-paginazione'],input[id$='-filtracampo'],input[id$='inviatabellainexcel']").each(function(index) {
      if(stato) {
		  if($.type($(this).attr("disabled")) != "string")   // Set dell'attributo se esiste
	         $(this).attr("disabled", "disabled");
	  }	  
	  else 
	      $(this).removeAttr('disabled'); 	
  });
} // fine function set_elimina_add_record


function apri_tabella(id_div) {    // su click apre tabella figlia e crea prima il relativo contenitore
 var obj_div = $("#" + id_div);
 var obj = id_div.split("-");
 var appeso = null;
   obj_div.off();                   // Elimino gli eventi perche' dopo aver aperto la tabella vado in off
   tbl_obj = "tbl_nome_" + obj[0];  // in obj[0] nome della tabella figlia da aprire - c'e' sempre la root table corrispondente
   elimina_tabelle_aperte(id_div);  // Se ci sono tabelle gia' aperte con lo stesso nome le elimino comprese le tabelle padri
   set_elimina_add_record(true);    // disabilito i pulsanti padre di aggiunta ed eliminazione record

   $("#tbl_nome_" + obj[1]).append("<DIV style=\"position:absolute;display:none;\" id='" + tbl_obj + "'></DIV>");  // contenitore tabella un contenitore per ogni riga che deve essere univoco
   appeso = $("#" + tbl_obj);
   $(appeso).css("z-index", ++z_index_new_table);     
// Posiziono l'elemento nuovo contenitore della tabella
 var tx = obj_div.position().top + parseInt(obj_div.css("height"), 10); 
 var sx = obj_div.position().left + parseInt(obj_div.css("width"), 10);
   $(appeso).offset({top:tx,left:sx});

   id_div_aperti.push(id_div);     // Salvo id div con - per trasformali in + se apro tabelle
   vedi_tabella(obj[0], obj[2]);   // e nel contempo attacca gli eventi di apertura tabelle figlie
   $(appeso).fadeIn("fast");

   obj_div.html("--").mouseup(function(event) {   // Cambio nel segno meno
       event.stopImmediatePropagation();
       // Elimino gli eventi perche' dopo aver aperto la tabella vado in off
          $(this).off().click(function(event) {
	          event.stopImmediatePropagation();
              chiudi_tabella(this.id);
          });
   });
} // fine apri_tabella


function attacca_eventi() {  // su tutti id con azione edit - veditabella DA ELIMINARE
// Attacco l'evento vedi tabella sui DIV con contenuto segno +
  $("DIV").each(function(index) {
  // Nel caso di ultima tabella non esiste la croce di espansione tabella figlia - verifico se esiste l'ID
    if($(this).attr("id") !== undefined) {
      var obj = $(this).attr("id").split("-");

      var eventObject = $._data($(this).get(0), 'events');
       if(!($.type(eventObject) != "undefined" && $.type(eventObject.click) != "undefined") && obj[obj.length -1] == "veditabella") {
	      $(this).click(function(event) {
                 event.stopImmediatePropagation();
  	             apri_tabella(this.id);
	      }); // Fine click
       }	
    }  // Fine if != undefined
  });  // Fine DIV each
  
}  // fine funzione attacca_eventi 


function vedi_tabella(tbl, id, pagina, filtro, ordine) {  // Si limita a vedere la tabella padre o figlia che sia in id chiave tabella padre (vedi i click su +)
 id = id || 0;           // 0 significa che la tabella che apro non e' figlia di altre   
 pagina = pagina || "";  // set default value per la richiesta di pagina nel caso fosse attivo il paginatore
 filtro = filtro || "";
 ordine = ordine || "";
 
 stato_aggiornamento(true, "");   // Avviso utente attessa e async ajax false
   // Svuoto il div
   $("#tbl_nome_" + tbl).empty().load("./crud.php", {"tbl_azione":"veditabella", "tbl_nome":tbl, "tbl_id":id, "pagina":pagina, "filtro":filtro, "ordine":ordine}, function(responseTxt, statusTxt, xhr){
       if(statusTxt == "success") {
   		  stato_aggiornamento(false, "");  // Chiude la finestra modale
			// Non faccio lo scrool automatico nelle tabelle master
            if(-1 == root_div_table.indexOf(tbl)) {			
			    setTimeout(function() {
		  				try {  // try perche' document.body.clientHeight non supportato da tutti i browser
			 			  if(($(document).scrollTop() + document.body.clientHeight) < ($("#tbl_nome_" + tbl).position().top + $("#tbl_nome_" + tbl ).height())) {
							  $('html, body').animate({scrollTop: $("#tbl_nome_" + tbl).position().top + $("#tbl_nome_" + tbl ).height() - document.body.clientHeight}, "slow");
					      }
						}  // fine try
                        catch(err) {
                          $.noop();
                        }
		        }, 80);  // millisecondi
		    }  // fine if no scrool 
	   }  // fine if status	   
	   if(statusTxt == "error")
          stato_aggiornamento(false, xhr.status + ":<BR />" + xhr.statusText);
   });

attacca_eventi(); // solo su tutti i figli del contenitore della nuova tabella creata
} // fine function vedi_tabella


function aggiorna_colonna_ad_record(tbl, id_padre) {  // in tbl nome della tabella interessata all'operazione A(add) o D(delete)  id_padre - non eseguito per tab alias
// Dopo le operazioni di aggiunta od eliminazione del record devo fare il refresh dello stato nella tabella padre di quella aggiornata	
var tbl_padre = id_div_aperti.GetMatchArray(tbl);

   if(-1 != tbl_padre) {   // ci sono tabelle padri da aggiornare e ne traggo il nome
      tbl_padre = id_div_aperti[tbl_padre -1].split("-")[1];
	  var obj = $("TD[alt|='" + tbl_padre + "'][alt$='-" + id_padre + "-eliminarecord']");
      var obj_record = $("TR[id^='" + tbl + "-numerorecord']");   // Array con tutte le righe TR  l' attributo record identifica il tipo di riga che contiene il record
     	 
	  if($.type(obj) == "object" && $.type(obj_record) == "object") {  // solo se esiste il tag id in TD posso gestire l'eliminazione record e l'indicatore tabella figlia
	     obj.empty();
	     obj_record = obj_record.attr("id").split("-")[2];     // Nel terzo array c'e' il numero dei record 
		   if(obj_record == 0 && "true" == obj.attr("nodel"))  // posso abilitare il segno elimina esite un solo record che e' quella della TR intestazione
    	      obj.html("<INPUT class=\"pulsante_x\" id=\"" + obj.attr("alt") + "\" type=\"button\" value=\"&nbsp;X&nbsp;\" onClick=\"elimina_record(this.id);\" title=\"ELIMINA IL RECORD\" />");
           else  // Ci sono dei record 
	          obj.html("SUB:&nbsp;<B>" + (obj_record)  + "</B>");
      } // fine if verifica se oggetto  
   } // fine if -1	
} // fine function aggiorna_colonna_ad_record


function elimina_record(id_del) {  // Riceve l'id del record da cancellare - nometabella-id_tabella_padre-id_record_da_eliminare_comando
var obj = id_del.split("-");
var err = "";
var obj_pagina = null;
  
  stato_aggiornamento(true, "");  // eliminazione sincrona
  $.post("./crud.php", {"tbl_azione" : "eliminarerecord", tbl_nome: obj[0], tbl_id: obj[2]}, function(data) {
        err = data;
  });
  stato_aggiornamento(false, "");

  vedi_tabella(obj[0], obj[1], setting_tabella(obj[0], obj[1], "pagina"));   // in obj[1] ci deve essere l'id della tabella padre !
  aggiorna_colonna_ad_record(obj[0], obj[1]);
  alert("CANCELLATO record:\n\n" + err);  // Informativa utente stato cancellazione
} // fine elimina_record


function add_record(id_new) {  // Aggiunta nuovo record nella tabella richiesta
// tabella-NONE-id_tabellapadre-azione
var obj = id_new.split("-");
var err = ""; // Se OK in update stringa vuota

  stato_aggiornamento(true, "");
  $.post("./crud.php", {"tbl_azione": "aggiungirecord", "tbl_nome": obj[0], "tbl_id": obj[2]}, function(data) {
        err = data;
  });	
  stato_aggiornamento(false, err);

  if(err.length < 2) vedi_tabella(obj[0], obj[2], "ultimaedit");
  aggiorna_colonna_ad_record(obj[0], obj[2]);  
}  // fine function add_record


function filtro_campo(id_div) {   // Riceve l'id con il campo da filtrare
var obj = id_div.split("-");
var filtro = {"id_filtro": id_div, "valore": $.trim($('#' + id_div).val())};

	vedi_tabella(obj[0], obj[2], setting_tabella(obj[0], obj[2], "pagina"), filtro, setting_tabella(obj[0], obj[2], "ordine"));  // posiziono al primo record dell'eventuale paginazione
}  // fine function filtro_campo


function ordina_campo(id_div) {  // riceve in id_div comando di ordinazione del campo
// in id_div tabella_richiesta-campo-id_tabella_padre-(ASC o DESC)-ordinacampo;	
var obj = id_div.split("-");
var ordine = {id_ordine: id_div, valore: obj[3]};
// devo preservare la paginazione che mantengo inviando il numero di pagina  
   vedi_tabella(obj[0], obj[2], setting_tabella(obj[0], obj[2], "pagina"), setting_tabella(obj[0], obj[2], "filtro"), ordine);  // posiziono al primo record dell'eventuale paginazione
}  // fine function ordina_campo


// Di seguito funzioni per finestra modale
function toggleModal() {
   $(".mod_modal").toggleClass("mod_show-modal");
}

function windowOnClick(event) {
   if (event.target.className == $(".mod_modal").attr("class"))
        toggleModal();
}

function validateEmail($email) {
var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

return emailReg.test($email);
}  // fine function validateEmail