<?php
class person {
  private $selbst         ;
  private $geschlecht     ;
  private $vater          ;
  private $mutter         ;
  private $tafel          ;
  private $generation     ;
  private $nr             ;
  private $name           ;
  private $vorname        ;
  private $rufname        ;
  private $beruf          ;
  private $geburtszeit    ;
  private $geburtsort     ;
  private $sterbezeit     ;
  private $sterbeort      ;
  private $bemerkung      ;
  private $datenfeld;
  private $mysqli;
  private $conn;
  private $tafel_s;
  private $tafel_e;
  
  private function real_escape_string( $string) {
    return $this->mysqli->real_escape_string( $string);
  }


  public function __construct( $selbst, $conn) {
    $this->tafel_s = (new configure())->tafel_s; // $tafel_s = " stamm";
    $this->tafel_e = (new configure())->tafel_e;

    $this->conn = $conn;
    $this->mysqli = $conn->get_mysqli();
    if(! isset($selbst) or $selbst == "") {return;}
    
    $this->datenfeld = $conn->hol_eine_person( $selbst);
// todo wenn 0
    $this->selbst          = $this->datenfeld['selbst'      ];                    
    $this->geschlecht      = $this->datenfeld['geschlecht'  ];                        
    $this->vater           = $this->datenfeld['vater'       ];                   
    $this->mutter          = $this->datenfeld['mutter'      ];                    
    $this->tafel           = $this->datenfeld['tafel'       ];                   
    $this->generation      = $this->datenfeld['generation'  ];                        
    $this->nr              = $this->datenfeld['nr'          ];                
    $this->name            = $this->datenfeld['name'        ];                  
    $this->vorname         = $this->datenfeld['vorname'     ];                     
    $this->rufname         = $this->datenfeld['rufname'     ];                     
    $this->beruf           = $this->datenfeld['beruf'       ];                   
    $this->geburtszeit     = $this->datenfeld['geburtszeit' ];                         
    $this->geburtsort      = $this->datenfeld['geburtsort'  ];                        
    $this->sterbezeit      = $this->datenfeld['sterbezeit'  ];                        
    $this->sterbeort       = $this->datenfeld['sterbeort'   ];                       
    $this->bemerkung       = $this->datenfeld['bemerkung'   ];                      
  }

  public function get_datenfeld   () { return $this->datenfeld   ;}

  public function get_selbst      () { return $this->selbst      ;}   
  public function get_geschlecht  () { return $this->geschlecht  ;}   
  public function get_vater       () { return $this->vater       ;}   
  public function get_mutter      () { return $this->mutter      ;}   
  public function get_tafel       () { return $this->tafel       ;}   
  public function get_generation  () { return $this->generation  ;}   
  public function get_nr          () { return $this->nr          ;}   
  public function get_name        () { return $this->name        ;}   
  public function get_vorname     () { return $this->vorname     ;}   
  public function get_rufname     () { return $this->rufname     ;}   
  public function get_beruf       () { return $this->beruf       ;}   
  public function get_geburtszeit () { return $this->geburtszeit ;}   
  public function get_geburtsort  () { return $this->geburtsort  ;}
  public function get_sterbezeit  () { return $this->sterbezeit  ;}
  public function get_sterbeort   () { return $this->sterbeort   ;}
  public function get_bemerkung   () { return $this->bemerkung   ;}   

  public function toString() {
    $erg = "";
    foreach ($this->datenfeld as $key=>$value) {
      $erg .= sprintf( "%s %s<br />\n", $key, $value);
    }
    return $erg;
  }

  public function get_alter() {
    $helfer = new helfer();
    if ($this->get_geburtszeit() == "") { return "";}
    return $this->get_sterbezeit() == ""
      ? $helfer->diff( $this->get_geburtszeit(), date( 'Y-m-d'))
      : $helfer->diff( $this->get_geburtszeit(), $this->get_sterbezeit());
  }

  /*
   * Die Nummern aller Kinder dieser Person.
   */
  public function kindernummern( ) { // liefert ein Array mehrerer Nummern von Kindern Personen
    return $this->conn->hol_array( "SELECT `selbst` FROM `$this->tafel_s`"
     . " WHERE `mutter`={$this->selbst} OR `vater`={$this->selbst} ORDER BY `geburtszeit`");
  }

  /*
   * Die Nummern aller Ehen dieser Person.
   */
  public function ehenummern( ) { // liefert ein Array mehrerer Nummern von Ehen
    return $this->conn->hol_array( "SELECT `ehe` FROM `$this->tafel_e`"
      . " WHERE `frau`={$this->selbst} OR `mann`={$this->selbst} ORDER BY `ehezeit`");
  }

  public function gatten() { // Liefere mehrere Personen : Die Personen aller meiner Gatten
    $query         = "SELECT * FROM `$this->tafel_e` WHERE `mann`='$this->selbst'";
    $query .= " UNION SELECT * FROM `$this->tafel_e` WHERE `frau`='$this->selbst'";
    return $conn->erster_datensatz( $query);
  }

