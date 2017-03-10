<?php
require_once( "helfer.php");
require_once( "person.php");
require_once( "ehe.php");
require_once( "adresse.php");
//include( "verbinde.php");
# ersetze   pg_query($db, $query) 
# durch     mysql_query( $query, $db_connection)
# ersetze   date_part('MONTH',
# durch     month( 
function debug( $arg) {
  // echo $arg;
}

function icke_selba( $conn) {
  $parameter = new parameter();
  $meine_nummer = $parameter->get_selbst();
  $fn = pathinfo(__FILE__,PATHINFO_BASENAME);
  if (!$meine_nummer or $meine_nummer == "") {
    printf( "<strong>Keine Personennummer angegeben. Versuche<br />\n%s?1</strong>", "$fn");
  }
  
  // Die Daten meiner selbst
  $meine_person = new person( $meine_nummer, $conn);
  if ( "" == $meine_person->get_selbst()) { // Keine Person unter dieser Nummer!
    printf( "<strong>");
    printf( "MELDUNG 003 : Keine Person mit der Nummer %s in %s gefunden.<br />\n", $meine_nummer, "xx1");
    printf( "</strong>");
    printf( "Versuch's <a href=\"%s://%s%s?%s\"> <strong> hier ! </strong></a><br />\n",
      $_SERVER['REQUEST_SCHEME'],
      $_SERVER['SERVER_NAME'],
      $_SERVER['SCRIPT_NAME'],
      1);
    // echo "<pre>"; print_r( $_SERVER); echo "</pre>";
  exit ;
}

// Meine eigenen Daten
// echo "<pre>"; print_r( $meine_person); echo "</pre>";

  return $meine_person;
}

function text_alle_namen( $meine_person, $conn) {
  $meine_nummer = $meine_person->get_selbst();
  $html_erg = "";
  // Die Namen aller meiner Ehen mit Männer und Frauen und meine Namen
  $namenshistory=array();
  $familienstand=array();

  /*
   * Die Daten meines Gatten
   *                                                                               
   * Die Nummer meiner Ehe. Nein, ich kann mehrere Ehen haben
   * $ehe = new ehe( $meine_nummer, $conn);
   *                                                                               
   * Die Nummer meines Gatten bei bekannter Ehe
   * $gatte = $ehe->get_mein_gatte( $meine_nummer);
   * $gattenanker  = (new person( $meine_person->get_gatte(),  $conn))->toAnker();
   *
   */
  
  foreach ( $meine_person->ehenummern() as $ehenr) {
    //$gattenzahl++;
    $ehe = new ehe_mit( $meine_nummer, $ehenr, $conn);
    array_push( $namenshistory, $ehe->name_des_gatten());
    array_push( $familienstand, $ehe->familienstand());
  }
  
  array_push( $namenshistory, $meine_person->get_name());
  array_push( $familienstand, "geb.");
  //print_r( $namenshistory);
  
  $alle_namen = "";
  $i = 0;
  $voriger     =       $namenshistory[$i];
  $alle_namen .= " " . $namenshistory[$i];
  for($i = 1, $size = sizeof($namenshistory); $i < $size; $i++) {
      if ($voriger ===   $namenshistory[$i]) {
        ;
      } else {
        $voriger     =                                   $namenshistory[$i];
        $alle_namen .= " $i" . $familienstand[$i] . "(" . $namenshistory[$i] . ")";
      }
  }
  $vorname = $meine_person->get_vorname();
  $rufname = $meine_person->get_rufname();
  //printf( "%s  %s<br />", $rufname, $vorname);
  $erg = "";
  if (is_numeric($rufname)) {
    $rufname--; // Der Arrayindex beginnt bei 0, die naive Zählung bei 1
    $vornamen = explode(' ', $vorname);
    $ende = count( $vornamen);
    $erg = "";
    for ($i=0; $i<$ende; $i++) {
      if ($rufname==$i) {
        $erg .= sprintf( "<span style=\"text-decoration-line: underline;text-decoration-style:dotted\">%s </span>", $vornamen[$i]);
        //printf( "Ruf=%s<br />", $vornamen[$i]);
      } else {
        $erg .= $vornamen[$i] . " ";
        //printf( "Sonst=%s<br />", $vornamen[$i]);
      }
    }
    //$erg .= ($i==$rufname ? " $rufname _" . $vornamen[$i] : " $rufname _ruf" . $vornamen[$i]);
  } else {
    $erg = $vorname;
  }
  //printf( "Erg=%s<br />", $erg);
  $vornamefarbe = "#800000";
  $vorname = "<span style=\"color:$vornamefarbe\">" . $erg . "</span>";
  //echo $vorname;

  //$alle_namen = (new ehe_mit( $meine_person->get_selbst(), 987654, $conn))->namensgeschichte();
  $alle_namen =  (new ehe_mit( $meine_person->get_selbst(), 987654, $conn))->namensgeschichte();
  $alle_namen = $vorname . " " . $alle_namen;

  return $alle_namen;
  return $html_erg;
}

