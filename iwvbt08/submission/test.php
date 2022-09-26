<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2006 Philippe Rigaux
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation;
 
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
************************************************/
 
 
// Allows reviewers to select their preferred topics

 session_start(); 

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");
require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl"));

$form = new Formulaire ("POST", "ExecSQL.php");

$form->debutTable(VERTICAL);
$form->champTexte ("Champ 1", "Vertical", "moi", 20, 25);


$form->debutTable(HORIZONTAL, array(), 3);
$form->champTexte ("Champ 1", "moi", "moi", 20, 25);
$form->champTexte ("Champ 2", "authors", "resume", 20, 25);
$form->champTexte ("Champ 3", "autres", "resume", 20, 25);
$form->finTable();
$form->ajoutTexte ("<P>");
$form->finTable();




$form->champValider ("Exécuter la requête", "exesql");



$tpl->set_var("BODY", $form->formulaireHTML());

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>
