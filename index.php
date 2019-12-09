<HTML lang="it" dir="ltr">  <!-- michele.furlan@unipd.it  27 novembre 2019 -->
<HEAD><TITLE>Datagrid PHP + MYSQL - AppTabelle</TITLE>
<link rel="canonical" href="http://wwwdisc.chimica.unipd.it/michele.furlan/" />
<meta property="og:title" content="Datagrid PHP" />
<meta property="og:description" content="PHP Grid • Functional PHP Data Grid in minimal code • Free • Codice compatto • Documentazione • Master Detail Options" />
<meta property="og:locale" content="it_IT" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://wwwdisc.chimica.unipd.it/michele.furlan/" />
<meta property="og:site_name" content="phpGrid - PHP Datagrid" />
<meta name="author" content="michele.furlan@unipd.it">
<meta name="keywords" content="Datagrid PHP + MYSQL - AppTabelle">
<meta name="description" content="Datagrid php, master detail table"/>
<meta charset="utf-8" />
</HEAD>
 
<SCRIPT type="text/javascript" src="./js/jquery-3.4.1.min.js"></SCRIPT> <!-- Carica libreria JQuery -->
<SCRIPT type="text/javascript" src="./js/myappatabelle.js"></SCRIPT> <!-- Carica  lo script di gestione della app -->
<SCRIPT type="text/javascript">

// Solo dopo il caricamente completo della pagina si possono caricare le tabelle
$(document).ready(function() {
var objTabelle;
  objTabelle = new AvviaAppTabelle();  // Crea l''oggetto AvviaAppTabelle             
  if(objTabelle.inizializzato) {       // Solo se inizializzazione riuscita carico le tabelle
    // Invocazione del rendering tabella "dip" dentro il DIV "corpo_tabella_dip"
	 objTabelle.CaricaTabella("corpo_tabella_dip", "dip");
	// Invocazione del rendering tabella "oggettibis" dentro il DIV "corpo_tabella_oggettibis"
     objTabelle.CaricaTabella("corpo_tabella_oggettibis", "oggettibis"); 
	// Invocazione del rendering tabella "calcolata" dentro il DIV "corpo_tabella_calcolata"
     objTabelle.CaricaTabella("corpo_tabella_calcolata", "calcolata");
  }	 
}); // fine document ready

</SCRIPT>
<LINK REL="StyleSheet" HREF="./style.css" TYPE="text/css" MEDIA="screen">
<BODY>
<H2 align="center">Datagrid PHP + MYSQL - AppTabelle - Versione gratuita</H2>
<DIV style="padding:0px 30px 8px 3px;text-indent:30px;text-align:justify;">
Tabelle annidate in relazione master/detail (uno a molti), tabelle multiple master sulla stessa pagina html, inline editing dei campi, operazioni di aggiunta, cancellazione record,
upload dei file in cartelle univoche per ogni campo con indicazione percentuale upload sul server (progress bar), codice sorgente commentato e snello per una facile personalizzazione, preview file immagine...
e altro ancora. Scopritelo cliccando sul simbolo <SPAN style="background-color:#e4eff0;font-size:14pt;font-weight:bold;">+</SPAN> a lato della tabella, leggendo il manuale o installando l'applicazione.
Al fine di mantenere l’integrità del database qui pubblicato sono bloccate le operazioni sui record delle tabelle.
Installare il pacchetto completo su un proprio server per provare tutte le funzionalit&agrave; disponibili. Requisiti lato server: php >= 5.2, mysql >= 5.5.<BR />
<DIV style="text-align:center;font-size:12pt;">[<a href="./apptabelle.zip">AppTabelle.zip codice + documentazione</a> (1.6MB)]&nbsp;(versione 1.0 del 27 novembre 2019) - [<a href="./documentazione/manuale.pdf">manuale.pdf</a>], o [<a href="./documentazione/manuale.docx">manuale.docx</a>].</DIV>
</DIV>

<DIV id="corpo_tabella_dip" style="height:550px;"></DIV><BR /><BR /><!-- Finestra centrale di gestione prima -->
<DIV id="corpo_tabella_oggettibis" style="float:left;clear:left;"></DIV><!-- style="position:absolute; top:700px; "Finestra centrale di gestione seconda -->
<DIV id="corpo_tabella_calcolata" style="float:left;clear:left;"></DIV><!-- style="position:absolute; top:700px; "Finestra centrale di gestione seconda -->

<DIV style="z-index:-6;overflow-y:hidden;overflow-x:scroll;background-color:#ccffcc;padding:5px;font-size:12pt;position:absolute;top:120px;text-align:right;right:0px;max-width:1ch;">C R E A T O &nbsp; D A &nbsp; M I C H E L E . F U R L A N @ U N I P D . I T &nbsp; I L &nbsp; 2 6 &nbsp; N O V E M B R E &nbsp; 2 0 1 9&nbsp;&nbsp;</DIV>
</BODY>
</HTML>