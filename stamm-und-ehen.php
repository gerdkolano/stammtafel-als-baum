<!DOCTYPE html>
<!--
http://gerd.dyndns.za.net/stamm/mysqli/stamm-und-ehen.php
-->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="stammtafel.css" type="text/css">
<title>Stamm und Ehen edit</title>
</head>
<body>
<?php
require_once( "helfer.php");
require_once( "person.php");
require_once( "ehe.php");

$parameter = new parameter();
$selbst = $parameter->get_selbst();
$fn = pathinfo(__FILE__,PATHINFO_BASENAME);
if (!$selbst or $selbst == "") {
  printf( "<strong>Keine Personennummer angegeben. Versuche<br />\n%s?1</strong>", "$fn"); exit;
}

$konst = new konstante();
$conn = new conn();
// (new gepostet())->zeig();

if ( isset($_POST['verborgen']) and isset($_POST['benutzer']) and $_POST['verborgen'] != $_POST['benutzer']) {
  printf( "<a href=\"%s?%s\">Zurück</a>", $_POST['RUFER'], $selbst);
  exit;
}

$ich = new person( $selbst, $conn);
if ($ich->get_selbst() == "") {
  printf( "<strong>");
  printf( "MELDUNG 002 : Keine Person mit der Nummer %s in %s gefunden.<br />\n", $selbst, "xxx");
  printf( "Versuche %s?1", $fn);
  printf( "</strong>");
  exit;
}

$ich->zeigeForm( $konst->bordercolor[0]);

foreach ( $ich->kindernummern() as $nr) {
  $kind = new person( $nr, $conn);
  $kind->zeigeForm( $konst->bordercolor[1]);
}

foreach ( $ich->ehenummern() as $nr) {
  $ehe = new ehe( $nr, $conn);
  $ehe->zeigeForm( $konst->bordercolor[2]);
}

printf( "%s<br />\n", $konst->genauigkeit());

/*
$mutter = new person( $ich->get_mutter(), $conn->get_mysqli());
printf( "<p style=\"color:magenta\">%s, %s</p>\n", $mutter->get_name(), $mutter->get_vorname());
printf( "%s<br />\n", $mutter->toForm( $bordercolor[0], "xx.php"));
 */

$tafel = (new configure)->tafel_s;
//$conn->frage( 0, "SELECT ggeburtszeit FROM $tafel WHERE selbst=3999");
//$conn->frage( 0, "sSELECT geburtszeit FROM $tafel WHERE selbst=3999");
//$conn->frage( 0, "SELECT geburtszeit FROM $tafel WHERE selbst=3999");
//$conn->frage( 0, "SELECT `geburtszeit` FROM `$tafel` WHERE `selbst`>'9999'");
//$conn->frage( 0, "UPDATE $tafel SET `bemerkung`='probe' WHERE selbst=3999");
//$conn->frage( 0, "UPDATE $tafel SET `bemerkung`='probe' WHERE selbst=3999");
//$conn->frage( 0, "UPDATE $tafel SET `bemerkung`='prob ' WHERE selbst=3999");
//$conn->frage( 0, "SELECT `selbst`, `name`, `geburtszeit`,`bemerkung` FROM `$tafel` WHERE `selbst`>= '5320'");
//$conn->frage( 2, "SELECT `selbst`, `name`, `geburtszeit`,`bemerkung` FROM `$tafel` WHERE `selbst`>= '5320'");
//$conn->frage( 0, "DELETE FROM $tafel WHERE selbst >= 5324");
//$conn->frage( 0, "ALTER TABLE $tafel AUTO_INCREMENT = 5324;");
//$conn->frage( 1, "INSERT INTO `$tafel` ( `geschlecht`, `name` ) VALUES ( '1', 'probe')");
//$conn->frage( 0, "");

?>
</body></html>
