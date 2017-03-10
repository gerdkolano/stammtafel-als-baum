<?php
/*
 * Kommandozeile : php partner.php 21
 * Browser : http://zoe.xeo/stamm/mysqli/partner.php?r211
 * Zeigt rekursiv 
 *  eine Partnerschaft und 
 *  alle Partnerschaften der daraus hervorgegangenen Kinder.
 *  Es fehlen Kinder, deren eines Elternteil nicht bekannt ist.
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
    "partner.php",
    $meine_person->get_selbst()
  );
  printf( "%s<a href=\"%s\">%s</a>\n", $praefix, $ziel, $langname);
}

function zeige( $tief, $praefix, $ich, $ehe, $conn) {
  $eine_ehe = new ehe_mit( $ich, $ehe, $conn);
  // $eine_ehe->set_selbst( $ich);
  // $eine_ehe->langname_des_gatten();
  // printf( "#### selbst=%s ### mannname=%s ###\n", $eine_ehe->get_selbst(), $eine_ehe->get_mannname());
  $ziel = sprintf( "%s?%s",
    "partner.php",
    $eine_ehe->nummer_des_gatten()
  );
  $eheart = $eine_ehe->get_art() == 0 ? "⚭" : "⚯";
  printf( "%s<a href=\"%s\">%s%s</a>\n", 
    $praefix, 
    $ziel, 
    $eheart, 
    $eine_ehe->langname_des_gatten())
    ;
  return;
}

function zeigr( $tief, $praefix, $selbst, $last, $letzt, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    if ($last) {
      zeigi(   $tief+1, $praefix.'└──', $selbst, $conn);
    } else {
      zeigi(   $tief+1, $praefix.'├──', $selbst, $conn);
    }
    $meine_ehen = $meine_person->ehenummern();
    foreach( $meine_ehen as $ehe) {
      $letzt = ($ehe === end($meine_ehen));
      if ($letzt) {
        zeiger( $tief, $praefix.'   ', $selbst, $ehe, $last, $letzt, $conn);
      } else {
        zeigel( $tief, $praefix.'   ', $selbst, $ehe, $last, $letzt, $conn);
      }
    }
  }
}

function zeiger( $tief, $praefix, $ich, $ehe, $last, $letzt, $conn) {
  $eine_ehe = new ehe( $ehe, $conn);
  $kindernummern = $eine_ehe->kindernummern();
  if ($letzt) {
    zeige(   $tief+1, $praefix.'╚══', $ich, $ehe, $conn);
  } else {
    zeige(   $tief+1, $praefix.'   ', $ich, $ehe, $conn);
  }
  foreach ($kindernummern as $kind) {
    $last = ($kind === end($kindernummern));
    if ($last) {
      zeigr( $tief+1, $praefix.'   ', $kind, $last, $letzt, $conn);
    } else {
      zeigl( $tief+1, $praefix.'   ', $kind, $last, $letzt, $conn);
    }
  }
}

function zeigel( $tief, $praefix, $ich, $ehe, $last, $letzt, $conn) {
  $eine_ehe = new ehe( $ehe, $conn);
  $kindernummern = $eine_ehe->kindernummern();
  if ($letzt) {
    zeige(   $tief+1, $praefix.'   ', $ich, $ehe, $conn);
  } else {
    zeige(   $tief+1, $praefix.'╠══', $ich, $ehe, $conn);
  }
  foreach ($kindernummern as $kind) {
    $last = ($kind === end($kindernummern));
    if ($last) {
      zeigr( $tief+1, $praefix.'║  ', $kind, $last, $letzt, $conn);
    } else {
      zeigl( $tief+1, $praefix.'║  ', $kind, $last, $letzt, $conn);
    }
  }
}

function zeigl( $tief, $praefix, $selbst, $last, $letzt, $conn) {
  $meine_person = new person( $selbst, $conn);
  if ($meine_person->get_selbst() != "") {
    if ($last) {
      zeigi(   $tief+1, $praefix.'└──', $selbst, $conn);
    } else {
      zeigi(   $tief+1, $praefix.'├──', $selbst, $conn);
    }
    $meine_ehen = $meine_person->ehenummern();
    foreach( $meine_ehen as $ehe) {
      $letzt = ($ehe === end($meine_ehen));
      if ($letzt) {
        zeiger( $tief, $praefix.'│  ', $selbst, $ehe, $last, $letzt, $conn);
      } else {
        zeigel( $tief, $praefix.'│  ', $selbst, $ehe, $last, $letzt, $conn);
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
    printf( "%s Kinder, Stiefkinder, Partner\n", (new person( $selbst, $conn))->get_vorname());
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

zeigr( 0, '', $selbst, true, true, $conn);
?>