function html_meiner_eltern( $meine_person, $conn) {
  // Die Daten meines Vaters  Die Daten meiner Mutter
  $html_erg  = "";
  $vaterperson  = new person( $meine_person->get_vater(),  $conn);
  $mutterperson = new person( $meine_person->get_mutter(), $conn);
  $vateranker  = $vaterperson ->toAnker();
  $mutteranker = $mutterperson->toAnker();

  $html_erg .= sprintf( "<li> Eltern\n");
  $html_erg .= sprintf( " <ul>\n");
  $html_erg .= sprintf( "  <li> Vater %s</li>\n",   $vateranker);
  $html_erg .= sprintf( "  <li> Mutter %s</li>\n", $mutteranker);
  $html_erg .= sprintf( " </ul>\n");
  $html_erg .= sprintf( "</li>\n");
  return $html_erg;
}

function html_meiner_verwandten( $meine_person, $conn) {
  $html_erg  = "<ul>\n";
  // Die Daten meines Vaters  Die Daten meiner Mutter
  $html_erg .= html_meiner_eltern( $meine_person, $conn);

  $gattenzahl = 0;
  $gatte_list_item = "";
  foreach ( $meine_person->ehenummern() as $ehenr) {
    $gattenzahl++;
    $ehe = new ehe_mit( $meine_person->get_selbst(), $ehenr, $conn);
    //$gatte = $ehe->get_mein_gatte( $meine_person->get_selbst());
    $gatte_list_item .= "  <li>" . $ehe->toAnker() . "</li>\n";
  }
  if ($gattenzahl>0) {
    $html_erg .= sprintf( "<li> $gattenzahl" . ($gattenzahl==1?" Gatte":" Gatten")." oder Partner\n");
    $html_erg .= sprintf( " <ul>\n%s </ul>\n", $gatte_list_item);
    $html_erg .= sprintf( "</li>\n");
  }
  
  // Die Daten aller meiner Kinder als HTML-List-Items
  $kinderzahl = 0;
  $kind_list_item = "";
  foreach ( $meine_person->kindernummern() as $nr) {
    $kinderzahl++;
    $kindperson = new person( $nr, $conn);
    $kind_list_item .= "  <li>" . $kindperson->toAnker() . "</li>\n";
  }
  if ($kinderzahl>0) {
    $html_erg .= sprintf( "<li> $kinderzahl" . ($kinderzahl==1?" Kind":" Kinder")."\n");
    $html_erg .= sprintf( " <ul>\n%s </ul>\n", $kind_list_item);
    $html_erg .= sprintf( "</li>\n");
  }
  $html_erg .= "</ul>\n";
  return $html_erg;
}

function html_meiner_visitenkarte( $meine_person, $conn) {
  $tierkreisnr = $conn->hol_monat( $meine_person->get_selbst());
  $konst = new konstante();
  $nbsp = "\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  $nbsp .= $nbsp;
  $nbsp .= $nbsp . "\n";
  //$tierkreis = $conf->tierkreis;
  $html_erg = "";
  $html_erg .= sprintf( "<sub>%s %s %s %d.Generation %s Nr.%d</sub><br />\n",
	($g=$meine_person->get_geschlecht()) == "" ? "" : $konst->sexe[$g],
	$tierkreisnr>0 ? $konst->zodiac_sign[$tierkreisnr] : " ",
	$tierkreisnr>0 ? $konst->zodiac_de  [$tierkreisnr] : " ",
	$meine_person->get_generation(),
	$meine_person->get_tafel() . ($meine_person->get_nr() != "" ? "/". $meine_person->get_nr() . ".Kind": ""),
       	$meine_person->get_selbst());
  $helfer = new helfer();
  $html_erg .= sprintf( "<table border>\n");
  $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "Stand",     $meine_person->get_beruf() == "" ? $nbsp : $meine_person->get_beruf());
  $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "geboren",   $helfer->kalendertag( $meine_person->get_geburtszeit()));
  $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "in",        $meine_person->get_geburtsort());
  if ($meine_person->get_sterbezeit()!="") {
    $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "gestorben", $helfer->kalendertag( $meine_person->get_sterbezeit()));
    $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "in",        $meine_person->get_sterbeort());
  }
  $alter = $meine_person->get_alter();
  $html_erg .= sprintf( "<tr><td> %s </td> <td> %s </td></tr>\n", "Alter",     $alter);
  $html_erg .= sprintf( "</table>\n");
  $html_erg .= sprintf( "%s", $meine_person->get_bemerkung());
  $html_erg .= sprintf( "\n");
  return $html_erg;
}

