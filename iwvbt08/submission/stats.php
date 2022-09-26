<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2006 Philippe Rigaux
   This program is free software; you can redistribute it and/or modify
   it under the terms of the U General Public License as published by
   the Free Software Foundation;
 
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
************************************************/
 
 
 session_start(); 

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");
require_once ("Formulaire.class.php");

define ("TXT_NB_PAPER_BY_TOPIC","Nb of Papers by topics");
define ("TXT_NB_SUBMITTED_REVIEWS","Nb of Submitted Reviews");
define ("TXT_NB_SUBMITTED_PAPERS","Nb of Submitted Papers");
define ("TXT_PAPERS_BY_STATUS","Nb of Papers by Status");
define ("TXT_REVIEWS_BY_TOPICS","Nb of Reviews by Topics");

// Instanciate a template object
$tpl = new Template (".");

$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl"));

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Assignment of the template variables
$tpl->set_var("TITLE", "Statistics");

$config = GetConfig ($db);

// First check for access rights
 $session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  if (IsAdmin($session->email, $db))
  {
    $body="";
    if (isSet($_POST['selection'])) {
      $body.="<lo>";
      foreach ($_POST['selection'] as $id=>$fileGraph) {
	$chemin="./stats/$fileGraph";
	$body.="<li><img src=\"$chemin\">";
      } 
      $body.="</lo>";
    }
    else {
      $body.= FormSelectGraph();    
    }
    $tpl->set_var("BODY",$body);
  }
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");


function FormSelectGraph ()
{
  $graphs=array("NbPapersByTopics.php"=>TXT_NB_PAPER_BY_TOPIC,
		"NbSubmittedReviews.php"=>"Nb of Submitted Reviews",
		"NbPapers.php"=>"Nb of Submitted Papers",
		"NbPapersByStatus.php"=>"Nb of Papers by Status",
		"NbReviewByTopics.php"=>"Nb of Reviews by Topics",
		);

  $form = new Formulaire ("POST", "stats.php");   
  $form->debutTable(VERTICAL,1,1); 
  $form->champCheckBox ("Graphs selection", "selection[]", array(), $graphs, 1);
  $form->finTable();
  $form->champValider ("Submit", "submit");
  $form->ajoutTexte("<br><br><a href='Admin.php'>Back to the admin menu</a>");
  return $form->fin(false);
}

?>