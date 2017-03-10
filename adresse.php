<?php

class adresse {
  private $tafel_jos_contact_details;
  private $tafel_stamm2contact_details;
  private $selbst;
  private $rufer;

  function __construct( $selbst, $rufer, $conn) {
    $this->selbst = $selbst;
    $prefix = "";
    $stamm2contact_details = $prefix . "stamm2contact_details";
    // $this->tafel_s = (new configure())->tafel_s; // $tafel_s = " stamm";
    // $this->tafel_e = (new configure())->tafel_e; // $tafel_s = " ehen";
    $this->tafel_jos_contact_details   = (new configure())->tafel_jos_contact_details;
    $this->tafel_stamm2contact_details = (new configure())->tafel_stamm2contact_details;

    $this->conn = $conn;
    $this->mysqli = $conn->get_mysqli();
  }

  public function adressen( ) { // liefert ein Array mehrerer Adressen
    $adresse = array();
    $query = "SELECT created, name, con_position, address, suburb, postcode, telephone, "
      . "email_to, mobile, webpage, "
      . "checked_out_time, modified, publish_up, publish_down "
      . "FROM $this->tafel_jos_contact_details "
      . "INNER JOIN $this->tafel_stamm2contact_details "
      . "ON ( $this->tafel_jos_contact_details.id = $this->tafel_stamm2contact_details.id) "
      . "WHERE selbst = $this->selbst;";
    $result = $this->conn->frage( 0, $query);  // Liefert ein Array mehrerer Adressen
    // todo wenn $result leer
    if ( !  $result) { return $adresse;}
    while ($datenfeld = $result->fetch_assoc()) {
      $adresse[] = $datenfeld;
    }
    // echo "<pre>func ";
    // print_r( $adresse);
    // echo "</pre>";
    return $adresse;
  }

  public function adress_list_items( ) { // liefert ein Array mehrerer Adress-Zeilen
    $helfer = new helfer();
    $adressenzahl = 0;
    $erg = "";
    $query = "SELECT created, name, con_position, address, suburb, postcode, telephone, "
      . "email_to, mobile, webpage, "
      . "checked_out_time, modified, publish_up, publish_down "
      . "FROM $this->tafel_jos_contact_details "
      . "INNER JOIN $this->tafel_stamm2contact_details "
      . "ON ( $this->tafel_jos_contact_details.id = $this->tafel_stamm2contact_details.id) "
      . "WHERE selbst = $this->selbst;";
    $result = $this->conn->frage( 0, $query);
    if ( !  $result) { return $erg;}
    while ($datenfeld = $result->fetch_assoc()) {
      $adressenzahl++;
      $erg .= "  <li>" . $helfer->kalendertag( $datenfeld['publish_up'])
        . " <a href=\"" . $this->rufer . "\">"
        . "</a>"
        . $datenfeld['name']         . " "
        . $datenfeld['con_position'] . " "
        . $datenfeld['address']      . " "
        . $datenfeld['postcode']     . " "
        . $datenfeld['suburb']       . " "
        . $datenfeld['telephone']    . " "
        . $datenfeld['email_to']     . " "
        . $datenfeld['mobile']       . " "
        . $datenfeld['webpage']      . " "
        . "</li>\n";
    }
    return $erg;
  }

}

/* meine Adressen mit der Konsole
mysql -h zoe.xeo -u hanno -p joo251
SELECT created, name, con_position, address, suburb, postcode, telephone, \
  checked_out_time, modified, publish_up, publish_down \
  FROM jos_contact_details \
  INNER JOIN stamm2contact_details \
  ON ( jos_contact_details.id = stamm2contact_details.id) \
  WHERE selbst = 1;
*/

?>
