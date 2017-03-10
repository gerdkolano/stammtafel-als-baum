<?php
/*
CREATE TABLE destination_db.new_table        LIKE          source_db.existing_table;
INSERT       destination_db.new_table        SELECT * FROM source_db.existing_table;

CREATE TABLE joo336.st_stamm                 LIKE          joo251.stamm;
INSERT       joo336.st_stamm                 SELECT * FROM joo251.stamm;

CREATE TABLE joo336.st_ehen                  LIKE          joo251.ehen;
INSERT       joo336.st_ehen                  SELECT * FROM joo251.ehen;

CREATE TABLE joo336.st_tierkreis             LIKE          joo251.tierkreis;
INSERT       joo336.st_tierkreis             SELECT * FROM joo251.tierkreis;

CREATE TABLE joo336.st_stamm2contact_details LIKE          joo251.stamm2contact_details;
INSERT       joo336.st_stamm2contact_details SELECT * FROM joo251.stamm2contact_details;

CREATE TABLE joo336.st_contact_details       LIKE          joo251.jos_contact_details;
INSERT       joo336.st_contact_details       SELECT * FROM joo251.jos_contact_details;

 */
class konstante {
  public $debug = 0;
  public $actionskript = "aktion.php";
  public $zeigeskript = "zeige-mit-mysqli.php";
  public $editierskript = "stamm-und-ehen.php";
  public $bordercolor = array( "#ffc0c0", "#c0c0ff", "#c0ffc0");
  public $MARRIAGE_SYMBOL = "&#x26ad;";
  public $divorce_symbol  = "&#x26ae;";
  public $unmarried_partnership_symbol = "&#x26af;";
  public $sexe = array( 1=>"&#9792;", "&#9794;", "drittes"); // female male
  public $zodiac_en = array( 1=>"Aquarius", "Pisces", "Aries", "Taurus", "Gemini",     "Cancer",    "Leo",      "Virgo",   "Libra",  "Scorpius",   "Sagittarius", "Capricorn" );
  public $zodiac_de = array( 1=>"Wassermann", "Fische", "Widder", "Stier", "Zwillinge", "Krebs", "L&ouml;we", "Jungfrau", "Waage", "Skorpion", "Sch&uuml;tze", "Steinbock");

  public $zodiac_sign = array( 1=>
    /* Aquarius                U+2652 */  "&#x2652;",
    /* Pisces                  U+2653 */  "&#x2653;",
    /* Aries                   U+2648 */  "&#x2648;",
    /* Taurus                  U+2649 */  "&#x2649;",
    /* Gemini                  U+264A */  "&#x264A;",
    /* Cancer                  U+264B */  "&#x264B;",
    /* Leo                     U+264C */  "&#x264C;",
    /* Virgo                   U+264D */  "&#x264D;",
    /* Libra                   U+264E */  "&#x264E;",
    /* Scorpio                 U+264F */  "&#x264F;",
    /* Sagittarius             U+2650 */  "&#x2650;",
    /* Capricorn               U+2651 */  "&#x2651;"
  );
  
  public function genauigkeit() { return "<br />\n"
    .  "MINUTE=0,     STUNDE=1,     TAG=2,     MONAT=3,     JAHR=4,     JAHRZEHNT=5,   TAGundMONAT=6<br />\n"
    .  "UMMINUTE=10,  UMSTUNDE=11,  UMTAG=12,  UMMONAT=13,  UMJAHR=14,  UMJAHRZEHNT=15              <br />\n"
    .  "VORMINUTE=20, VORSTUNDE=21, VORTAG=22, VORMONAT=23, VORJAHR=24, VORJAHRZEHNT=25             <br />\n"
    .  "NACHMINUTE=30,NACHSTUNDE=31,NACHTAG=32,NACHMONAT=33,NACHJAHR=34,NACHJAHRZEHNT=35            <br />\n"
    .  "getauft oder begraben=.01"
    ;
  }
}

class configure {
  private $prefix;
  public $tafel_s;
  public $tafel_e;
  public $tierkreis;
  public $tafel_jos_contact_details;
  public $tafel_stamm2contact_details;

  public $db_server;
  public $db_name;
  public $db_port;
  public $db_user;
  public $db_password;

