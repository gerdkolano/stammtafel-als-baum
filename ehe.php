<?php

class ehe_mit extends ehe {
  public function __construct( $selbst, $ehenummer, $conn) {
    //printf( "%s\n", "__construct class ehe_mit");
    $this->selbst = $selbst;
    parent::__construct( $ehenummer, $conn);
  }

  private $selbst;
  public function set_selbst( $selbst) {
    $this->selbst = $selbst;
  }

  public function get_selbst() {
    return $this->selbst;
  }

  public function nummer_des_gatten() {
    return $this->selbst == $this->get_frau()
      ? $this->get_mann()
      : $this->get_frau();
  }

  public function name_des_gatten() {
    return $this->selbst == $this->get_frau()
      ? $this->get_mannname()
      : $this->get_frauname();
  }

  public function langname_des_gatten() {
    $gatte = $this->selbst == $this->get_frau()
      ? new person( $this->get_mann(),  $this->conn)
      : new person( $this->get_frau(),  $this->conn);
    //return $gatte->langname();
    $ehename = $this->name_des_gatten();
    $geburtsname = $gatte->get_name();

    $alle_namen = ($geburtsname == "" OR $geburtsname == $ehename)
      ? $ehename
      : $ehename . " geb." . $geburtsname
      ;

    return $gatte->get_vorname()
      . " "
      . $alle_namen
    ;
  }

  public function toAnker() {
    $helfer = new helfer();
    $rufer = basename($_SERVER['PHP_SELF']);
    $gatte = $this->selbst == $this->get_frau()
      ? new person( $this->get_mann(),  $this->conn)
      : new person( $this->get_frau(),  $this->conn);
    $anker = sprintf( "<a href=\"%s?%s\">%s</a>",
      $rufer,
      $this->nummer_des_gatten(),
      $this->langname_des_gatten()
    );

    $erg = sprintf( "%s %s", $helfer->kalendertag( $this->get_ehezeit()), $anker);
    return $erg;
  }

  /*
   * Sterbezeit des Ehegatten
   * Scheidungsdatum
   * select e.mann as gatte, s.vorname from st_ehen e, st_stamm s where e.mann=28 AND e.mann=s.selbst UNION
   * select e.mann as gatte, s.vorname from st_ehen e, st_stamm s where e.frau=28 AND e.mann=s.selbst UNION
   * select e.frau as gatte, s.vorname from st_ehen e, st_stamm s where e.mann=28 AND e.frau=s.selbst UNION
   * select e.frau as gatte, s.vorname from st_ehen e, st_stamm s where e.frau=28 AND e.frau=s.selbst
   * ;
   *
  *
   */
  public function namensgeschichte() {
    $ee = (new configure())->tafel_e;
    $ss = (new configure())->tafel_s;
    $ii = $this->selbst;
    $query = ""
      .  "SELECT e.ehe, e.mann AS gatte, e.mannname AS ehename, e.ehezeit, e.endezeit, s.sterbezeit FROM $ee e, $ss s WHERE e.mann=$ii AND e.mann=s.selbst UNION"
      . " SELECT e.ehe, e.mann AS gatte, e.mannname AS ehename, e.ehezeit, e.endezeit, s.sterbezeit FROM $ee e, $ss s WHERE e.frau=$ii AND e.mann=s.selbst UNION"
      . " SELECT e.ehe, e.frau AS gatte, e.frauname AS ehename, e.ehezeit, e.endezeit, s.sterbezeit FROM $ee e, $ss s WHERE e.mann=$ii AND e.frau=s.selbst UNION"
      . " SELECT e.ehe, e.frau AS gatte, e.frauname AS ehename, e.ehezeit, e.endezeit, s.sterbezeit FROM $ee e, $ss s WHERE e.frau=$ii AND e.frau=s.selbst UNION"
      . " SELECT     0, selbst AS gatte,       name AS ehename, geburtszeit,     NULL,   sterbezeit FROM        $ss   WHERE selbst=$ii"
      . " ORDER BY ehezeit DESC"
      ;
    $debug = true;
    $debug = false;

    $erg = $this->conn->hol_array_of_objects( $query);
    $alle_namen = "";
    while ($gatte_b = current( $erg)) {
      if ($gatte_a = next( $erg)) {
        if ($ii != $gatte_a['gatte']) {$temp = $gatte_a; $gatte_a = $gatte_b; $gatte_b = $temp;}
        if ($debug) $this->zeig_mal( $gatte_a);
        if ($debug) $this->zeig_mal( $gatte_b);

        if ($gatte_b['endezeit'] == "") {
          $alle_namen = $this->hinzu( $alle_namen, "verw.", $gatte_a['ehename']);
        } else {
          $alle_namen = $this->hinzu( $alle_namen, "gesch.", $gatte_a['ehename']);
        }
      } else {
        if ($debug) $this->zeig_mal( $gatte_b);
        $alle_namen = $this->hinzu( $alle_namen, "geb.", $gatte_b['ehename']);
        continue;
      }

      next( $erg);
    }
    //echo "<pre>"; print_r( $erg); echo "</pre>";
    //echo "<pre>"; print_r( $current); echo "</pre>";
    return $alle_namen;
  }

