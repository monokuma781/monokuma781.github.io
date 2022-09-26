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
$tpl->set_file (array 
		("Page" => TPLDIR . "Page.tpl",
		 "TxtErrorLoginAuthor" => TPLDIR . "TxtErrorLoginAuthor.tpl",
		 "TxtSubmitPaper" => TPLDIR . "TxtSubmitPaper.tpl",
		 "TxtPostPaper" => TPLDIR . "TxtPostPaper.tpl",
		 "TxtPostCameraReady" => TPLDIR . "TxtPostCameraReady.tpl",
		 "TxtPostError" => TPLDIR . "TxtPostError.tpl",
		 "MailConfirmUpload" => TPLDIR . "MailConfirmUpload.tpl",
		 "MailConfirmUpdate" => TPLDIR . "MailConfirmUpdate.tpl",
		 "MailConfirmCameraReady" => TPLDIR . "MailConfirmCameraReady.tpl",
		 "TxtLoginAuthor" => TPLDIR . "TxtLoginAuthor.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", "Paper submission");

// In principle, a file is required here. Change the variable 
// to 'false' if the form can be used for update paper infor. only
$file_required = true;

//Get the config array
$config = GetConfig($db);

if ($config['isSubmissionOpen'] == 'Y' or $config['isCameraReadyOpen']=='Y')
{
  // At this point, create an artificial uploaded file info., if
  // the megaupload package is used
  if ($config['use_megaupload'] == 'Y') {
    if (isSet($_REQUEST['sid'])) {
      $sid=$_GET['sid'];
      $_POST=getPostData("/tmp/", $sid);
      $upfile=array();
      $upfile['tmp_name']=$_POST["file_tmp_name"][0];
      $upfile['name']=$_POST["file_name"][0];
      $upfile['size']=$_POST["file_size"][0];
      $_FILES['file']=$upfile;
    }		
    else  {//no upload was specified
      $upfile=array();
      $upfile['tmp_name']="none";
      $upfile['name']="none";
      $upfile['size']=0;
      $_FILES['file']=$upfile;
    }
  }

  // After submission, insert
  if (isSet($_POST['title']))  {
    $messages = InsertPaper ($_POST, $_FILES['file'], $file_required,
			     $_POST['mode'], $TEXTS, $db);
    
    // Check whether errors have been met
    if (is_array($messages)) {
      // Error reporting
      $tpl->set_block("TxtPostError", "ERRORS_LIST", "ERRORS");
      $tpl->set_var("ERRORS", "");
      foreach ($messages as $message) {
	$tpl->set_var("ERROR_MESSAGE", $message);
	$tpl->parse("ERRORS", "ERRORS_LIST", true); 
      }
      $tpl->parse("INFO", "TxtPostError");
      $tpl->set_var("FormAbstract", 
		    FormAbstract ($_POST, MAJ, "SubmitPaper.php", 
				  $db, $TEXTS, true));
    }
    else {
      // Paper succesfully uploaded
      $id = $messages;
      $fileSize = $_FILES['file']['size'];
      $tpl->set_var("PAPER_FILE_SIZE", $fileSize);
      if($config['isCameraReadyOpen']=='Y') 
	$tpl->parse("INFO", "TxtPostCameraReady");
      else
	$tpl->parse("INFO", "TxtPostPaper");
      
      // Show the form with submitted values
      $paper = GetPaper ($id, $db);     
      $paper['confirmEmail'] = $paper['emailContact'];
      $tpl->set_var("FormAbstract", 
		    FormAbstract ($paper, MAJ, "SubmitPaper.php", $db, 
				  $TEXTS, $file_required)); 
      
      // Get the paper in the DB, and instanciate paper variables
      $paper = GetPaper ($id, $db, "object");     
      InstanciatePaperVars ($paper, $tpl, $db);
      $tpl->set_var ("CONF_ACRONYM", $config['confAcronym']);
      $tpl->set_var ("CONF_URL", $config['confURL']);
      
      // Send a mail to the contact author
      $tpl->set_var("PAPER_ABSTRACT",$paper->abstract);
      if($config['isCameraReadyOpen']=='Y') {
	$tpl->parse ("MAIL_BODY", "MailConfirmCameraReady");
	$mailSubject=   $TEXTS->get("SUBJ_CR_PAPER_UPLOAD");
      }
      else {
	if ($fileSize > 0) {
	  $tpl->parse ("MAIL_BODY", "MailConfirmUpload");
	  $mailSubject=   $TEXTS->get("SUBJ_PAPER_UPLOAD");
	}
	else {
	  $tpl->parse ("MAIL_BODY", "MailConfirmUpdate");
	  $mailSubject= $TEXTS->get("SUBJ_UPDATE_PAPER");
	}
      }
      $tpl->set_var("PAPER_ABSTRACT", nl2br($paper->abstract));
      
      // Send a copy to the conf. mail (depends on config.)
      if ($config['mailOnAbstract'] == 'Y')
	$confMail = $config['confMail'];
      else
	$confMail = "";
      
      SendMail ($paper->emailContact, $mailSubject . $paper->id ,
		$tpl->get_var("MAIL_BODY"), 
		$config['chairMail'], $config['chairMail'], $confMail);
    }
    $tpl->parse("BODY", "INFO");
    $tpl->parse("BODY", "FormAbstract", true);
    
  }
  else if (isSet($_POST['login'])) {
    if (isSet($_POST['sendpwd'])) {
      // Someone required to send the password of a paper
      $paper = GetPaper ($_POST['login'], $db);
      if (!$paper) {
	// This paper does not exists!! 
	$tpl->set_var("FORM_LOGIN_AUTHOR", FormLoginAuthor ());
	$tpl->parse("BODY", "TxtErrorLoginAuthor");
      }
      else {
	// OK. Send a mail to the contact author
	$password = PWDPaper($paper['id'],$config['passwordGenerator']);
	$email = $paper['emailContact'];
	$authors = GetAuthors($paper['id'], $db, false, "string", 
			      $paper['authors']);
	$tpl->set_file ("MailSendPwd", TPLDIR  . "MailSendPwd.tpl");
	$tpl->set_var("NAME_USER", $authors);
	$tpl->set_var("EMAIL_USER", $paper['id']);
	$tpl->set_var("PASSWORD_USER", $password);
	$tpl->parse ("MAIL_BODY", "MailSendPwd");	    
	SendMail ($email, $config['confAcronym'] . " " . 
		  $TEXTS->get("PWD_RECALL"), 
		  $tpl->get_var("MAIL_BODY"),
		  $config['chairMail'], $config['chairMail'], 
		  $config['chairMail']);
	$tpl->set_var ("BODY", $TEXTS->get("TXT_SEND_PWD"));
	$tpl->set_var("FORM_LOGIN_AUTHOR", FormLoginAuthor ());	  	 	  
	$tpl->parse("BODY", "FORM_LOGIN_AUTHOR", true);
      }
    }

  if (!isSet($_POST['sendpwd'])) {
    // Password not required: someone ask for paper update
    $paper = GetPaper ($_POST['login'], $db);
    $paper['confirmEmail'] = $paper['emailContact'];
    if (isSet($paper['id'])) 
      $password = PWDPaper($paper['id'],$config['passwordGenerator']);
    else $password="";

    // Check that the paper exists AND that the password is correct
    if (!isSet($paper['id']) or $_POST['password'] != $password) {	
	$tpl->set_var("FORM_LOGIN_AUTHOR", FormLoginAuthor ());
	$tpl->parse("BODY", "TxtErrorLoginAuthor");
    }
    else {
      // Everything is OK. Show the form
      $tmp_sid = md5(uniqid(rand(), true)); // SID used by cgi (mega upload)
      
      if($config['isCameraReadyOpen']=='Y') {
	// Check that the status does require a camera-ready file
	$qIsCRRequired="select cameraReadyRequired from PaperStatus s, ".
	  "Paper p where p.status=s.id and p.id='".$paper['id']."'";
	$rIsCRRequired=$db->execRequete($qIsCRRequired);
	$isCR=$db->objetSuivant($rIsCRRequired);
	if ($isCR->cameraReadyRequired=='Y') {
	  $tpl->set_var("FormAbstract", 
			FormAbstract ($paper, MAJ, 
				      "SubmitPaper.php", $db,
				      $TEXTS, $file_required, $tmp_sid));
	  $tpl->parse("BODY", "TxtSubmitPaper");
	  $tpl->parse("BODY", "FormAbstract", true);
	}
	else {
	  $tpl->set_var("FormLoginAuthor", FormLoginAuthor ());
	  $tpl->set_var("BODY", $TEXTS->get("TXT_CANNOT_UPLOAD"));
	  $tpl->parse("BODY", "FormLoginAuthor", true);
	}
      }
      else {
	$tpl->set_var("FormAbstract", 
		      FormAbstract ($paper, MAJ, "SubmitPaper.php", 
				    $db, $TEXTS, $file_required, $tmp_sid));
	$tpl->parse("BODY", "TxtSubmitPaper");
	$tpl->parse("BODY", "FormAbstract", true);
      }
    }
  }
  }
  else {
    // Just show the login form
    $tpl->set_var("FORM_LOGIN_AUTHOR", FormLoginAuthor());
    $tpl->parse("BODY", "TxtLoginAuthor");
  }
}
else {
  // Submission is closed
  $tpl->set_var("BODY", $TEXTS->get("TXT_SUBMISSION_CLOSED")); 
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");
?>