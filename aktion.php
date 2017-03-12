<!DOCTYPE html>
<!--
http://gerd.dyndns.za.net/stamm/mysqli/aktion.php
-->
<?php
require_once( "helfer.php");
require_once( "person.php");

class bank {
  private $datenfeld = array();
  private $conn;
  private $mysqli;
  private $rufer;
  private $tafel_s;
  private $tafel_e;

  function __construct( $conn) {
    $this->conn = $conn;
    $this->mysqli = $conn->get_mysqli();
    $posted = new gepostet();
    $this->datenfeld = $posted->get_datenfeld();
    $posted->zeig();
    $this->rufer = $posted->rufer();
    $this->tafel_s = (new configure())->tafel_s; // $tafel_s = " stamm";
    $this->tafel_e = (new configure())->tafel_e; // $tafel_e = " ehen";
  }

  function rufer() { return $this->rufer; }

  function s_update( $tafel_s, $id) {
    $this->conn->frage( 0, $this->updatequery( $tafel_s, "selbst", $id));
    return $id;
  }

  function updatequery( $tafel, $where, $id) {
    $query = "UPDATE `$tafel` SET ";
    foreach ($this->datenfeld as $key=>$value) {
      $value = $this->mysqli->real_escape_string( $value);
      $value == "" ? $value="NULL" : $value = "'" . $value . "'";
      $query .= "`$key` = $value, ";
    }
    $query = rtrim( $query, " ,");
    $query .= " WHERE `$where` = '$id'";
    printf( "AKT100 Query = %s<br />\n", $query);
    return $query;
  }

  function s_insert( $tafel_s) {
    $query = $this->insertquery( $tafel_s);
    $this->conn->frage( 1, $query);
    return $this->conn->hol_last_inserted();
  }

  function insertquery( $tafel) {
    $qkeys = "";
    $qvals = "";
    foreach ($this->datenfeld as $key=>$value) {
      if ($value != "") {
        $value = $this->mysqli->real_escape_string( $value);
        $qkeys .= "`$key`, ";
        $qvals .= "'$value', ";
      }
    }
    $qkeys = rtrim( $qkeys, " ,");
    $qvals = rtrim( $qvals, " ,");
    $query = "INSERT INTO `$tafel` ($qkeys) VALUES ( $qvals)";
    printf( "AKT040 Query = %s<br />\n", $query);
    return $query;
  }

