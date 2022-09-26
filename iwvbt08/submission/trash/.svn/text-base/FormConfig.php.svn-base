<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2004 Philippe Rigaux
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
require_once ("Formulaire.class.php");

function FormConfig ($email, $config, $target, 
		     &$TEXTS, &$CODES)
{
  global $FILE_TYPES; // Array of possible types

  // Create the form
  $form = new Formulaire ("POST", $target);

  $yesNo = array ('Y'  => $TEXTS->get("TXT_YES"),
		       'N' => $TEXTS->get("TXT_NO"),);
 
  $form->debutTable(VERTICAL, array("BORDER"=>1), 1,
		    $TEXTS->get("FRM_CONFIG_TITLE"));

  /* Show the admin. login and the current password */

  $form->champPlain ($TEXTS->get("FRM_LOGIN_PASSWORD"), $email . " / " . 
		     PWDMember($email, $config['passwordGenerator']));

  $form->champTexte ($TEXTS->get("FRM_CONF_ACRONYM"),
		     "confAcronym", $config['confAcronym'], 20);
  $form->champTexte ($TEXTS->get("FRM_CONF_NAME"),
		     "confName", $config['confName'], 50, 100);
  $form->champTexte ($TEXTS->get("FRM_CONF_URL"),
		     "confURL", $config['confURL'], 50, 100);
  $form->champTexte ($TEXTS->get("FRM_CONF_MAIL"),
		     "confMail", $config['confMail'], 60);
  $form->champTexte ($TEXTS->get("FRM_CHAIR_MAIL"),
		     "chairMail", $config['chairMail'], 60);
  $form->champTexte ($TEXTS->get("FRM_PASSWORD_GENERATOR"),
		     "passwordGenerator", $config['passwordGenerator'], 10);
  $form->champTexte ($TEXTS->get("FRM_UPLOAD_DIR"),
		     "uploadDir", $config['uploadDir'], 30);

  $currentTypes = explode(';', $config['fileTypes']);
  foreach ($currentTypes as $key => $val)  $defArray[$val] = $val;
  $form->champCheckBox ($TEXTS->get("FRM_FILE_TYPES"), "fileTypes[]", 
		     $defArray,  $FILE_TYPES);

  $form->champRadio ($TEXTS->get("FRM_BLIND_REVIEW"), 
		     "blind_review", 
		     $config['blind_review'],  $yesNo);
  $form->champRadio ($TEXTS->get("FRM_TWO_PHASES"), 
		     "two_phases_submission", 
		     $config['two_phases_submission'],  $yesNo);
  $form->champRadio ($TEXTS->get("FRM_EXTENDED_SUB_FORM"), 
		     "extended_submission_form", 
		     $config['extended_submission_form'],  $yesNo);

  $form->champRadio ($TEXTS->get("FRM_ABSTRACT_SUBMISSION_OPEN"), 
		     "isAbstractSubmissionOpen", 
		     $config['isAbstractSubmissionOpen'],  $yesNo);
  $form->champRadio ($TEXTS->get("FRM_SUBMISSION_OPEN"), "isSubmissionOpen", 
		     $config['isSubmissionOpen'],  $yesNo);

  $form->champRadio ($TEXTS->get("FRM_DISCUSSION_MODE"), "discussion_mode", 
		     $config['discussion_mode'],  
		     $CODES->get("discussion_mode"));

  $form->champRadio ($TEXTS->get("FRM_BALLOT_MODE"), "ballot_mode", 
		     $config['ballot_mode'], $CODES->get("ballot_mode"));

  $form->champRadio ($TEXTS->get("FRM_CR_OPEN"), "isCameraReadyOpen", 
		     $config['isCameraReadyOpen'],  $yesNo);
  $form->champTexte ($TEXTS->get("FRM_NB_REVIEWERS_PER_PAPER"),
		     "nbReviewersPerItem", $config['nbReviewersPerItem'], 2);

  // Mails
  $form->champRadio ($TEXTS->get("FRM_MAIL_ON_ABSTRACT"), "mailOnAbstract", 
		     $config['mailOnAbstract'],  $yesNo);
  $form->champRadio ($TEXTS->get("FRM_MAIL_ON_UPLOAD"), "mailOnUpload", 
		     $config['mailOnUpload'],  $yesNo);
  $form->champRadio ($TEXTS->get("FRM_MAIL_ON_REVIEW"), "mailOnReview", 
		     $config['mailOnReview'],  $yesNo);

  // Deadlines
  $form->champTexte ($TEXTS->get("FRM_SUBMISSION_DEADLINE"), 
		     "submissionDeadline",
		     DBtoDisplay($config['submissionDeadline']), 10);
  $form->champTexte ($TEXTS->get("FRM_REVIEW_DEADLINE"), "reviewDeadline",
		     DBtoDisplay($config['reviewDeadline']), 10);
  $form->champTexte ($TEXTS->get("FRM_CR_DEADLINE"), "cameraReadyDeadline",
		     DBtoDisplay($config['cameraReadyDeadline']), 10);

  $form->finTable();

  $form->champValider ($TEXTS->get("FRM_SUBMIT"), "submit");

  return $form->fin(false);
}

?>