  function __construct() {
    //$this->fadi__construct();
    $this->zoe__construct();
  }
/*
 * mysql -hfadi.xeo -uhanno -p joo336
 * mysql  -hzoe.xeo -uhanno -p joo336
 */
  
    
  function zoe__construct() { //zoe
    $this->db_server   = "zoe.xeo";
    $this->db_name     = "joo336"; // "joo251";
    $this->db_port     = "3306";
    $this->db_user     = "hanno";
    $this->db_password = shell_exec( "/usr/local/bin/koerperteil mysql");
    $this->prefix = "st_";
    $this->tierkreis = $this->prefix . "tierkreis";
    $this->tafel_s = $this->prefix . "stamm";
    $this->tafel_e = $this->prefix . "ehen";
    $this->tafel_jos_contact_details   = $this->prefix . "contact_details";
    $this->tafel_stamm2contact_details = $this->prefix  . "stamm2contact_details";
  }
  function fadi__construct() { //fadi
    $this->db_server   = "fadi.xeo";
    $this->db_name     = "joo336";
    $this->db_port     = "3306";
    $this->db_user     = "hanno";
    $this->db_password = shell_exec( "/usr/local/bin/koerperteil mysql");
    $this->prefix = "st_";
    $this->tierkreis = $this->prefix . "tierkreis";
    $this->tafel_s = $this->prefix . "stamm";
    $this->tafel_e = $this->prefix . "ehen";
    $this->tafel_jos_contact_details   = $this->prefix . "contact_details";
    $this->tafel_stamm2contact_details = $this->prefix . "stamm2contact_details";
  }

    // $joomla_prefix = "jos_";
    // $this->tafel_jos_contact_details   = $joomla_prefix . "contact_details";
    // $this->db_name     = "joo251";
}

class helfer {
  function kalendertag( $arg) {
   // 1943-10-15 00:00:00+01
   // 1888-03-25 00:00:00+00:53:28
   // 1692-08-11 00:00:00.01+00:53:28
   // preg_replace($pattern, $replacement, $string);
    $sekunde = preg_replace( "/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+).*/", "$6", $arg);
    switch ($sekunde) {
      case "03" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"$2.$1"      , $arg);
      case "04" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"$1"         , $arg);
      case "05" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"$1er Jahre" , $arg);
      case "06" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"$3.$2"      , $arg);
      case "14" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"um $1"      , $arg);
      case "24" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"vor $1"     , $arg);
      case "34" : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"nach $1"    , $arg);
      default   : return preg_replace( "/^(\d+)-(\d+)-(\d+).*/" ,"$3.$2.$1"   , $arg);
    }
  }
  
  /*
          alert("MINUTE=0,     STUNDE=1,     TAG=2,     MONAT=3,     JAHR=4,     JAHRZEHNT=5,   TAGundMONAT=6\\n"
               +"UMMINUTE=10,  UMSTUNDE=11,  UMTAG=12,  UMMONAT=13,  UMJAHR=14,  UMJAHRZEHNT=15              \\n"
               +"VORMINUTE=20, VORSTUNDE=21, VORTAG=22, VORMONAT=23, VORJAHR=24, VORJAHRZEHNT=25             \\n"
               +"NACHMINUTE=30,NACHSTUNDE=31,NACHTAG=32,NACHMONAT=33,NACHJAHR=34,NACHJAHRZEHNT=35            \\n"
               +"getauft oder begraben=.01");
  Die Genaugkeit der Datumsangabe ist in den Sekunden und Zehntelsekunden kodiert.
  */

  function diff( $arg1, $arg2) {
    $laenge = array( 0=>31,31,28,31,30,31,30,31,31,30,31,30,31);
    $dann=explode( "-", $arg2);
    $erst=explode( "-", $arg1);
    $diffJahr  = $dann[0] - $erst[0];
    $diffMonat = $dann[1] - $erst[1];
    $diffTag   = $dann[2] - $erst[2];
      if ($diffTag < 0) {
          $diffTag += $laenge[$erst[1]-1];
          $diffMonat -= 1;
      }
      if ($diffMonat < 0) {
          $diffMonat += 12;
          $diffJahr  -=1;
      }
    $arr = array( $arg1, $arg2);
    foreach ($arr as $value) {
      $sekunde = preg_replace( "/^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+).*/", "$6", $value);
      switch ($sekunde) {
        case "04": $diffMonat =-1;
        case "03": $diffTag   =-1; break;
        default:                   break;
      }
    }
  
    $erg = "";
    if ($diffJahr >0) $erg .= $diffJahr  . ($diffJahr ==1 ? " Jahr "  : " Jahre " );
    if ($diffMonat>0) $erg .= $diffMonat . ($diffMonat==1 ? " Monat " : " Monate ");
    if ($diffTag  >0) $erg .= $diffTag   . ($diffTag  ==1 ? " Tag "   : " Tage "  );
    return $erg;
  }

}

class gepostet {
  private $datenfeld = array();
  private $rufer;