function html_meiner_adressen( $meine_person, $conn) {
  $html_erg = "";
  $html_erg .= "<ul>\n";
  $meine_nummer = $meine_person->get_selbst();
  $rufer = basename($_SERVER['PHP_SELF']); // "zeige-mit-mysqli.php?";

  $adresse = new adresse( $meine_nummer, $rufer, $conn);
  $adressenzahl = count( $adresse->adressen());

  if ($adressenzahl>0) {
    $html_erg .= sprintf( "<li> $adressenzahl" . ($adressenzahl==1?" Adresse":" Adressen")."\n");
    $html_erg .= sprintf( " <ul>\n%s </ul>\n", $adresse->adress_list_items());
    $html_erg .= sprintf( "</li>\n");
  }
  $html_erg .= "</ul>\n";
  return $html_erg;
}

function html_kopf( $meine_person, $conn) {
  $alle_namen = text_alle_namen( $meine_person, $conn);
    
  $html_erg = "";
  $html_erg .= sprintf( "<!--  version 2.2 http://zoe.xeo/stamm/tafel/zeige-mit-mysqli.php?5189 -->\n");
  $html_erg .= sprintf( "<!DOCTYPE html>\n");
  $html_erg .= sprintf( "<html>\n");
  $html_erg .= sprintf( "<head>\n");
  $html_erg .= sprintf( "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n");
  $html_erg .= sprintf( "<link rel=\"stylesheet\" href=\"stammtafel.css\" type=\"text/css\">\n");
  $html_erg .= sprintf( "<title>%s</title>\n", $meine_person->get_vorname());
  $html_erg .= sprintf( "</head>\n");
  $html_erg .= sprintf( "<body>\n");
  $html_erg .= sprintf( "<h2>%s</h2>\n", $alle_namen);

  return $html_erg;
}

function anker( $ziel, $label, $selbst) {
  return sprintf( "<a href=\"%s?$selbst\">%s</a>\n", $ziel, $label, $selbst);
}

function html_fusz( $meine_person, $conn) {
  $fn = pathinfo(__FILE__,PATHINFO_BASENAME);
  $konst = new konstante();
  $conf = new configure();
  $meine_nummer = $meine_person->get_selbst();
  $editierskript = $konst->editierskript;
  $action = "$editierskript?$meine_nummer";
  $zufall_a = rand(10,99);

  $aktualisierungszeit = $conn->hol_aktualisierungszeit();
  $html_erg = "";
  $html_erg .= sprintf( "<hr><sub>Zuletzt aktualisiert: %s.%s %s %s</sub>\n", $aktualisierungszeit, $zufall_a, $conf->db_server, $conf->db_name);
  $html_erg .= sprintf( "<form method=\"post\" action=\"$action\">\n");
  $html_erg .= sprintf( "<input type=\"text\" name=\"benutzer\" size=\"5\">\n");
  $html_erg .= sprintf( "<input type=\"hidden\" name=\"RUFER\" value=\"$fn\">\n");
  $html_erg .= sprintf( "<input type=\"hidden\" name=\"selbst\" value=\"$meine_nummer\">\n");
  $html_erg .= sprintf( "<input type=\"hidden\" name=\"verborgen\" value=\"$zufall_a\">\n");
  $html_erg .= sprintf( "<input type=\"submit\" name=\"SubmitButton\" value=\"Editieren\">\n");
  $html_erg .= sprintf( "</form>\n");
  $html_erg .= "Baumdarstellungen: ";
  // $html_erg .= anker( ".php", "");
  $html_erg .= anker( "partner.php", "Partner mit Kindern", $meine_nummer);
  $html_erg .= anker( "kinder.php", "Kinder und Stiefkinder", $meine_nummer);
  $html_erg .= anker( "baum.php", "Eltern und Vorfahren", $meine_nummer);
  $html_erg .= sprintf( "</body>\n");
  $html_erg .= sprintf( "</html>\n");

  $myFile = "geheim";
  $fh = fopen( $myFile, 'w') or die("Kann $myFile nicht öffnen. f=/zoe.xeo/stamm/mysqli/geheim; touch \$f; chown www-data: \$f");
  fwrite($fh, $zufall_a);
  fclose($fh);

  return $html_erg;
}

function main() {
  date_default_timezone_set( 'Europe/Berlin');
  $conn = new conn();
  $meine_person = icke_selba( $conn);
  
  echo html_kopf( $meine_person, $conn);
  echo html_meiner_visitenkarte  ( $meine_person, $conn);
  echo html_meiner_adressen      ( $meine_person, $conn);
  echo html_meiner_verwandten    ( $meine_person, $conn);
  echo html_fusz( $meine_person, $conn);
}

main();
?>
