<?php
/*
 * Kommandozeile : php kinder.php 21
 * Browser : http://zoe.xeo/stamm/mysqli/kinder.php?r211
 * Zeigt rekursiv 
 *  eine Person und 
 *  alle ihre Kinder und
 *  alle Kinder der Partner dieser Person,
 *  also auch Stiefkinder.
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
    "baum.php",
    $meine_person->get_selbst()
  );
  printf( "%s<a href=\"%s\">%s</a>\n", $praefix, $ziel, $langname);
}

function zeigi_ohne_links( $tief, $praefix, $selbst, $conn) {
  $meine_person = new person( $selbst, $conn);
  printf( "%s%s %s %s \n", $praefix,
    $meine_person->get_selbst(),
    $meine_person->get_vorname(),
    $meine_person->get_name()
  );
}

function alle_kinder ( $meine_person, $conn) {
  $selbst = 2;
  $query = ""
    . "SELECT selbst from st_stamm where `vater` IN ("
    . "SELECT `frau` AS `gatte` FROM `st_ehen` WHERE `mann`='$selbst' UNION "
    . "SELECT `mann` AS `gatte` FROM `st_ehen` WHERE `frau`='$selbst' UNION "
    . "SELECT '$selbst' AS `gatte`) UNION "
    . "SELECT selbst from st_stamm where `mutter` IN ("
    . "SELECT `frau` AS `gatte` FROM `st_ehen` WHERE `mann`='$selbst' UNION "
    . "SELECT `mann` AS `gatte` FROM `st_ehen` WHERE `frau`='$selbst' UNION "
    . "SELECT '$selbst' AS `gatte`)";

  $selbst = $meine_person->get_selbst();
  $gatten = $meine_person->gattennummern();
  $gatten[] = $selbst;
  $alle_kinder = array();
  foreach ( $gatten as $gatte) {
    $alle_kinder = array_merge( $alle_kinder, (new person( $gatte, $conn))->kindernummern());
  }
  $alle_kinder = array_unique( $alle_kinder);
  return $alle_kinder;
}

function zeigr ( $tief, $praefix, $selbst, $last, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    $alle_kinder = alle_kinder ( $meine_person, $conn);
    if ($last) {
      zeigi(   $tief+1, $praefix.'└──', $selbst, $conn);
    } else {
      zeigi(   $tief+1, $praefix.'├──', $selbst, $conn);
    }
    foreach ( $alle_kinder as $kind) {
      $last = ($kind === end($alle_kinder));
      if ($last) {
        zeigr( $tief+1, $praefix.'   ', $kind, $last, $conn);
      } else {
        zeigl( $tief+1, $praefix.'   ', $kind, $last, $conn);
      }
    }
  }
}

function zeigl ( $tief, $praefix, $selbst, $last, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    $alle_kinder = alle_kinder ( $meine_person, $conn);
    if ($last) {
      zeigi(   $tief+1, $praefix.'└──', $selbst, $conn);
    } else {
      zeigi(   $tief+1, $praefix.'├──', $selbst, $conn);
    }
    foreach ( $alle_kinder as $kind) {
      $last = ($kind === end($alle_kinder));
      if ($last) {
        zeigr( $tief+1, $praefix.'│  ', $kind, $last, $conn);
      } else {
        zeigl( $tief+1, $praefix.'│  ', $kind, $last, $conn);
      }
    }
  }
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
    printf( "%s Kinder und Stiefkinder\n", (new person( $selbst, $conn))->get_vorname());
    printf( "</title>\n");
    printf( "<style> a { text-decoration: none; }</style>");
    printf( "</head>\n");
    printf( "<body>\n");
    printf( "<pre>\n");
    #php_sapi_name()==="cli";
    #printf( "%s\n", php_sapi_name());
  }
  return $selbst;
}

$conn = new conn();

$tief = 0;
$praefix = "";
$selbst = parameter( $conn);

zeigr( 0, '', $selbst, true, $conn);
?>