  function __construct() {
    $this->feld();
  }

  function rufer() { return $this->rufer; }

  function zeig() {
    $posted = "";
      foreach ($_POST as $key=>$value) {
      $posted .= " $key = $value <br />\n";
    }
    echo "SV_010 posted = \"<br />\n$posted\"<br />\n";
  }

  function feld() {
    foreach ($_POST as $key=>$value) {
      switch($key) {
      default        : $this->datenfeld[ $key] = $value; break;
      case 'RUFER'   : $this->rufer = $value; break;
      case 'AUSWAHL' :
      case 'GATTENR' :
      case 'selbst'  : break;
      }
    }
  }

  function get_datenfeld() { return $this->datenfeld; }

}

class conn {
  private $fh;
  private $mysqli;
  public $debug;
  public function get_mysqli() {
    return $this->mysqli;
  }

  public function __construct() {
    $config = new configure();
    $konst  = new konstante();
    $this->debug = $konst->debug;
    $db_name     = $config->db_name; //$db_name     = "joo251"; $db_name     = "joo1700"; $db_name     = "joo1701";
    $db_server   = $config->db_server; //$db_server   = "zoe.xeo";
    $db_port     = $config->db_port;
    $db_user     = $config->db_user;
    $db_password = $config->db_password;

    $myFile = "logging";
    $strich = "##############################################################################################################";
    $this->fh = fopen( $myFile, 'a')
      or printf( "Kann %s/%s nicht öffnen.<br />\nAls root: <br />\nf=%s/%s; touch \$f; chown www-data: \$f<br />\n",
        __DIR__, $myFile, __DIR__, $myFile);

    if ( $this->fh) {
      fwrite( $this->fh, sprintf ( "%s %s %s %s\n", date( "Y-m-d H:i:s"), $db_server, $db_name, $strich));
    }
    //fclose($fh);

    $mysqli = new mysqli( $db_server, $db_user, $db_password, $db_name);
    if ($mysqli->connect_errno) {
      $meldung = "HEL010 Failed to connect to MySQL: " . $mysqli->connect_error;
      echo "<strong> $meldung </strong>";
        $this->logge( $meldung);
    }

    if (!$mysqli->set_charset("utf8")) {
      $meldung = sprintf("HEL020 Error loading character set utf8: %s", $mysqli->error);
    } else {
      $meldung = sprintf("HEL030 Current character set: %s", $mysqli->character_set_name());
    }
    if ($this->debug>3) {printf("%s<br />\n", $meldung);}
    $this->logge( $meldung);

    /*
     * <a href="http://php.net/manual/en/mysqli.set-charset.php" target="_blank">http://php.net/manual/en/mysqli.set-charset.php</a>
    $query = "SET NAMES 'utf8'";
    $res = $mysqli->query( $query, MYSQLI_STORE_RESULT);
     */
    $this->mysqli = $mysqli;
  }

    // SELECT liefert einen Fehler oder ein Array mit 0, 1 oder n Elementen. Manchmal interessiert nur ein Element.
    //  mysqli_query() returns FALSE on failure.
    //  For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries
    //  mysqli_query() will return a mysqli_result object.
    //
    // INSERT
    // UPDATE
    //  For other successful queries mysqli_query() will return TRUE. 

  function erster_datensatz( $query) {
    $result  = $this->frage( 0, $query); // Liefert ein Array
    if ($result) {
      $eine_row = $result->fetch_assoc();
      return $eine_row;
    } else {
      printf( "HEL060 <strong>%s</strong><br />\nHEL065 %s<br />\n", $this->mysqli->error, $query);
      return false;
    }
  }

  function lang_frage( $min, $query) {
    $result  = $this->mysqli->query( $query); // , MYSQLI_STORE_RESULT);
    printf( "###############################################################<br />\n");
    if (is_object($result)) { printf( "obj "); }
    if (is_bool($result)) { printf( "bool "); }
    if ($result) {
      if (isset($result)) {
        printf( "isset ");
        echo "<pre>"; print_r( $result); echo "!</pre><br />\n";
      } else {
        printf( "notset ");
      }
      printf ("erfolg affected_rows %s<br />\nquery %s;<br />\n<br />\n",
        $this->mysqli->affected_rows, $query);
      printf( "Field Count %s<br />\n", $this->mysqli->field_count); // ==0 : bool, >0 : array 
      if (is_object($result)) {
        printf( "Liefere array<br />\n");
      } else {
        printf( "Liefere bool<br />\n");
      }
  
    } else {
      printf ("misserfolg Errno %s<br />\nError %s<br />\nquery %s;<br />\n<br />\n", $this->mysqli->errno, $this->mysqli->error, $query);
      printf( "Liefere bool<br />\n");
    }
    return $result;
  }
  
