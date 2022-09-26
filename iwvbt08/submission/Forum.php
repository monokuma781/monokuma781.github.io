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
			"TxtShowTblReview" => TPLDIR . "TxtShowTblReview.tpl",
			"Forum" => TPLDIR . "Forum.tpl",
			"MailMessage" => TPLDIR . "MailMessage.tpl",
			"TxtPapersInForum" => TPLDIR . "TxtPapersInForum.tpl"));

$config = GetConfig($db);


// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_FORUM_PAGE"));


if ($config['discussion_mode'] == GLOBAL_DISCUSSION)
{
  // Initialize the current interval
  if (!isSet($_REQUEST['iMin']))
    {
      $iMinCur = 1; $iMaxCur = SIZE_RATING;
    }
  else
    {
      $iMinCur = $_REQUEST['iMin'];  $iMaxCur = $_REQUEST['iMax'];
    }
  $tpl->set_var("IMIN_CUR", $iMinCur);
  $tpl->set_var("IMAX_CUR", $iMaxCur);
  $tpl->set_block("TxtPapersInForum", "PAPER_DETAIL", "PAPERS");
  $tpl->set_var("PAPERS","");
  $tpl->set_block("TxtPapersInForum", "GROUPS_LINKS", "LINKS");
  $tpl->set_var("LINKS", "");

  // First check for access rights
  $session = CheckAccess ("Review.php", $_POST, session_id(), $db, $tpl);

  if (is_object($session))
    {
      // Actions if the id of a paper is submitted
      if (isSet($_REQUEST['idPaper']))
	{
	  $idPaper = $_REQUEST['idPaper'];

	  // Check that the paper is not in conflict with the reviewer
	  if (GetRatingValue ($idPaper, $session->email, $db) != 0)
	    {
	      if (isSet($_POST['message']))
		{
		  // A new message has been posted
		  SQLMessage ($_POST, $db);
		
		  // Show the papers assigned to the reviewer
		  AdmForum ($session->email, $tpl, $db, $TEXTS,
			    $iMinCur, $iMaxCur);
		}
	      else if (isSet($_REQUEST['newMessage']))
		{
		  // Add a message on this paper
		  if (isSet($_REQUEST['idParent']))
		    $idParent = $_REQUEST['idParent'];
		  else $idParent=0;
		  $tpl->set_var("BODY", 
				FormMessage ($idPaper, $idParent, $session->email,
					     "Forum.php", $db, $TEXTS));
		}
	    }
	  else
	    {
	      $tpl->set_var("INFO", "You cannot access this paper");
	      $tpl->parse("BODY", "INFO");
	    }
	}
      else
	{
	  // Show the papers assigned to the reviewer
	  AdmForum ($session->email, $tpl, $db, $TEXTS, $iMinCur, $iMaxCur);
	}
    }
}
else
$tpl->set_var("BODY", "This function is disabled");

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>
