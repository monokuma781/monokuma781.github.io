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
 
// Simple script to show informations about a paper

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");
require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
$config=GetConfig($db);

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
			"TxtShowTblReview" => TPLDIR . "TxtShowTblReview.tpl",
			"Forum" => TPLDIR . "Forum.tpl",
			"TxtShowInfo" => TPLDIR . "TxtShowInfo.tpl"));

// First check for access rights.
$idSession = $_REQUEST['idSession'];

$session = CheckAccess ("ShowInfo.php", $_POST, 
			 $idSession, $db, $tpl);

if (is_object($session))
{

  $idPaper = $_REQUEST['idPaper'];
  $paper = GetPaper ($idPaper, $db, "object");

  InstanciatePaperVars ($paper, $tpl, $db, $idSession);

  // Default: print only the basic informations. Do not show the reviews
  // nor the forum
  $tpl->set_block("TxtShowInfo", "BLOCK_QUESTIONS", "INFO_QUESTIONS");
  $tpl->set_block("TxtShowInfo", "BLOCK_REVIEWERS", "INFO_REVIEWERS");
  $tpl->set_block("TxtShowInfo", "BLOCK_REVIEWS", "INFO_REVIEWS");
  $tpl->set_var("INFO_QUESTIONS", "");
  $tpl->set_var("INFO_REVIEWERS", "");
  $tpl->set_var("INFO_REVIEWS", "");
  $tpl->set_var("FORUM", "");

  // Put the questions
  $q_questions = "SELECT * FROM PaperQuestion q, PQChoice c, PaperAnswer a "
    . " WHERE q.id=c.id_question AND a.id_answer=c.id_choice "
    . " AND id_paper=$idPaper";
  $rq = $db->execRequete($q_questions);
  while ($question = $db->objetSuivant($rq)) {
    $tpl->set_var("QUESTION", $question->question);
    $tpl->set_var("ANSWER", $question->choice);
    $tpl->parse ("INFO_QUESTIONS", "BLOCK_QUESTIONS", true);
  }

  // Check the context to determine what must be printed
  if (IsAdmin($session->email, $db)
      or isSet ($_REQUEST['in_forum']))
    {
      // It is an administrator. Print everything
      $tpl->set_var("REVIEWS", DisplayReviews ($idPaper, 
					       "TxtShowTblReview", $tpl, $db,
					       "", true));
      // Show the reviewers
      $reviewers = $comma = "";
      $tabReviewers = GetReviewers ($idPaper, $db);
      if ($tabReviewers) do {
	  $rev=current($tabReviewers);
          $reviewers .= $comma ." ". $rev->firstName . " ". $rev->lastName;
	  $comma = ", "; 
	} while (next($tabReviewers));
      $tpl->set_var("LIST_REVIEWERS", $reviewers);
      $tpl->parse("INFO_REVIEWERS", "BLOCK_REVIEWERS");

      // Add the messages if the discussion is opened
      if ($config['discussion_mode'] != NO_DISCUSSION)
	$tpl->set_var("FORUM", DisplayMessages($idPaper, 0, $db, false));
    }
   else if ($review = GetReview($idPaper, $session->email, $db))
     {
       // It is a reviewer, check the 'allReviews' parameter
       if (isSet($_REQUEST['allReviews']))
	 if ($_REQUEST['allReviews'])
	   {

	     $tpl->set_var("REVIEWS", DisplayReviews ($idPaper, 
					       "TxtShowTblReview", $tpl, $db,
					       "", true));
	   }
	 else
	   {
	     $tpl->set_var("REVIEWS", 
			   DisplayReviews ($idPaper, 
					   "TxtShowTblReview", $tpl, $db,
					   $session->email, true));
	   }
	  // Add the messages if the discussion is open
	 if ($config['discussion_mode'] != NO_DISCUSSION)
	   $tpl->set_var("FORUM", DisplayMessages($idPaper, 0, $db, false));
     }

  // Take account of the 'noReview' and 'noForum' parameter
  if (isSet($_REQUEST['noReview'])) 
    $tpl->set_var("INFO_REVIEWS", "");
  else
    $tpl->parse("INFO_REVIEWS", "BLOCK_REVIEWS");
    
  if (isSet($_REQUEST['noForum'])) $tpl->set_var("FORUM", "");

  $tpl->pparse("RESULT", "TxtShowInfo");
}
else
{
  echo "You do not have access to this page";
}
?>