  public function gattennummern() { // Liefere mehrere Nummern : Die Nummern aller meiner Gatten
    $query         = "SELECT `frau` AS `gatte` FROM `$this->tafel_e` WHERE `mann`='$this->selbst'";
    $query .= " UNION SELECT `mann` AS `gatte` FROM `$this->tafel_e` WHERE `frau`='$this->selbst'";
    return $this->conn->hol_array( $query);
  }

  public function vorname_name() {
    return $this->vorname . " " .$this->name; 
  }

  public function toAnker() {
    $helfer = new helfer();
    $rufer = basename($_SERVER['PHP_SELF']);
    return sprintf( "%s <a href=\"%s?%s\">%s</a>",
      $helfer->kalendertag( $this->geburtszeit),
      $rufer,
      $this->selbst,
      $this->vorname_name()
    ); 
  }

  public function toTable( $farbe) {
    $erg = "";
    $erg .= "<table style=\"background-color:$farbe\">";
    foreach ($this->datenfeld as $key=>$value) {
      $erg .= sprintf( "<tr><td>%s <td>%s\n", $key, $value);
    }
    $erg .= "</table>";
    return $erg;
  }

  public function toForm( $farbe, $actionskript) {
    //$rufer = pathinfo(__FILE__,PATHINFO_BASENAME);
    $rufer = basename($_SERVER['PHP_SELF']);
    $conf = new configure();
    ;
    $erg = "";
// Kopft
    $erg .= "<tr><td>Zeige\n";
    $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", $rufer, $this->selbst, "Diese");
    if ($this->mutter != "") {
      $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", $rufer, $this->mutter, "Mutter");
    } else {
      $erg .= sprintf( "    <td><span style=\"color:#707070\">Mutter</span>");
    }
    if ($this->vater != "") {
      $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", $rufer, $this->vater , "Vater" );
    } else {
      $erg .= sprintf( "    <td><span style=\"color:#707070\">Vater</span>");
    }
    $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", (new konstante())->zeigeskript, $this->selbst, "Visitenkarte");
    $erg .= sprintf( "    <td style=\"border:0px\"><span style=\"color:#707070\"><sub>%s</sub></span>\n", $conf->db_server);
    $erg .= sprintf( "    <td style=\"border:0px\"><span style=\"color:#707070\"><sub>%s</sub></span>\n", $conf->db_name);

    // todo wenn $result leer toForm nicht rufen
    // foreach ($this->datenfeld as $key=>$value) {
    
// Rumpf #########################################################
    $feld = $this->datenfeld;
    //if (isset( $feld)) {echo "print_r"; print_r( $feld);}
    foreach ($feld as $key=>$value) {
      $value = $this->real_escape_string( $value);
      //$value = $this->mysqli->real_escape_string( $value);
      $erg .= "<tr><td>$key";
      $erg .= "    <td colspan=\"6\">"
	      . "<input type=\"TEXT\"   name=\"$key\" size=\"77\" value='$value' >"
	      . "</td>\n";
    }
// Fusz #########################################################
    $nr = $this->selbst;
    $erg .= "<tr><td>Ändere\n";
    $erg .= "    <input type=\"HIDDEN\" name=\"RUFER\" value=\"$rufer?$nr\">\n";
    $value = "update"; $label = "update";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";
    $value = "tunix"; $label = "Tu nix";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";
    $value = "insert"; $label = "insert";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";

    $erg .= "<tr><td>Erzeuge\n";
    if ($this->vater == "") {
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"vater\"   > Vater   </button>\n";
    } else {
    $erg .= sprintf( "    <td><span style=\"color:#707070\">Vater</span>\n");
    }
    if ($this->mutter == "") {
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"mutter\"  > Mutter  </button>\n";
    } else {
    $erg .= sprintf( "    <td><span style=\"color:#707070\">Mutter</span>\n");
    }
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"tochter\" > Tochter </button>\n";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"sohn\"    > Sohn    </button>\n";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"gatte\"   > Gatte   </button>\n";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"ehe\"     > Ehe mit </button>\n";
    $erg .= "        <input  type=\"TEXT\"   name=\"GATTENR\"  size=\"5\" >\n";
// Ende #########################################################

    $erg = "<table style=\"background-color:$farbe\">\n$erg</table>";
    return "<form method=\"POST\" action=\"$actionskript\">\n$erg\n</form>\n";
    return $erg;
  }

  public function zeigeForm( $farbe) {
    //$farbe;
    if (!$this->datenfeld) return;
    $konst = new konstante();
    //$bordercolor = array( "#ffc0c0", "#c0c0ff");
    printf( "<p style=\"color:magenta\">%s, %s</p>\n", $this->get_name(), $this->get_vorname());
    printf( "%s\n", $this->toForm( $farbe, $konst->actionskript));
  }

}
?>