  function machelter( $tafel_s, $kind_id, $art) {
    switch ($art) {
    case "mutter": $label = "Mutter"; $geschlecht = "1"; break;
    case "vater" : $label = "Vater" ; $geschlecht = "2"; break;
    default      : $label = "MutFat"; $geschlecht = "3"; break;
    }
    $generation = -1 + $this->datenfeld['generation'];
    $name = "$label von (" . $this->datenfeld['vorname'] . " " . $this->datenfeld['name'] . ")";

    $geschlecht = $this->mysqli->real_escape_string( $geschlecht);
    $kind_id    = $this->mysqli->real_escape_string( $kind_id   );
    $generation = $this->mysqli->real_escape_string( $generation);
    $name       = $this->mysqli->real_escape_string( $name      );

    /*$query = "INSERT INTO `$tafel_s` (`geschlecht`, `$art`, `generation`, `name`) VALUES ( "
      . "'$geschlecht', '$kind_id', '$generation', '$name'"
      . ")";
     */
    $query = "INSERT INTO `$tafel_s` (`geschlecht`, `generation`, `name`) VALUES ( "
      . "'$geschlecht', '$generation', '$name'"
      . ")";
    printf( "AKT040 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);

    $neue_person = $this->conn->hol_last_inserted();

    $query = "UPDATE `$tafel_s` SET `$art`=$neue_person WHERE `selbst`=$kind_id";
    printf( "AKT045 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);
    return $neue_person;
  }

  function machgatte( $geschlecht, $generation, $vorname, $name) {
    $tafel_s = $this->tafel_s;
    switch ($geschlecht) {
    case "2" : $geschlecht=1; break;
    case "1" : $geschlecht=2; break;
    default  : $geschlecht=3; break;
    }
    $name       = $this->mysqli->real_escape_string( $name      );
    $query = "INSERT INTO `$tafel_s` (`geschlecht`, `generation`, `vorname`, `name`) VALUES ( "
      . "'$geschlecht', '$generation', '$vorname', '$name' ) ";
    printf( "AKT060 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);
    $neue_person = $this->conn->hol_last_inserted();
    return $neue_person;
  }

  function machehe( $tafel, $selbst, $gatte2) {
    $langname = $this->datenfeld['vorname'] . " " . $this->datenfeld['name'];
    if ($gatte2 < 0) {
      switch ($this->datenfeld['geschlecht']) {
      case "1" : $gatte_von =  "Mann der " ; break;
      case "2" : $gatte_von =  "Frau des " ; break;
      default  : $gatte_von = "Gatte von "; break;
      }
      $gatte2name = $gatte_von . $langname;
      $gatte2 = $this->machgatte(
        $this->datenfeld['geschlecht'],
        $this->datenfeld['generation'],
        "Vorname de. " . $gatte_von . $langname,
        $gatte2name);
    } else {
      $gt2 = new person( $gatte2, $this->conn);
      $gatte2feld = $gt2->get_datenfeld();
      $gatte2name = $gatte2feld['name'];
    }
    switch ($this->datenfeld['geschlecht']) {
    default  :
    case "1" : $mann = $gatte2; $frau = $selbst; $frauname = $this->datenfeld['name']; $mannname = $gatte2name; break;
    case "2" : $mann = $selbst; $frau = $gatte2; $mannname = $this->datenfeld['name']; $frauname = $gatte2name; break;
    }
    $mannname = $this->mysqli->real_escape_string( $mannname);
    $frauname = $this->mysqli->real_escape_string( $frauname);
    $mannname == "" ? $mannname="NULL" : $mannname = "'$mannname'";
    $frauname == "" ? $frauname="NULL" : $frauname = "'$frauname'";

    $query = "INSERT INTO `$tafel` (`mann`, `frau`, `mannname`, `frauname`) VALUES ( " .
      " '$mann', '$frau', $mannname, $frauname ) ";
    printf( "AKT065 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);
    $neue_ehe = $this->conn->hol_last_inserted();

    return $gatte2;
  }

  function zeigmal( $tafel, $wahl) {
    printf( "AKT070 AUSWAHL = \"%s\" tafel = %s<br />", $wahl, $tafel);
  }

  function e_update( $tafel, $ehe) {
    $query = $this->updatequery( $tafel, "ehe", $ehe);
    printf( "AKT066 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);
    if ($this->datenfeld['mann'] != "") {return $this->datenfeld['mann'];}
    if ($this->datenfeld['frau'] != "") {return $this->datenfeld['frau'];}
    return 88888;
  }

  function e_insert( $tafel) {
    $this->datenfeld['ehe'] = "";
    $query = $this->insertquery( $tafel);
    printf( "AKT067 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);
    if ($this->datenfeld['mann'] != "") {return $this->datenfeld['mann'];}
    if ($this->datenfeld['frau'] != "") {return $this->datenfeld['frau'];}
    return 91919;
  }

  function machekind ( $tafel, $elter1id, $elter2id, $art) {
    $elter1 = new person( $elter1id, $this->conn);
    $elter1feld = $elter1->get_datenfeld();
    echo "<pre>"; echo "Mein Feld\n"; print_r( $elter1feld); echo "</pre>\n";
    switch ($art) {
    case "tochter": $label = "Tochter"; $geschlecht = "1"; break;
    case "sohn"   : $label = "Sohn"   ; $geschlecht = "2"; break;
    default       : $label = "TochSoh"; $geschlecht = "3"; break;
    }
    $generation = +1 + $elter1feld['generation'];
    $vorname = "$label von " . $this->datenfeld['mannname'] . " und " . $this->datenfeld['frauname'];
    $name = $this->datenfeld['mannname'];
    if ($elter2id < 0) {
      switch ($this->datenfeld['geschlecht']) {
      case "2" : $column = 'mutter'; break;
      case "1" : $column = 'vater' ; break;
      default  : $column = 'vater' ; break;
      }
      $query = "INSERT INTO `$tafel` (`geschlecht`, `$column`, `generation`, `name`) VALUES ( "
        . "'" . $this->mysqli->real_escape_string( $geschlecht) . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $elter1id)   . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $generation) . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $name)       . "'"
        . ")";
    } else {
      switch ($elter1feld['geschlecht']) {
      case "1" : $tmp = $elter1id; $elter1id = $elter2id; $elter2id = $tmp; break;
      case "2" : ; break;
      default  :                   ; break;
    }
    $query = "INSERT INTO `$tafel` (`geschlecht`, `vater`, `mutter`, `generation`, `vorname`, `name`) VALUES ( "
      . "'" . $this->mysqli->real_escape_string( $geschlecht) . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $elter1id)   . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $elter2id)   . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $generation) . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $vorname)    . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $name)       . "'"
      . ")";
    }
    printf( "AKT040 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);

    $neue_person = $this->conn->hol_last_inserted();
    return $neue_person;
  }

  function machkegel ( $tafel, $elter1id, $elter2id, $art) {
    // $elter1 = new person( $elter1id, $this->conn);
    // $elter1feld = $elter1->get_datenfeld();
    switch ($art) {
    case "tochter": $label = "der Tochter" ; $geschlecht = "1"; break;
    case "sohn"   : $label = "des Sohns"   ; $geschlecht = "2"; break;
    default       : $label = "von TochSoh" ; $geschlecht = "3"; break;
    }
    $generation = +1 + $this->datenfeld['generation'];
    $name = $this->datenfeld['name'];
    $vorname = "Vorname $label " . $this->datenfeld['name'];
    if ($elter2id < 0) {
      switch ($this->datenfeld['geschlecht']) {
      case "1" : $column = 'mutter'; break;
      case "2" : $column = 'vater' ; break;
      default  : $column = 'vater' ; break;
      }
      $query = "INSERT INTO `$tafel` (`geschlecht`, `$column`, `generation`, `vorname`, `name`) VALUES ( "
        . "'" . $this->mysqli->real_escape_string( $geschlecht) . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $elter1id)   . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $generation) . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $vorname)    . "'" . ", "
        . "'" . $this->mysqli->real_escape_string( $name)       . "'"
        . ")";
    } else {
      switch ($this->datenfeld['geschlecht']) {
      case "2" : $tmp = $elter1id; $elter1id = $elter2id; $elter2id = $tmp; break;
      case "1" : ; break;
      default  :                   ; break;
    }
    $query = "INSERT INTO `$tafel` (`geschlecht`, `vater`, `mutter`, `generation`, `name`) VALUES ( "
      . "'" . $this->mysqli->real_escape_string( $geschlecht) . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $elter1id)   . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $elter2id)   . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $generation) . "'" . ", "
      . "'" . $this->mysqli->real_escape_string( $name)       . "'"
      . ")";
    }
    printf( "AKT040 Query = %s<br />\n", $query);
    $this->conn->frage( 1, $query);

    $neue_person = $this->conn->hol_last_inserted();
    return $neue_person;
    }

  function auftrag( $rufer) {
    $tafel_s = $this->tafel_s;
    $tafel_e = $this->tafel_e;
    $tunix = false;
    if (isset($_POST['AUSWAHL']) and isset($_POST['selbst'])) {
      switch ($wahl = $_POST['AUSWAHL']) {
        case "mutter"  : $nr=$this->machelter ( $tafel_s , $_POST['selbst'],                    $wahl); break;
        case "vater"   : $nr=$this->machelter ( $tafel_s , $_POST['selbst'],                    $wahl); break;
        case "tochter" : $nr=$this->machkegel ( $tafel_s , $_POST['selbst'],                -1, $wahl); break;
        case "sohn"    : $nr=$this->machkegel ( $tafel_s , $_POST['selbst'],                -1, $wahl); break;
        case "gatte"   : $nr=$this->machehe   ( $tafel_e , $_POST['selbst'],                -1       ); break;
        case "ehe"     : $nr=$this->machehe   ( $tafel_e , $_POST['selbst'], $_POST['GATTENR']       ); break;
        case "insert"  : $nr=$this->s_insert  ( $tafel_s );                                             break;
        case "update"  : $nr=$this->s_update  ( $tafel_s , $_POST['selbst'] );                          break;
        case "tunix"   : $nr = ""; $tunix = true;                                                       break;
        default        : $nr=$this->zeigmal   ( $tafel_s , $wahl);                                      break;
      }
    }
    if (isset($_POST['AUSWAHL']) and isset($_POST['ehe'])) {
      switch ($wahl = $_POST['AUSWAHL']) {
        case "INSERT"  : $nr=$this->e_insert  ( $tafel_e  );                                            break;
        case "UPDATE"  : $nr=$this->e_update  ( $tafel_e  , $_POST['ehe'] );                            break;
        case "sohn":
        case "tochter" : $nr=$this->machekind ( $tafel_s  , $_POST['mann'],   $_POST['frau'],   $wahl); break;
        case "tunix"   : $nr = ""; $tunix = true;                                                       break;
        default        : $nr=$this->zeigmal   ( $tafel_e  , $wahl);                                     break;
      }
    }

    // ############### Rette das Datum dieser Aktualisierung
    // ############### todo aber nur, wenn etwas geändert wurde !!
    $this->conn->frage( 0, "UPDATE `$tafel_s` SET `geburtszeit`=now() WHERE `selbst`='3999'");

    $editierskript = (new konstante())->editierskript;
    if ($tunix) {
      $ziel = $rufer;
      $zieltext = "Zurück zu den Tabellen. $rufer";
    } else {
      $ziel = "$editierskript?$nr";
      $zieltext = "Zurück zu den Tabellen. Person Nummer $nr";
    }
    printf( "<a href=\"%s\"> %s </a>", $ziel, $zieltext);
    /*
      <form method="POST" action="aktion.php">
     <button type="SUBMIT" name="ZURUECK"  value="von-hier" > Zurück </button>
      </form>
      * */
    $zieltext = "<button type=\"SUBMIT\" name=\"ZURUECK\"  value=\"von-hier\" > $zieltext </button>\n";
    $zieltext .= "<INPUT type=\"TEXT\" name=\"DUMMY\" size=\"3\" autofocus >";
    printf( "<form method=\"POST\" action=\"%s\">\n %s \n</form>", $ziel, $zieltext);
  }

}

