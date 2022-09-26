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
                   "TxtAbstract" => TPLDIR . "TxtSubmitAbstract.tpl",
                   "TxtPostError" => TPLDIR . "TxtPostError.tpl",
                   "MailConfirmAbstract" => TPLDIR . "MailConfirmAbstract.tpl",
                   "TxtPostAbstract" => TPLDIR . "TxtPostAbstract.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_ABSTRACT_SUBMISSION"));

$config = GetConfig($db);

// Check whether the file upload must be proposed
if ($config['two_phases_submission'] == 'Y')
     $upload = false;
     else
     $upload= true;
     
if ($config['isAbstractSubmissionOpen'] == 'Y' 
    and $config['isSubmissionOpen'] == 'Y'
    and $config['isCameraReadyOpen'] == 'N')
{
  // An abstract has been uploaded? Then insert.
  if (isSet($_POST['title']))
    {
      // Check whether the file has been uploaded
      if (isSet($_FILES['file'])) 
	$file =$_FILES['file'];
      else 
	$file=array("tmp_name" => "none");
      
      // Try to insert the paper
      $messages = InsertPaper ($_POST, $file, $upload, $_POST['mode'],
			       $TEXTS, $db);

      // Error?
      if (is_array($messages))
	{
	  // Error reporting
	  $tpl->set_block("TxtPostError", "ERRORS_LIST", "ERRORS");
	  $tpl->set_var("ERRORS", "");
	  foreach ($messages as $message)
	    {
	      $tpl->set_var("ERROR_MESSAGE", $message);
	      $tpl->parse("ERRORS", "ERRORS_LIST", true); 
	    }
	  $tpl->parse("INFO", "TxtPostError");
	  $tpl->set_var("FormAbstract", 
			FormAbstract ($_POST, INSERTION, "SubmitAbstract.php", 
				      $db, $TEXTS, $upload));
	}
      else
	{
	  // Succesful insertion
	  $id = $messages;
	  $paper = GetPaper($id, $db, "object");
	  InstanciatePaperVars ($paper, $tpl, $db);
 	  InstanciateConfigVars($config, $tpl);

	  $password = PWDPaper($id, $config['passwordGenerator']);
	  $tpl->set_var("LOGIN", $id);
	  $tpl->set_var("PASSWORD", $password);
	  $tpl->parse("INFO", "TxtPostAbstract");
	  $tpl->set_var("FormAbstract", " ");
      
	  // Send a mail to the contact author
	  $tpl->set_var ("PAPER_PASSWORD", $password);

	  // Create the body 
	  $tpl->set_var("PAPER_ABSTRACT",$paper->abstract);
	  $tpl->parse ("MAIL_BODY", "MailConfirmAbstract");
	  $tpl->set_var("PAPER_ABSTRACT", nl2br($paper->abstract));
	  
	  // Send a copy to the conf. mail (depends on config.)
	  if ($config['mailOnAbstract'] == 'Y')
	      $confMail = $config['confMail'];
	  else
	      $confMail = "";
	    
	  SendMail ($paper->emailContact, 
		    $TEXTS->get("SUBJ_ABSTRACT_SUBMISSION")
		    . $config['confAcronym'],
		    $tpl->get_var("MAIL_BODY"), 
		$config['chairMail'], $config['chairMail'], $confMail);
	}

      $tpl->parse("BODY", "INFO");
      $tpl->parse("BODY", "FormAbstract", true);
    }
  else 
    {
      // Display the form
      $tpl->set_var("FormAbstract", 
          FormAbstract ($_POST, INSERTION, "SubmitAbstract.php", 
			$db, $TEXTS, $upload));
      $tpl->parse("BODY", "TxtAbstract");
      $tpl->parse("BODY", "FormAbstract", true);
    }
}
else
{
  // Submission is closed
  $tpl->set_var("BODY", $TEXTS->get("TXT_ABSTRACT_SUBMISSION_CLOSED")); 
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>
