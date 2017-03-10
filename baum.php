<?php
/*
 * Kommandozeile : php baum.php 11
 * Browser : http://zoe.xeo/stamm/mysqli/baum.php?11
 * Zeigt rekursiv die Eltern einer Person
 * */
require_once( "helfer.php");
require_once( "person.php");
require_once( "ehe.php");

function zeigi( $tief, $praefix, $selbst, $conn) {
  $meine_person = new person( $selbst, $conn);
  $langname = sprintf( "%s %s",
    $meine_person->get_vorname(),
    $meine_person->get_name()
  );
  $ziel = sprintf( "%s?%s",
    "kinder.php",
    $meine_person->get_selbst()
  );
  printf( "%s<a href=\"%s\">%s</a>\n", $praefix, $ziel, $langname);
}

function zeigl( $tief, $praefix, $selbst, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    zeigl( $tief+1, $praefix . "  ", $meine_person->get_vater(), $conn);
    zeigi( $tief+1, $praefix . "┌─", $selbst, $conn);
    zeigr( $tief+1, $praefix . "│ ", $meine_person->get_mutter(), $conn);
  }
}

function zeigr( $tief, $praefix, $selbst, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    zeigl( $tief+1, $praefix . "│ ", $meine_person->get_vater(), $conn);
    zeigi( $tief+1, $praefix . "└─", $selbst, $conn);
    zeigr( $tief+1, $praefix . "  ", $meine_person->get_mutter(), $conn);
  }
}

function zeigi_ohne_link( $tief, $praefix, $selbst, $conn) {
  $meine_person = new person( $selbst, $conn);
  //$praefix = str_replace( " ", "&nbsp;", $praefix);
  printf( "%s%s %s\n", $praefix,
    $meine_person->get_vorname(),
    $meine_person->get_name());
}

function parameter( $conn) {
  if (php_sapi_name()==="cli") { // von der Kommandozeile gerufen
  printf( "%s\n", $_SERVER['argv'][1]);
  $selbst = $_SERVER['argv'][1];
} else {
  $parameter = new parameter();
  $selbst = $parameter->get_selbst();
  printf( "<!DOCTYPE html>\n");
  printf( "<html>\n");
  printf( "<head>\n");
  printf( "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n");
  printf( "<title>\n");
  printf( "%s Vorfahren\n", (new person( $selbst, $conn))->get_vorname());
  printf( "</title>\n");
  printf( "<style> a { text-decoration: none; }</style>\n");
  printf( "</head>\n");
  printf( "<body>\n");
  printf( "<pre>\n");
  #php_sapi_name()==="cli";
  #printf( "%s\n", php_sapi_name());
}
  return $selbst;
}

error_reporting(E_ALL); // high level of error reporting
$conn = new conn();

$tief = 0;
$praefix = "";
$selbst = parameter( $conn);

zeigl( $tief, $praefix, $selbst, $conn);
printf( "</pre>\n");
printf( "</body>\n");
printf( "</html>\n");
?>