$conn = new conn();
$bank = new bank( $conn);
$rufer = $bank->rufer( ); //  $rufer = "stamm-und-ehen.php?5327";
$wartezeit = "555";
// <meta http-equiv="refresh" content="5; URL=stamm-und-ehen?5327>" />
?>
<html>
<head>
<meta http-equiv="refresh" content="<?php echo $wartezeit;?>; URL=<?php echo $rufer;?>" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
  table, td, th { border: 1px solid gray }
</style>
<title>Stamm und Ehen edit</title>
</head>
<body>
<?php

$bank->auftrag( $rufer);

?>

<pre>
SELECT liefert einen Fehler oder ein Array mit 0, 1 oder n Elementen. Manchmal interessiert nur ein Element. 
Vater/Mutter
insert Erzeuge eine neue person.
update Bei selbst muss diese neue als vater oder mutter eingetragen werden.
       Gleicher Nachname, mit zusatz verh.

Tochter/Sohn
insert Erzeuge eine neue person.
       Bei ihr muss selbst als vater oder mutter eingetragen werden.
       Gleicher Nachname

Gatte
insert Erzeuge eine neue person. Anderes Geschlecht
insert Erzeuge eine neue Ehe. Ein Name ist bekannt

Ehe
unverändert selbst.
insert Erzeuge eine neue Ehe. Ein Name ist bekannt Anderes Geschlecht

insert
insert Erzeuge eine neue person.
       Alle Daten sind bekannt

update
update Speichere die vorhandene Person.
       Alle Daten sind bekannt
</body></html>
