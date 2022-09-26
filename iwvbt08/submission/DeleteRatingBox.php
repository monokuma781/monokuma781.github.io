<?php
// This script creates a test run
 session_start(); 

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");

require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);

$db->execRequete ("DELETE FROM RatingBox");
$db->execRequete ("DELETE FROM Rating");
echo "All the lines from RatingBox and Rating have been deleted<br>";

?>