  private function zeig_mal( $gatte) {
    printf("%04d: ehezeit=%s endezeit=%s sterbezeit=%s %s<br />\n", $gatte['gatte'], $gatte['ehezeit'], $gatte['endezeit'], $gatte['sterbezeit'], $gatte['ehename']);
  }

  private function hinzu( $alle_namen, $zustand, $gatte) {
    if ($gatte == "") { return $alle_namen; }
    if ($alle_namen == "") { return $gatte; }
    if (false === strripos($alle_namen, $gatte)) { return "$alle_namen $zustand $gatte"; }
    return $alle_namen;
  }

// echo "<pre>"; print_r( $value); echo "</pre>";
  public function familienstand() {
// Ich bin verwitwet, wenn ich noch lebe, mein Gatte aber nicht
// Ich bin geschieden, wenn ich noch lebe und die Ehe zuende ist
    $gatte = $this->selbst == $this->get_frau()
      ? new person( $this->get_mann(),  $this->conn)
      : new person( $this->get_frau(),  $this->conn);
    $ich = new person( $this->selbst,  $this->conn); 
    if ($ich->get_sterbezeit() and $gatte->get_sterbezeit() and $ich->get_sterbezeit() > $gatte->get_sterbezeit()) {
      return "verw.";
    } else {
      return $this->get_endezeit() != "" ? "gesch." : "verh.";
    }
  }
  // select mann, frauname, ehezeit, endezeit, endezeit from $ee e, $ss s where e.frau=$ii AND s.selbst=e.mann UNION select selbst, name, geburtszeit, sterbezeit, sterbezeit from $ss WHERE selbst=$ii ORDER BY ehezeit
  //
}

class ehe {
  private $ehe       ;
  private $art       ;
  private $mann      ;
  private $frau      ;
  private $mannname  ;
  private $frauname  ;
  private $ehezeit   ;
  private $eheort    ;
  private $endezeit  ;
  private $endegrund ;

  private $datenfeld;
  private $mysqli;
  private $tafel_e;

  public function __construct( $ehenummer, $conn) {
    // printf( "%s ehenummer = %s \n", "__construct class ehe", $ehenummer);
    $this->tafel_e = (new configure())->tafel_e; // $tafel_e = "ehen";
    $this->ehe = $ehenummer;
    $this->conn = $conn;
    $this->mysqli = $conn->get_mysqli();
    $query = "SELECT * FROM `$this->tafel_e` WHERE `ehe` = $this->ehe;";
    $this->datenfeld = $conn->erster_datensatz( $query);

    $this->ehe       = $this->datenfeld['ehe'       ];
    $this->art       = $this->datenfeld['art'       ];
    $this->mann      = $this->datenfeld['mann'      ];
    $this->frau      = $this->datenfeld['frau'      ];
    $this->mannname  = $this->datenfeld['mannname'  ];
    $this->frauname  = $this->datenfeld['frauname'  ];
    $this->ehezeit   = $this->datenfeld['ehezeit'   ];
    $this->eheort    = $this->datenfeld['eheort'    ];
    $this->endezeit  = $this->datenfeld['endezeit'  ];
    $this->endegrund = $this->datenfeld['endegrund' ];
  }

  public function get_ehe        () { return $this->ehe       ;}   
  public function get_art        () { return $this->art       ;}   
  public function get_mann       () { return $this->mann      ;}   
  public function get_frau       () { return $this->frau      ;}   
  public function get_mannname   () { return $this->mannname  ;}   
  public function get_frauname   () { return $this->frauname  ;}   
  public function get_ehezeit    () { return $this->ehezeit   ;}   
  public function get_eheort     () { return $this->eheort    ;}   
  public function get_endezeit   () { return $this->endezeit  ;}   
  public function get_endegrund  () { return $this->endegrund ;}   

  public function get_mein_gatte( $ich_selbst) {
    return $ich_selbst == $this->frau
      ? $this->mann
      : $this->frau
      ;
  }

  public function toString() {
    $erg = "";
    foreach ($this->datenfeld as $key=>$value) {
      $erg .= sprintf( "%s %s<br />\n", $key, $value);
    }
    return $erg;
  }

  /*
   * Die Nummern aller Kinder dieser Ehe.
   */
  public function kindernummern( ) { // liefert ein Array mehrerer Nummern von Kindern
    $tafel_s = (new configure())->tafel_s;
    return $this->conn->hol_array(
       "SELECT s.selbst FROM $tafel_s s, $this->tafel_e e "
     . " WHERE e.ehe = $this->ehe AND ("
     .    "s.vater  = e.mann AND s.mutter = e.frau"
    . " OR s.mutter = e.mann AND s.vater  = e.frau"
  . ")"); 
  }

