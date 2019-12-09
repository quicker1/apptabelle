<?php  // michele.furlan@unipd.it  27 novembre 2019
// da fare: validazione numerica sul tipi numeric
// i controlli add filtro ecc... vanno riabilitati solo sulla tabella padre

// !NON MODIFICARE le sottostanti due righe - $tabella_css_color stabilisce l'alternanza dei colori dei bordi nelle tabelle
define("MEGABYTES", pow(2,20), TRUE);
date_default_timezone_set('Europe/Rome');
$tabella_css_color = array("red", "#3946f7", "#309629");
//if(!defined(DIRECTORY_SEPARATOR)) define("DIRECTORY_SEPARATOR", "/");  // UNIX == /  WIN == \	
// FINE NON MODIFICARE

$servername = "localhost";
$dbname = "apptabelle";  // nome del database MYSQL

$username = "";   // o apptabelle
$password = "";   // (quicker per test in locale) - apptab2019 su wwwdisc.chimica

// Gestione upload file
$massima_dimensione_file = MEGABYTES * 20; // dimensione massima del file in bytes - in esempio 20 MB 
$quota_massima = MEGABYTES * 300;          // dimensione massima cartella di upload in bytes  - in esempio 300MB

/*
MODELLI TIPI CAMPO :
"campi" => array("denominazione" => array("tipo" => "text", "etichetta" => "Denominazione", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 26, "maxlength" => 200, "pattern" => "", "error_pattern" => "")), 
                 "indirizzo" => array("tipo" => "select", "etichetta" => "Indirizzo", "editable" => TRUE, "size_filtro" => 12, "attributi" => array("default_vuoto" => "--------", "VV" => "Via Verdi", "VM" => "Via Monte Rosso", "VV" => "Via Volta", "VT" => "Via Termoli")),
                 "foto" => array("tipo" => "file", "etichetta" => "Foto", "editable" => TRUE, "size_filtro" => 15, "attributi" => array("cartella_upload" => "upload", "larghezza_max_miniatura" => 70)),
                 "note" => array("tipo" => "textarea", "etichetta" => "Annotazioni", "editable" => TRUE, "size_filtro" => 22, "attributi" => array("default_value" => "", "rows" => 2, "cols" => 22, "maxlength" => 200)),
				 "magazzino" => array("tipo" => "booleano", "etichetta" => "Magazzino", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "0")), 
                 "datainserimento" => array("tipo" => "date", "etichetta" => "Data acquisizione", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "2019-11-11", "max" => "2022-12-31", "min" => "1979-01-01", "pattern" => "\d{4}-\d{2}-d{2}", "error_pattern" => "immettere formato anno-mese-giorno (YYYY-MM-DD)")),
	             "calcolato1" => array("tipo" => "calcolato", "etichetta" => "IF(magazzino, 'DEPOSITO', 'UFFICIO')", "size_filtro" => 30, "formula" => "IF(magazzino, 'DEPOSITO', 'UFFICIO')")			 

*/
					 
// Modelli tabelle database formato su il database di esempio database_esempio.txt
$tabella["dip"] =   array("nometabella" => "dipartimenti",
                                 "figlia" => "stanze",
					             "chiave_padre" => "",
					             "paginazione" => 6,
			    	             "pulsanti" => array("verticale" => TRUE, "add" => TRUE, "delete" => TRUE, "filtro" => TRUE),
                                 "campi" => array("denominazione" => array("tipo" => "text", "etichetta" => "Denominazione", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 26, "maxlength" => 200, "pattern" => "", "error_pattern" => "")), 
					                              "indirizzo" => array("tipo" => "select", "etichetta" => "Indirizzo", "editable" => TRUE, "size_filtro" => 12, "attributi" => array("default_vuoto" => "--------", "VV" => "Via Verdi", "VM" => "Via Monte Rosso", "VV" => "Via Volta", "VT" => "Via Termoli", "VG" => "Via Garibaldi")),
									              "telefono" => array("tipo" => "text", "etichetta" => "Telefono", "editable" => TRUE, "size_filtro" => 13, "attributi" => array("default_value" => "", "size" => 20, "maxlength" => 100, "pattern" => "", "error_pattern" => "")), 
									              "email" => array("tipo" => "text", "etichetta" => "Email", "editable" => TRUE, "size_filtro" => 12, "attributi" => array("default_value" => "", "size" => 20, "maxlength" => 100, "pattern" => "", "error_pattern" => "")), 
				                                  "foto" => array("tipo" => "file", "etichetta" => "Foto", "editable" => TRUE, "size_filtro" => 15, "attributi" => array("cartella_upload" => "upload/foto", "larghezza_max_miniatura" => 90)),
                                                  "note" => array("tipo" => "textarea", "etichetta" => "Annotazioni", "editable" => TRUE, "size_filtro" => 22, "attributi" => array("default_value" => "", "rows" => 2, "cols" => 22, "maxlength" => 200))
							                     )
					);					 

