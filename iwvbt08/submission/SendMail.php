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
$tpl->set_file ( array 
		 ("Page" => TPLDIR . "Page.tpl",
		  "TxtAckMail" => TPLDIR . "TxtAckMail.tpl",
		  "TxtShowReview" => TPLDIR . "TxtShowReview.tpl",
		  "TxtShowAuthorsReview" => TPLDIR . "TxtShowAuthorsReview.tpl",
		  "MailSelectTopics" => TPLDIR . "MailSelectTopics.tpl",
		  "MailRatePapers" => TPLDIR . "MailRatePapers.tpl",
		  "MailParticipateForum" => TPLDIR . "MailParticipateForum.tpl",
		  "FreeMail" => TPLDIR . "FreeMail.tpl",
		  "MailReviewerInstructions" => TPLDIR . "MailReviewerInstructions.tpl",
		  "MailActionRequired" => TPLDIR . "MailActionRequired.tpl",
		  "MailStatusOfPaper" => TPLDIR . "MailStatusOfPaper.tpl"));

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Assignment of the template variables
$tpl->set_var("TITLE", "Send an email");

$config = GetConfig ($db);

// First check for access rights
 $session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl);

if (is_object($session)) {
  if (strstr($session->roles, "C"))  {
    if (isSet($_POST['sendmail']))    {
      // The form has been submitted: send the mail
      $to = stripSlashes($_POST['to']);
      $from = $config['confMail'];
      $mail =   stripSlashes($_POST['body']);
      $subject = stripSlashes($_POST['subject']);
      
      if ($to == ALL_REVIEWERS)	{
	// Loop on the reviewers, instanciate and send the mail
	$tpl->set_var("TEMPLATE", $mail);
	
	$qMembers = "SELECT * FROM PCMember WHERE roles LIKE '%R%'";
	$rMembers = $db->execRequete ($qMembers);
	while ($member = $db->ligneSuivante($rMembers)) {
	  $to = $member['email'];
	  InstanciateConfigVars ($config, $tpl);
	  // Instanciate all variables present in reviewers messages
	  $message = InstanciateMailReviewer ("TEMPLATE",
					      $config, $member, $tpl, $db);
	  
	  SendMail ($to, $subject, $message, 
		    $from, $from, $config['chairMail']);
	}
	$tpl->set_var("BODY", "The mail has been sent to all reviewers<p>");
      }
      else if ($to == ALL_AUTHORS) {
	// Loop on the contact authors, instanciate and send the mail
	$tpl->set_var("TEMPLATE", $mail);
	
	$qPapers = "SELECT * FROM Paper WHERE status IS NOT NULL";
	$rPapers = $db->execRequete ($qPapers);
	while ($paper = $db->objetSuivant($rPapers))
	  {
	    $subjectWithID = $subject . " " . $paper->id;
	    $to = $paper->emailContact;
	    // Instanciate all variables present in authors messages
	    $message = ""; // For security...
	    
	    $message = InstanciateMailAuthor ($config, $paper, $tpl, $db);
	    SendMail ($to, $subjectWithID, $message, 
		      $from, $from, $config['chairMail']);
	  }
	$tpl->set_var("BODY", "The mail has been sent to all authors<p>");
      }
      else if ($to == ALL_AUTHORS_FREE)	{
	// Loop on the contact authors, instanciate and send the mail
	$tpl->set_var("TEMPLATE", $mail);
	
	$qPapers = "SELECT * FROM Paper";
	$rPapers = $db->execRequete ($qPapers);
	while ($paper = $db->objetSuivant($rPapers)) {
	  $subjectWithID = $subject . " " . $paper->id;
	  $to = $paper->emailContact;
	  // Send the free mail text
	  SendMail ($to, $subjectWithID, $mail, 
		    $from, $from, $config['chairMail']);
	}
	$tpl->set_var("BODY", "The mail has been sent to all authors<p>");
      }
      else if ($to == ALL_AUTHORS_ACCEPTED)	{
	// Loop on the contact authors of accepted papers, 
	// instanciate and send the mail
	$tpl->set_var("TEMPLATE", $mail);

	$qPapers="SELECT p.* FROM PaperStatus s, Paper p ".
	  "WHERE p.status=s.id and cameraReadyRequired='Y' ";
	$rPapers = $db->execRequete ($qPapers);
	while ($paper = $db->objetSuivant($rPapers)) {
	  $subjectWithID = $subject . " " . $paper->id;
	  $to = $paper->emailContact;
	  // Send the free mail text
	  SendMail ($to, $subjectWithID, $mail, 
		    $from, $from, $config['chairMail']);
	}
	$tpl->set_var("BODY", 
		      "The mail has been sent to authors of accepted papers<p>");
      }
      else {
	// Send the mail
	SendMail ($to, $subject, $mail, 
		  $from, $from, $config['chairMail']);
	// Show the mail just sent
	$tpl->set_var("MAIL_TO", $to);
	$tpl->set_var("MAIL_SUBJECT", $subject);
	$tpl->set_var("MAIL_BODY", String2HTML($mail));
	$tpl->parse("BODY", "TxtAckMail");
      }
    }

    // Show the mail text in the form
    if (isSet($_REQUEST['idMessage']))
      if ($_REQUEST['idMessage'] == PWD_REVIEWER
	or $_REQUEST['idMessage'] == MAIL_RATE_PAPERS
	or $_REQUEST['idMessage'] == MAIL_PARTICIPATE_FORUM
	or $_REQUEST['idMessage'] == MAIL_SELECT_TOPICS
	or $_REQUEST['idMessage'] == FREE_MAIL)
      {
	if ($_REQUEST['idMessage'] == MAIL_RATE_PAPERS)
	  {
	    $subject = $config['confAcronym'] . " " . 
	      $TEXTS->get("SUBJ_PAPER_RATING");
	    $template = "MailRatePapers";
	  }
	if ($_REQUEST['idMessage'] == MAIL_PARTICIPATE_FORUM)
	  {
	    $subject = $config['confAcronym'] . " " . 
	      $TEXTS->get("SUBJ_PAPER_RATING");
	    $template = "MailParticipateForum";
	  }
	if ($_REQUEST['idMessage'] == PWD_REVIEWER)
	  {
	    $subject = $config['confAcronym'] . " " 
	       . $TEXTS->get("SUBJ_REVIEWERS_INSTRUCTIONS");
	    $template = "MailReviewerInstructions";
	  }
	if ($_REQUEST['idMessage'] == MAIL_SELECT_TOPICS)
	  {
	    $subject = $config['confAcronym'] . " " . 
	      $TEXTS->get("SUBJ_SELECT_TOPICS");
	    $template = "MailSelectTopics";
	  }
	if ($_REQUEST['idMessage'] == FREE_MAIL)
	  {
	    $subject = "";
	    $template = "FreeMail";
	  }

	if (isSet($_REQUEST['to']))
	  {
	    // Mail to one reviewer
	    $member = GetMember ($_REQUEST['to'], $db);
	    $to = $member['email'];
	    $message=  InstanciateMailReviewer 
	       ($template, $config, $member, $tpl, $db);
	  }	
	else if (isSet($_REQUEST['all_reviewers']))
	  {
	    // Batch mail. Just show the template
	    $to =  ALL_REVIEWERS;
	    $tpl->parse("MESSAGE", $template);
	    $message=  $tpl->get_var("MESSAGE");
	  }
	else if (isSet($_REQUEST['all_authors']))  {
	    // Batch mail. Just show the template
	    $to =  ALL_AUTHORS_FREE;
	    $tpl->parse("MESSAGE", $template);
	    $message=  $tpl->get_var("MESSAGE");
	  }
	else if (isSet($_REQUEST['all_authors_accepted']))  {
	    // Batch mail. Just show the template
	    $to =  ALL_AUTHORS_ACCEPTED;
	    $tpl->parse("MESSAGE", $template);
	    $message=  $tpl->get_var("MESSAGE");
	  }

	$tpl->set_var("BODY", FormSendMail ($to,stripSlashes($subject),
					    $message));
      }

    // Send a mail to the reviewers of a paper, with reviews
    if (isSet($_REQUEST['idMessage']))
     if ($_REQUEST['idMessage'] == REVIEWS_TO_REVIEWERS)
      {
	$idPaper = $_REQUEST['idPaper'];
	$paper = GetPaper ($idPaper, $db);
	$subject = $config['confAcronym'] . " - " 
	  . $TEXTS->get("SUBJ_ACTION_REQUIRED") . "#$idPaper";

	$tpl->set_var("PAPER_ID", $paper['id']);
        $tpl->set_var ("PAPER_TITLE", $paper['title']);
        $tpl->set_var ("PAPER_AUTHORS",
		       GetAuthors($paper['id'], $db, $config['blind_review'], 
				  "string", $paper['authors']));
	$tpl->set_var("REVIEWS", 
		      DisplayReviews ($idPaper, "TxtShowReview", $tpl, $db));

	// Get the mail addresses
	$tabReviewers = GetReviewers($idPaper, $db);
	$comma = $mails = "";
	do {
	  $rev=current($tabReviewers);
	  $mails .= $comma . $rev->email;
	  $comma = ", "; 
	} while (next($tabReviewers));
    
	// Create the message
	$tpl->parse("Mail", "MailActionRequired");
	$message = $tpl->get_var("Mail");
	$tpl->set_var("BODY", FormSendMail ($mails,
					    stripSlashes($subject),
					    $message));
      }

    // Send a mail to the authors of a paper, with reviews
    if (isSet($_REQUEST['idMessage']))
     if ($_REQUEST['idMessage'] == STATUS_TO_AUTHORS)
      {
        $subject = $config['confAcronym'] . " "
	       . $TEXTS->get("SUBJ_STATUS_PAPER");

	if (isSet($_REQUEST['idPaper']))
	  {
	    // Mail for one paper
	    $idPaper = $_REQUEST['idPaper'];
	    $paper = GetPaper ($idPaper, $db, "object");
	    $subject .= " " . $paper->id;
	    $to = $paper->emailContact;
	    $message =InstanciateMailAuthor ($config, $paper, $tpl, $db);
	  }	
	else
	  {
	    // Batch mail. Check that all papers have a status,
            // and that there are no missing reviews
          $qPaper = "SELECT count(*) AS count FROM Paper WHERE status IS NULL";
          $res = GetRow ($qPaper, $db, "object");
          if ($res->count > 0)
            {
              FatalError (sprintf (FE_PAPERS_WITHOUT_STATUS, $res->count));
            }

         $qReview = "SELECT count(*) AS count FROM Review "
                        . "WHERE overall IS NULL";
          $res = GetRow ($qReview, $db, "object");

          if ($res->count > 0)
            {
              FatalError (sprintf (FE_MISSING_REVIEWS, $res->count));
            }

          // OK. Now give the list of the templates that will be used
          $tpl->set_block("MailStatusOfPaper", 
                               "MAIL_TEMPLATE", "MAIL_TEMPLATES");
          $tpl->set_var("MAIL_TEMPLATES",""); 
          $sList = GetListStatus ($db);
          foreach ($sList as $id => $sVals)
          {
            $tpl->set_var("TEMPLATE_FILE", $sVals['mailTemplate']);
            $tpl->set_var("STATUS", $sVals['label']);
            $tpl->parse ("MAIL_TEMPLATES", "MAIL_TEMPLATE", true);
          }

          // Done. Finally show the message
	    $to =  ALL_AUTHORS;
	    $tpl->parse("MESSAGE", "MailStatusOfPaper");
	    $message=  $tpl->get_var("MESSAGE");
	  }

	$tpl->set_var("BODY", 
		      FormSendMail ($to, stripSlashes($subject),
				    $message));
      }
  }
}
// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>