  /*
   * Die Nummern aller Ehen der beiden Partner dieser Ehe einschließlich der Nummer dieser Ehe.
   */
  public function ehenummern( ) { // liefert ein Array mehrerer Nummern von Ehen
    return $this->conn->hol_array(
        "SELECT `ehe` FROM `$this->tafel_e`"
      . " WHERE `frau`={$this->frau} OR `mann`={$this->mann}"
      . " OR    `frau`={$this->mann} OR `mann`={$this->frau}"
      . " ORDER BY `ehezeit`");
  }

  public function familienstand_obsolet( $nr_selbst) {
// Ich bin verwitwet, wenn ich noch lebe, mein Gatte aber nicht
// Ich bin geschieden, wenn ich noch lebe und die Ehe zuende ist
    $gatte = $nr_selbst == $this->frau
      ? new person( $this->mann,  $this->conn)
      : new person( $this->frau,  $this->conn);
    $ich = new person( $nr_selbst,  $this->conn); 
    if ($ich->get_sterbezeit() and $gatte->get_sterbezeit() and $ich->get_sterbezeit() > $gatte->get_sterbezeit()) {
      return "verw.";
    } else {
      return $this->endezeit != "" ? "gesch." : "verh.";
    }
  }

  public function toAnker_obsolet( $nr_selbst) {
    $helfer = new helfer();
    $rufer = basename($_SERVER['PHP_SELF']);
    $gatte = $nr_selbst == $this->frau
      ? new person( $this->mann,  $this->conn)
      : new person( $this->frau,  $this->conn);
    $anker = sprintf( "<a href=\"%s?%s\">%s</a>",
      $rufer,
      $this->nummer_des_gatten_von( $nr_selbst),
      $this->langname_des_gatten_von( $nr_selbst)
    );

    $erg = sprintf( "%s %s", $helfer->kalendertag( $this->datenfeld['ehezeit']), $anker);
    return $erg;
  }

  public function toForm( $farbe, $actionskript) {
    // $diese = pathinfo(__FILE__,PATHINFO_BASENAME);
    $rufer = basename($_SERVER['PHP_SELF']);
    $conf = new configure();

    $erg = "";

    $erg .= "<tr><td>Zeige\n";
    $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", $rufer, $this->frau, "Frau" );
    $erg .= sprintf( "    <td><a href=\"%s?%s\">%s</a>\n", $rufer, $this->mann, "Mann" );

    $erg .= sprintf( "    <td style=\"border:0px\">&nbsp;\n");
    $erg .= sprintf( "    <td style=\"border:0px\"><span style=\"color:#707070\"><sub>%s</sub></span>\n", $conf->db_server);
    $erg .= sprintf( "    <td style=\"border:0px\"><span style=\"color:#707070\"><sub>%s</sub></span>\n", $conf->db_name);

    foreach ($this->datenfeld as $key=>$value) {
      $erg .= "<tr><td>$key";
      $erg .= "<td colspan=\"5\">"
	      . "<input type=\"TEXT\"   name=\"$key\" size=\"77\" value='"
	      . addcslashes( $value, '\\\'')
	      . "' >"
	      . "</td>\n";
    }
    $nr = $this->frau;
    $erg .= "<tr><td>Ändere";
    $erg .= "    <input type=\"HIDDEN\" name=\"RUFER\" value=\"$rufer?$nr\">\n";

    $value = "UPDATE"; $label = "update";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";

    $value = "tunix"; $label = "Tu nix";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";

    $value = "INSERT"; $label = "insert";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\" value=\"$value\">$label</button>\n";

    $erg .= "<tr><td>Erzeuge\n";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"tochter\" > Tochter </button>\n";
    $erg .= "    <td><button type=\"SUBMIT\" name=\"AUSWAHL\"  value=\"sohn\"    > Sohn    </button>\n";

    $erg = "<table style=\"background-color:$farbe\">\n$erg</table>\n";

    return "<form method=\"POST\" action=\"$actionskript\">\n$erg</form>\n";
  }

  public function zeigeForm( $farbe) {
    $konst = new konstante();
    //$bordercolor = $konst->bordercolor;
    $partnership = $this->get_art() == 1 ? $konst->unmarried_partnership_symbol : $konst->MARRIAGE_SYMBOL;
    $mann = $this->get_mannname() == "" ? $this->get_mann() : $this->get_mannname();
    $frau = $this->get_frauname() == "" ? $this->get_frau() : $this->get_frauname();
    printf( "<p style=\"color:magenta\">%s %s %s</p>\n", $mann, $partnership, $frau);
    printf( "%s\n", $this->toForm( $farbe, $konst->actionskript));
  }

}

?>