$tabella["stanze"] = array("nometabella" => "stanze",
                           "figlia" => "oggetti",
					       "chiave_padre" => "id_dipartimento",
					       "paginazione" => 4,
			    	       "pulsanti" => array("verticale" => TRUE, "add" => TRUE, "delete" => TRUE, "filtro" => TRUE),
					       "campi" => array("piano" => array("tipo" => "text", "etichetta" => "Codice stanza", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 30, "maxlength" => 100, "pattern" => "", "error_pattern" => "")), 
                                            "magazzino" => array("tipo" => "booleano", "etichetta" => "Magazzino", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "0")),
						                    "responsabile" => array("tipo" => "text", "etichetta" => "Responsabile", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 30, "maxlength" => 100, "pattern" => "", "error_pattern" => "")),
											"calcolato1" => array("tipo" => "calcolato", "etichetta" => "IF(magazzino, 'DEPOSITO', 'UFFICIO')", "size_filtro" => 30, "formula" => "IF(magazzino, 'DEPOSITO', 'UFFICIO')")
                                           )											
				      );
				   
$tabella["oggetti"] = array("nometabella" => "oggetti",
                            "figlia" => "",
					        "chiave_padre" => "id_stanza",
					        "paginazione" => 0,
			    	        "pulsanti" => array("verticale" => TRUE, "add" => TRUE, "delete" => TRUE, "filtro" => TRUE),
							"campi" => array("descrizione" => array("tipo" => "text", "etichetta" => "Descrizione bene", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 30, "maxlength" => 100, "pattern" => "", "error_pattern" => "")), 
                                             "datainserimento" => array("tipo" => "date", "etichetta" => "Data acquisizione", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "2019-11-11", "max" => "2022-12-31", "min" => "1979-01-01", "pattern" => "\d{4}-\d{2}-d{2}", "error_pattern" => "immettere formato anno-mese-giorno (YYYY-MM-DD)")),
											 "valore" => array("tipo" => "text", "etichetta" => "Valore", "editable" => TRUE, "size_filtro" => 15, "attributi" => array("default_value" => "", "size" => 20, "maxlength" => 100, "pattern" => "", "error_pattern" => "")),
                                             "foto" => array("tipo" => "file", "etichetta" => "Pianta stanza", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("cartella_upload" => "upload/beni", "larghezza_max_miniatura" => 70))
											 )											 
				      );	

$tabella["oggettibis"] = array("nometabella" => "oggetti",
                               "figlia" => "",
					           "chiave_padre" => "",
					           "paginazione" => 0,
			    	           "pulsanti" => array("verticale" => TRUE, "add" => TRUE, "delete" => TRUE, "filtro" => TRUE),
							   "campi" => array("descrizione" => array("tipo" => "text", "etichetta" => "Descrizione bene", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "", "size" => 30, "maxlength" => 100, "pattern" => "", "error_pattern" => "")), 
                                                "datainserimento" => array("tipo" => "date", "etichetta" => "Data acquisizione", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("default_value" => "2019-11-11", "max" => "2022-12-31", "min" => "1979-01-01", "pattern" => "\d{4}-\d{2}-d{2}", "error_pattern" => "immettere formato anno-mese-giorno (YYYY-MM-DD)")),
									            "valore" => array("tipo" => "text", "etichetta" => "Valore", "editable" => TRUE, "size_filtro" => 15, "attributi" => array("default_value" => "", "size" => 20, "maxlength" => 100, "pattern" => "", "error_pattern" => "")),
                                                "foto" => array("tipo" => "file", "etichetta" => "Pianta stanza", "editable" => TRUE, "size_filtro" => 20, "attributi" => array("cartella_upload" => "upload/beni", "larghezza_max_miniatura" => 70))
											   )											 
				         );	
$tabella["calcolata"] = array("nometabella" => "oggetti",
                               "figlia" => "",
					           "chiave_padre" => "",
					           "paginazione" => 0,
			    	           "pulsanti" => array("verticale" => FALSE, "add" => FALSE, "delete" => FALSE, "filtro" => FALSE),
							   "campi" => array("calcolato1" => array("tipo" => "calcolato", "etichetta" => "Conta descrione", "size_filtro" => 30, "formula" => "count(descrizione)"),
                                                "calcolato2" => array("tipo" => "calcolato", "etichetta" => "Conta data", "size_filtro" => 30, "formula" => "count(datainserimento)"),
									            "calcolato3" => array("tipo" => "calcolato", "etichetta" => "Somma valore", "size_filtro" => 30, "formula" => "SUM(valore)"),
                                                "calcolato4" => array("tipo" => "calcolato", "etichetta" => "Conta foto", "size_filtro" => 30, "formula" => "count(foto)")
											   )											 
				         );	
	   	   
	   
?>