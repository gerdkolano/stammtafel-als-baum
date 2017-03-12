<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="kalenderblatt.css">
<title>Kalenderblatt </title>
</head>
<body>
<?php
class datum_objekt extends DateTime {

  public function __construct($time='now', $timezone='Europe/Berlin') {
      parent::__construct($time, new DateTimeZone($timezone));
  }

  function deutsch( $format) {
    $fmt_tagesname = new IntlDateFormatter( 
        'de-DE',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Berlin',
        IntlDateFormatter::GREGORIAN,
        $format   // http://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
                  // http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
      );
    return rtrim(
      $fmt_tagesname->format( $this)
      , ".");
  }

  function tagesname() {
  # apt-get install php7.0-intl -y
    $fmt_tagesname = new IntlDateFormatter( 
        'de-DE',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Berlin',
        IntlDateFormatter::GREGORIAN,
        "EEEE"   // http://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
      );
    return rtrim(
      $fmt_tagesname->format( $this)
      , ".");
  }

  function monatsname() {
  # apt-get install php7.0-intl -y
    $fmt_tagesname = new IntlDateFormatter( 
        'de-DE',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Berlin',
        IntlDateFormatter::GREGORIAN,
        "MMMM"   // http://www.unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
      );
    return rtrim(
      $fmt_tagesname->format( $this)
      , ".");
  }

}
$debug = true;
$db_name     = "joo336";
$db_server   = "fadi.xeo";
$db_server   = "zoe.xeo";
$db_port     = "3306";
$db_user     = "hanno";
$db_password = shell_exec( "/usr/local/bin/koerperteil mysql");

# heutiges Datum
$d = new datum_objekt;
echo $d->deutsch( 'EEEE dd.MMMM Y');
$heute = $d->deutsch( '-MM-dd');
$jahr  = $d->deutsch( 'Y');

# Hole Geburtstagskinder
$mysqli = new mysqli( $db_server, $db_user, $db_password, $db_name);
if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error;
}
$query = "SET NAMES 'utf8'";
$res = $mysqli->query( $query, MYSQLI_STORE_RESULT);

$query  = "";
$query .= "select "
  . " $jahr - YEAR(geburtszeit) as j"
  . ", selbst"
  . ", name"
  . ", vorname"
  . ", '*' as a"
  . ", geburtszeit as z"
  . ", YEAR(geburtszeit) as y"
  . "  from st_stamm where geburtszeit like '____$heute %' union ";
$query .= "select "
  . " $jahr - YEAR(sterbezeit ) as j"
  . ", selbst"
  . ", name"
  . ", vorname"
  . ", '✝' as a"
  . ", sterbezeit  as z"
  . ", YEAR(sterbezeit ) as y"
  . "  from st_stamm where sterbezeit  like '____$heute %' order by z;";
$result = $mysqli->query( $query, MYSQLI_STORE_RESULT);

if ($result) {
  $tafel = "";
  $tafel .= sprintf( "<table>\n");
  while ($row_stamm = $result->fetch_assoc()) {
    $selbst = $row_stamm['selbst'];
    $name   = $row_stamm['name']; if ($name == "") {$name = "NN";} 
    $tafel .= sprintf( "<tr>");
      $tafel .= sprintf( "<td>");
      $tafel .= sprintf( "<a href=\"/stamm/mysqli/zeige-mit-mysqli.php?$selbst\" target=\"_blank\">");
      $tafel .= sprintf( "%s</a></td>", $name);
      $tafel .= sprintf( "<td>%s</td>", $row_stamm['vorname']);
      $tafel .= sprintf( "<td>%s</td>", $row_stamm['a']);
      $tafel .= sprintf( "<td>%s</td>", $row_stamm['y']);
      $tafel .= sprintf( "<td>%s</td>", $row_stamm['j']);
    $tafel .= sprintf( "</tr>\n");
  }
  $tafel .= sprintf( "</table>");
  echo $tafel;
  /* free result set */
  $result->close();
} else {
  printf("E010 Error: %s<br />\n", $mysqli->error);
}

$mysqli->close();

/* Nur eine Zeile
if ($debug) {
  foreach ($row_stamm as $key=>$value) {
    echo sprintf( "%s %s<br />\n", $key, $value);
  }
}
*/
echo "<br />\n";

?>
</body>
</html>

