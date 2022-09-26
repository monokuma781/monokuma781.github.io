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
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
                    "TxtInfoReview" => TPLDIR . "TxtInfoReview.tpl",
                   "TxtInfoPostReview" => TPLDIR . "TxtInfoPostReview.tpl",
                   "TxtShowTblReview" => TPLDIR . "TxtShowTblReview.tpl",
                   "Forum" => TPLDIR . "Forum.tpl",
                   "MailMessage" => TPLDIR . "MailMessage.tpl",
                   "TxtPapersToReview" => TPLDIR . "TxtPapersToReview.tpl"));

$config = GetConfig($db);

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_REVIEWER_PAGE"));

// First check for access rights
if (isSet($_GET['logout']))
{
  // Delete the current session
  $q = "DELETE FROM Session WHERE idSession='" . session_id() . "'";
  $db->execRequete($q);
}

$session = CheckAccess ("Review.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  // Actions if the id of a paper is submitted
    if (isSet($_REQUEST['idPaper']))
      {
	$idPaper = $_REQUEST['idPaper'];

        // Check that the paper is REALLY assigned to the reviewer
	if ($review = GetReview($idPaper, $session->email, $db))
	  {
	    if (isSet($_POST['message']))
	      {
		// A new message has been posted
		SQLMessage ($_POST, $db);
		
		// Send a mail to all reviewers
		$tabReviewers = GetReviewers($idPaper, $db);
		$mails = ""; $comma="";
		do {
		  $rev=current($tabReviewers);
		  $mails .= $comma . $rev->email;
		  $comma = ", "; 
		} while (next($tabReviewers));
		$member = GetMember($session->email, $db, "object");
		InstanciateMemberVars($member, $tpl, $db);
		$paper = GetPaper($idPaper, $db, "object");
		InstanciatePaperVars($paper, $tpl, $db);
		$tpl->set_var("CONF_URL", $config['confURL']);
		$tpl->set_var("MESSAGE", $_POST['message']) ;
		$tpl->parse("FULL_MESSAGE", "MailMessage") ;
		SendMail ($mails, 
			  $config['confAcronym'] . " " . 
			  "New comment on paper $idPaper",
			  $tpl->get_var("FULL_MESSAGE"), 
			  $config['chairMail'], $config['chairMail'], 
			  $config['chairMail']);

		// Show the papers assigned to the reviewer
		AdmReviewerPapers ($session->email, $tpl, $db, $TEXTS);
	      }
	    else if (isSet($_REQUEST['newMessage']))
	      {
		// Add a message on this paper
		if (isSet($_REQUEST['idParent']))
		  $idParent = $_REQUEST['idParent'];
		else $idParent=0;
		$tpl->set_var("BODY", 
		      FormMessage ($idPaper, $idParent, $session->email,
				   "Review.php", $db, $TEXTS));
	      }
	    else
	      {
		// Form to enter a review
		ManageReview ($_POST, $review, $tpl, $db, $TEXTS);
	      }
	  }
	else
	  {
	    $tpl->set_var("INFO", "You do NOT have to access this paper");
	    $tpl->parse("BODY", "INFO");
	  }
      }
    else
      {
	// Show the papers assigned to the reviewer
	AdmReviewerPapers ($session->email, $tpl, $db, $TEXTS);
      }
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>
