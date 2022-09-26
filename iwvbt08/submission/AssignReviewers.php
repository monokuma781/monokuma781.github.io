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
 
 
session_start();

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");
require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
                "TxtPostReviewer" => TPLDIR . "TxtPostReviewer.tpl",
                "TxtPaperAsList" => TPLDIR . "TxtPaperAsList.tpl",
                "TxtPostError" => TPLDIR . "TxtPostError.tpl"));

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Assignment of the template variables
$tpl->set_var("TITLE", "Assignment of reviewers to paper " . 
                    $_REQUEST['idPaper']);

$tpl->set_var("BODY", "");

// First check for access rights
 $session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  // For chairs only
  if (strstr($session->roles, "C"))
  {
    // Should exist...
    $idPaper = $_REQUEST['idPaper'];

    // After submission, insert
    if (isSet($_POST['tabMails']))
      {
	$message = 
	   InsertReviewers ($_POST['idPaper'], $_POST['tabMails'], $db);

	if (!empty($message))
	  {
	    $tpl->set_var("ERROR_MESSAGE", $message); 
            $tpl->parse("INFO", "TxtPostError");
	  }
	else
	  {
	    $tpl->parse("INFO", "TxtPostReviewer");
	  }
	$tpl->parse("BODY", "INFO");
      }

    /* Removal of a reviewer from a paper ? */
    if (isSet($_GET['remove']))
	DeleteReview ($_GET['idPaper'], $_GET['remove'], $db);

    // Always display the reviewers list and the paper

    $paper = GetPaper ($idPaper, $db, "object");
    InstanciatePaperVars ($paper, $tpl, $db);

    $tpl->set_var("FORM_REVIEWERS", 
		  FormAssignReviewers ($idPaper, 
				       "AssignReviewers.php", $db));

    $tpl->parse("BODY", "TxtPaperAsList", true);
  }
  else
    $tpl->set_var("BODY", "This page is for administrators only");
}


// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>