  function logge( $meldung) {
    if ( $this->fh) {
      fwrite( $this->fh, sprintf ( "%s %s;\n", date( "Y-m-d H:i:s"). substr((string)microtime(), 1, 6), $meldung));
    }
  }
  
  function frage( $min, $query) {
    $this->logge( $query);
    $result  = $this->mysqli->query( $query); // , MYSQLI_STORE_RESULT);
    if ($result) {
      if ($this->mysqli->affected_rows >= $min) {
        return $result;
      } else {
        $meldung = sprintf ("Meldung 004: Misserfolg. Erwarte mindestens %s Ergebnisse.", $min);
        $this->logge( sprintf ("$meldung\n"));
        printf ("query %s;<br />\n%s<br />\n", $query, $meldung);
        return false;
      }
    } else {
      $meldung = sprintf ("Meldung 005: Error %s %s", $this->mysqli->errno, $this->mysqli->error);
      $this->logge( "$meldung\n");
      printf ("%s<br />\nMeldung 005: query %s;<br />\n<br />\n", $meldung, $query);
      return false;
    }
  }

  function hol_aktualisierungszeit() { // Liefert "Unbekannt" oder "YYYY-MM-DD HH-MM-SS"
    $tafel_s = (new configure)->tafel_s;
    $erg="YYYY-MM-DD HH-MM-SS";
    if ($result = $this->frage( 1, "SELECT `geburtszeit` AS `erg` FROM $tafel_s WHERE `selbst`='3999'")) {
      $einzige_row = $result->fetch_assoc();
      $erg = $einzige_row['erg'];
      if ($erg == "") { $erg = "Unbekannt";}
    }
    return $erg;
  }
  
  function hol_last_inserted() { // Liefert -1:Fehler, 0:nichts inserted, n: (die letzte ehe oder selbst)
    $erg = -1;
//  $query = "SELECT max(`selbst`) as erg FROM `$tafel_s`";
    if ($result = $this->frage( 1, "SELECT LAST_INSERT_ID() AS `erg`")) {
      $einzige_row = $result->fetch_assoc();
      $erg = $einzige_row['erg'];
    }
    return $erg;
  }

  function hol_monat( $ich_selbst) {
    $tafel_s = (new configure)->tafel_s;
    $tierkreis = (new configure)->tierkreis;
    $query = "SELECT month( max(anfang)) AS monat FROM $tierkreis WHERE anfang <(SELECT geburtszeit FROM $tafel_s WHERE selbst=$ich_selbst); ";
    $datenfeld = $this->erster_datensatz( $query);
    return $datenfeld['monat'];
  }

  function hol_eine_person( $ich_selbst) {
    $tafel_s = (new configure)->tafel_s;
    $query  = "SELECT * FROM `$tafel_s` WHERE `selbst` = '$ich_selbst'";
    $datenfeld = $this->erster_datensatz( $query);
    if ( !$datenfeld) {
      //printf( "<strong>MELDUNG 001 : Keine Person mit der Nummer %s in %s gefunden.</strong><br />\n", $ich_selbst, $tafel_s);
      return;
    }
    return $datenfeld;
  }

  // hol_array( "SELECT …") { // liefert ein Array mehrerer Objekte mit numerischem Index
  public function hol_array_of_objects( $query) { // liefert ein Array mehrerer Objekte
    $erg = array();
    $result = $this->frage( 0, $query);
    if ( !  $result) { return $erg;}
    while ($datenfeld = $result->fetch_assoc()) {
        $erg[] = $datenfeld;
    }
    return $erg;
  }

  // hol_array( "SELECT …") { // liefert ein Array mehrerer Strings
  public function hol_array( $query) { // liefert ein Array mehrerer Objekte
    $erg = array();
    $result = $this->frage( 0, $query);
    if ( !  $result) { return $erg;}
    while ($datenfeld = $result->fetch_assoc()) {
      foreach ($datenfeld as $key=>$value) {
        $erg[] = $value;
      }
    }
    return $erg;
  }

}

class parameter {
  // Hole alle Zeichen hinter '?' aus der URL
  // http://zoe.xeo/stamm/mysqli/stamm-und-ehen.php?76
  // im diesem Beispiel '76'
  private $selbst;
  public function __construct() {
    $anfrage = isset( $_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ""; // für Nutzung von der Kommandozeile
    $this->selbst = $anfrage;
  }
  public function get_selbst() { return $this->selbst; }
}

?>
