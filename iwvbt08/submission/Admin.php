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
require_once ("SetupTexts.php"); 
// Instanciate a template object 
$tpl = new Template ("."); 
// Connect to the database 
$db = new BD (NAME, PASS, BASE, SERVER); 
// Set the standard conf. infos 
SetStandardInfo ($db, $tpl); 
 
// Load the required files and assign them to variables 
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl", 
			"TxtInfoAdmin" => TPLDIR . "TxtInfoAdmin.tpl", 
			"TxtListPapers" => TPLDIR . "TxtListPaper.tpl", 
			"TxtAcceptedPapers" => TPLDIR . "TxtAcceptedPapers.tpl", 
			"TxtAcceptedPapersSimple" => TPLDIR . "TxtAcceptedPapersSimple.tpl", 
			"TxtSummaryAssignment" => TPLDIR . "TxtSummaryAssignment.tpl", 
			"TxtPostBallot" => TPLDIR . "TxtPostBallot.tpl", 
			"TxtReportAssignment" => TPLDIR . "TxtReportAssignment.tpl", 
			"SQL" => TPLDIR . "SQL.tpl", 
			"Forum" => TPLDIR . "Forum.tpl", 
			"TxtPostError" => TPLDIR . "TxtPostError.tpl", 
			"TxtPostMember" => TPLDIR . "TxtPostMember.tpl", 
			"TxtInfoSelectionNotClosed" => TPLDIR . "TxtInfoSelectionNotClosed.tpl", 
			"TxtInfoSelectionClosed" => TPLDIR . "TxtInfoSelectionClosed.tpl", 
			"TxtInfoSubmissionClosed" => TPLDIR . "TxtInfoSubmissionClosed.tpl", 
			"TxtInfoPredictionCreated" =>  
			TPLDIR . "TxtInfoPredictionCreated.tpl", 
                        "TxtListMembers" => TPLDIR . "TxtListMembers.tpl")); 
 
$nomScript = "Admin.php"; 
if (!isSet($_REQUEST['action'])) 
     $context = $nomScript; 
     else 
     $context = $nomScript . "?action=" . $_REQUEST['action']; 
 
$config = GetConfig ($db); 
 
// Assignment of the template variables 
$tpl->set_var("TITLE", "Administration tasks"); 
     
// First check for access rights 
if (isSet($_GET['logout'])) 
{ 
  // Delete the current session 
  $q = "DELETE FROM Session WHERE idSession='" . session_id() . "'"; 
  $db->execRequete($q); 
} 
 
$session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl); 
 
if (is_object($session)) 
{ 
  if (strstr($session->roles, "A") or strstr($session->roles, "C")) 
    { 
      $tpl->set_block("TxtInfoAdmin", "ADMIN_CHOICES"); 
      $tpl->set_block("TxtInfoAdmin", "CHAIR_CHOICES"); 
      $tpl->set_block("CHAIR_CHOICES", "PARTICIPATE_FORUM"); 
      if (!strstr($session->roles,"A"))	$tpl->set_var("ADMIN_CHOICES", ""); 
      if (!strstr($session->roles,"C"))	$tpl->set_var("CHAIR_CHOICES", ""); 
 
      if ($config['discussion_mode'] != GLOBAL_DISCUSSION) 
	  $tpl->set_var("PARTICIPATE_FORUM", ""); 
 
      // If the current selection is updated, record it 
     if (isSet($_POST['spStatus'])) 
        { 
	  $spTitle = $_POST['spTitle']; 
	  $spAuthor = $_POST['spAuthor']; 
	  $spUploaded = $_POST['spUploaded']; 
	  $spStatus = $_POST['spStatus']; 
	  $spFilter = $_POST['spFilter']; 
	  $spRate = $_POST['spRate']; 
	  $spTopic = $_POST['spTopic']; 
	  $spConflict = $_POST['spConflict']; 
	  $spMissing = $_POST['spMissing']; 
	  $spReviewer = $_POST['spReviewer']; 
	  $spPQuestions = encodeQuestions ("paperQuestions"); 
	  $spRQuestions = encodeQuestions ("reviewQuestions"); 
 
	  $updConfig = "UPDATE Config SET papersWithStatus='$spStatus', " 
	    . "papersWithFilter='$spFilter',papersWithRate='$spRate', " 
	    . "papersWithReviewer='$spReviewer', " 
	    . "papersWithTopic='$spTopic', " 
	    . "papersWithConflict='$spConflict', " 
	    . "papersWithMissingReview='$spMissing', " 
	    . "papersWithTitle='$spTitle', " 
	    . "papersWithAuthor='$spAuthor', " 
	    . "papersUploaded='$spUploaded'," 
	    . "papersQuestions='$spPQuestions', " 
	    . "reviewsQuestions='$spRQuestions'"; 
	  $db->execRequete ($updConfig); 
	  // Compute the current selection 
	  GetCurrentSelection ($db); 
	} 
      
      // No action? Show the list of possible actions 
      if (!isSet($_GET['action'])) 
	{ 
	  /* Give the id of actions  */ 
	  $tpl->set_var("CONFIGURE", CONFIGURE); 
	  $tpl->set_var("TOPICS", TOPICS); 
	  $tpl->set_var("CRITERIAS", CRITERIAS); 
	  $tpl->set_var("REVIEW_QUESTIONS", REVIEW_QUESTIONS); 
	  $tpl->set_var("PAPER_QUESTIONS", PAPER_QUESTIONS); 
	  $tpl->set_var("REGISTRATION_QUESTIONS", REGISTRATION_QUESTIONS); 
	  $tpl->set_var("REGISTRATION_LIST", REGISTRATION_LIST); 
	  $tpl->set_var("PAYMENT_MODES", PAYMENT_MODES); 
	  $tpl->set_var("PAPER_STATUS_CODES", PAPER_STATUS_CODES); 
	  $tpl->set_var("LIST_PC_MEMBERS", LIST_PC_MEMBERS); 
	  $tpl->set_var("STATUS_OF_PAPERS", STATUS_OF_PAPERS); 
	  $tpl->set_var("LIST_PAPERS", LIST_PAPERS); 
	  $tpl->set_var("LIST_AUTHORS", LIST_AUTHORS); 
	  $tpl->set_var("CREATE_VOTE", CREATE_VOTE); 
	  $tpl->set_var("COMPUTE_PREDICTION", COMPUTE_PREDICTION); 
	  $tpl->set_var("COMPUTE_ASSIGNMENT", COMPUTE_ASSIGNMENT); 
	  $tpl->set_var("SUMMARY_ASSIGNMENT", SUMMARY_ASSIGNMENT); 
	  $tpl->set_var("CLOSE_SUBMISSION", CLOSE_SUBMISSION); 
	  $tpl->set_var("CLOSE_SELECTION", CLOSE_SELECTION); 
	  $tpl->set_var("QUERY", QUERY); 
	   
	  $tpl->set_var("PWD_REVIEWERS", PWD_REVIEWER); 
	  $tpl->set_var("STATUS_TO_AUTHORS", STATUS_TO_AUTHORS); 
	  $tpl->set_var("FREE_MAIL", FREE_MAIL); 
	  $tpl->set_var("MAIL_SELECT_TOPICS", MAIL_SELECT_TOPICS); 
	  $tpl->set_var("MAIL_RATE_PAPERS", MAIL_RATE_PAPERS); 
	  $tpl->set_var("MAIL_PARTICIPATE_FORUM", MAIL_PARTICIPATE_FORUM); 
	  $tpl->set_var("CONF_SESSIONS", CONF_SESSIONS); 
	  $tpl->set_var("CONF_SLOTS", CONF_SLOTS); 
	  $tpl->set_var("LATEX_OUTPUT", LATEX_OUTPUT); 
	  $tpl->set_var("ASSIGN_CR_PAPERS", ASSIGN_CR_PAPERS); 
	  $tpl->set_var("LIST_ACCEPTED_PAPERS", LIST_ACCEPTED_PAPERS); 
	  $tpl->set_var("CONF_PROGRAM", CONF_PROGRAM); 
	  $tpl->set_var("PDF_CONFIG_PARAMS", PDF_CONFIG_PARAMS); 
	 
	if (graphsTestConfig()) $tpl->set_var("GRAPHS", "<a href=\"stats.php\">Statistics</a>"); 
	else $tpl->set_var("GRAPHS", ""); 
 
        // One entry for each status 
	if (strstr($session->roles, "C")) 
	  { 
	    $tpl->set_block("CHAIR_CHOICES", "PAPER_CLASSIFICATION",  
                            "PAPERS_CLASSIFICATION"); 
	    $tpl->set_var("PAPERS_CLASSIFICATION", ""); 
	    $statusList = GetListStatus ($db); 
	    foreach ($statusList as $id => $sVals) 
	      { 
		$tpl->set_var("PAPER_STATUS", $id); 
		$tpl->set_var("STATUS_LABEL", $sVals['label']); 
		$tpl->parse("PAPERS_CLASSIFICATION", "PAPER_CLASSIFICATION", true); 
	      }  
	  } 
	$tpl->parse("BODY", "TxtInfoAdmin"); 
      } 
    else 
      switch ($_GET['action']) 
	{ 
 
	case LIST_PC_MEMBERS: 
	  { 
	    // Modify request use the 'GET' method. 
	    if (isSet($_GET['email'])) $_POST['email'] =  $_GET['email']; 
	    if (isSet($_GET['instr'])) $_POST['instr'] =  $_GET['instr']; 
	    AdmListMembers ($_POST, $tpl, $db, $TEXTS); break; 
	  } 
 
	case QUERY: 
	  { 
	    $sql_error = FALSE; 
	    $tpl->set_block("SQL", "RESULT", "SQL_RESULT"); 
 
	    if (isSet($_POST['sqlQuery'])) 
	    { 
	      // Always display the submitted query 
	      $query = $_POST['sqlQuery']; 
	      $tpl->set_var("SQL_QUERY", $query); 
	      // Execute the query, put the result in the template 
	      ExecQuery ($query, $tpl); 
	    } 
	    else 
	      { 
		// Try to connect to the DB with restricted rights 
		$sql_error = FALSE; 
		$connexion = @mysql_pconnect (SERVER, SQLUser, pwdSQL); 
		if (!$connexion)  
		  $sql_error = TRUE; 
		else 
		  // Connnect to the DB 
		  if (!@mysql_select_db (BASE, $connexion))  
		    $sql_error = TRUE; 
		if ($sql_error) 
		    $tpl->set_var("BODY",  
				  $TEXTS->get("TXT_INVALID_SQL_USER")); 
		else 
		  { 
		    $tpl->set_var("SQL_RESULT", ""); 
		    $tpl->set_var("SQL_QUERY",  
				  "SELECT firstName, lastName FROM PCMember"); 
		  } 
	      } 
	    // Show the query and the result 
	    if (!$sql_error) $tpl->parse("BODY", "SQL"); 
	    break; 
	  } 
 
	case TOPICS: 
	  $ihm = new IhmBD ("ResearchTopic", $db, $context); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
	case CRITERIAS: 
	  $ihm = new IhmBD ("Criteria", $db, $context); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break;
	   
	case REVIEW_QUESTIONS: 
	  $ihm = new IhmBD ("ReviewQuestion", $db, $context); 
	  $ihm->setSlaveTable ("RQChoice", array("id_question" => "id")); 
	  $ihm->setAutoIncrementedKey ("id_choice"); 
	  $ihm->setTitle ($TEXTS->get("FRM_RQ_TITLE")); 
	  $ihm->setEntete ("question", $TEXTS->get("FRM_RQ_QUESTION")); 
	  $ihm->setEntete ("choice", $TEXTS->get("FRM_RQ_CHOICE_NAME")); 
	  $ihm->setFormField ("public", BOOLEAN_FIELD, array()); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
	case PAPER_QUESTIONS: 
	  $ihm = new IhmBD ("PaperQuestion", $db, $context); 
	  $ihm->setSlaveTable ("PQChoice", array("id_question" => "id"), 10); 
	  $ihm->setAutoIncrementedKey ("id_choice"); 
	  $ihm->setTitle ($TEXTS->get("FRM_PQ_TITLE")); 
	  $ihm->setEntete ("question", $TEXTS->get("FRM_RQ_QUESTION")); 
	  $ihm->setEntete ("choice", $TEXTS->get("FRM_RQ_CHOICE_NAME")); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
	case PAYMENT_MODES: 
	  $ihm = new IhmBD ("PaymentMode", $db, $context); 
	  $ihm->setAutoIncrementedKey ("id"); 
	  $ihm->setTitle ($TEXTS->get("FRM_PAYMENT_FORMTITLE")); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
	case REGISTRATION_QUESTIONS: 
	  $ihm = new IhmBD ("RegQuestion", $db, $context); 
	  $ihm->setSlaveTable ("RegChoice", array("id_question" => "id") 
			       ); 
	  $ihm->setAutoIncrementedKey ("id_choice"); 
	  $ihm->setTitle ($TEXTS->get("FRM_REGQ_TITLE")); 
	  $ihm->setEntete ("question", $TEXTS->get("FRM_RQ_QUESTION")); 
	  $ihm->setEntete ("choice", $TEXTS->get("FRM_RQ_CHOICE_NAME")); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
	case PAPER_STATUS_CODES: 
            GenericTblAccess ($_POST, "PaperStatus",$context,$db,$tpl); break; 
 
 	case CONF_SESSIONS: 
	  $ihm = new IhmBD ("ConfSession", $db, $context); 
	  $ihm->setEntete ("id_slot", "Slot"); 
	  $ihm->addHiddenField ("action", CONF_SESSIONS); 
	  $ihm->setFormField("id_slot", SELECT_FIELD,  
			     array("tb_name" => "Slot", "id_name" => "id", 
				   "name" =>  
				   "CONCAT(slot_date, ' (', begin, '-', end,')')")); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
 	case CONF_SLOTS: 
	  $ihm = new IhmBD ("Slot", $db, $context); 
	  $ihm->addHiddenField ("action", CONF_SLOTS); 
	  $ihm->setEntete ("slot_date", "Date (yyyy-mm-dd)"); 
	  $ihm->setEntete ("begin", "Begins at (hh:mm)"); 
	  $ihm->setEntete ("end", "Ends at (hh:mm)"); 
	  $ihm->setOrderBy ("slot_date, begin"); 
	  $tpl->set_var("BODY", $ihm->genererIHM($_REQUEST)); 
	  break; 
 
 	case LATEX_OUTPUT: 
	  $messages = latex_docs ($db); 
	  $tpl->set_var("BODY", $messages); 
	  break; 
	   
	case LIST_PAPERS:  
	  { 
	    /* Removal of a reviewer from a paper ? */ 
	    if (isSet($_GET['remove'])) 
	      DeleteReview ($_GET['idPaper'], $_GET['remove'], $db); 
	    AdmListPapers($tpl, $db, $TEXTS, $CODES); break; 
	  } 
 
	case REGISTRATION_LIST:  
	  { 
	    AdmListRegistrations($tpl, $db, $TEXTS, $CODES); break; 
	  } 
 
	case LIST_AUTHORS:  
	  { 
	    AdmListAuthors("Admin.php?action=" . LIST_AUTHORS, 
                               $tpl, $db, $TEXTS); break; 
	  } 
 
	case CLOSE_SUBMISSION:  
	  { 
	    // Close the submission 
	    $qUpdateConfig = "UPDATE Config SET isSubmissionOpen='N'"; 
 
	    $db->execRequete($qUpdateConfig); 
 
	    // Delete all reviews for papers not uploaded 
	    $qReviews = "SELECT idPaper,email FROM Paper p, Review r " 
                      . " WHERE p.id=r.idPaper AND isUploaded='N'"; 
	    $rReviews = $db->execRequete($qReviews); 
	    while ($review = $db->objetSuivant($rReviews)) 
	      DeleteReview ($review->idPaper, $review->email, $db); 
 
	    $tpl->parse("BODY", "TxtInfoSubmissionClosed"); 
	    break; 
	  } 
 
 
	case CLOSE_SELECTION:  
	  { 
	    $qAllPapersUploaded="select * from Paper where isUploaded='N'"; 
	    $rAllPapersUploaded=$db->execRequete($qAllPapersUploaded); 
	    if ($db->objetSuivant($rAllPapersUploaded)) { 
	      SetConfig ($db, "N", "A", SP_ANY_STATUS);	       
	      $tpl->set_var("ERROR","There are <b>not uploaded papers</b>"); 
	      $tpl->set_var("LINK","Admin.php?action=".LIST_PAPERS); 
	      $tpl->parse("BODY", "TxtInfoSelectionNotClosed"); 
	      break; 
	    } 
	    
	    $qAllReviewsSubmitted="select * from Review where overall is null"; 
	    $rAllReviewsSubmitted=$db->execRequete($qAllReviewsSubmitted); 
	    if ($db->objetSuivant($rAllReviewsSubmitted)) { 
	      SetConfig ($db, "A", "Y", SP_ANY_STATUS);	       
	      $tpl->set_var("ERROR","There exists <b>not yet submitted reviews</b>"); 
	      $tpl->set_var("LINK","Admin.php?action=".STATUS_OF_PAPERS); 
	      $tpl->parse("BODY", "TxtInfoSelectionNotClosed"); 
	      break; 
	    } 
	     
	    $qAllPapersDecided="select * from Paper where status is null"; 
	    $rAllPapersDecided=$db->execRequete($qAllPapersDecided); 
	    if ($db->objetSuivant($rAllPapersDecided)) { 
	      SetConfig ($db, "A", "A", SP_NOT_YET_ASSIGNED);		      
	      $tpl->set_var("ERROR","There exists <b>papers without status</b>"); 
	      $tpl->set_var("LINK","Admin.php?action=".STATUS_OF_PAPERS); 
	      $tpl->parse("BODY", "TxtInfoSelectionNotClosed"); 
	      break; 
	    } 
	     
	    $qMailTemplates="select mailTemplate from PaperStatus"; 
	    $rMailTemplates=$db->execRequete($qMailTemplates); 
	    $errors=""; 
	    while ($mailTemplate=$db->objetSuivant($rMailTemplates)) { 
	      if (!is_file(TPLDIR.$mailTemplate->mailTemplate)) { 
		$errors.="Template ".$mailTemplate->mailTemplate." must exist !<BR>"; 
	      } 
	      elseif (!is_readable(TPLDIR.$mailTemplate->mailTemplate)) { 
		$errors.="Template ".$mailTemplate->mailTemplate." must be readeable !<BR>"; 
	      } 
	    } 
	    if ($errors<>"") { 
	      $tpl->set_var("ERROR",$errors); 
	      $tpl->set_var("LINK","Admin.php?action=".PAPER_STATUS_CODES); 
	      $tpl->parse("BODY", "TxtInfoSelectionNotClosed"); 
	      break; 
	    } 
	     
	    $qUpdateConfig="UPDATE Config SET isSubmissionOpen='Y', ". 
	      "discussion_mode=" . NO_DISCUSSION . ", isCameraReadyOpen='Y'"; 
	    $db->execRequete($qUpdateConfig); 
	    $tpl->parse("BODY", "TxtInfoSelectionClosed"); 
	    break; 
	  } 
 
	  /* Select a batch of papers to be rated by PCM */ 
	case CREATE_VOTE:  
	  { 
	    SelectPapersForRating ($db); 
	    $tpl->parse("BODY", "TxtPostBallot"); 
	    break; 
	  } 
 
	  /* Compute/predict new preferences */ 
	case COMPUTE_PREDICTION:  
	  { 
	    ComputePrediction ($db); 
	    $tpl->parse("BODY", "TxtInfoPredictionCreated"); 
	    break; 
	  } 
 
	case STATUS_OF_PAPERS: 
	  { 
	    $tpl->set_file("StatusOfPapers", TPLDIR . "StatusOfPapers.tpl"); 
	    $tpl->set_var("PDF_SELECT_PAPERS_WITH_REVIEWS",  
			  PDF_SELECT_PAPERS_WITH_REVIEWS); 
	    if (isSet($_REQUEST['remove'])) 
             { 
	      DeleteReview ($_REQUEST['idPaper'], $_REQUEST['remove'], $db); 
             } 
	    else if (isSet($_REQUEST['idPaper'])) 
	      { 
   	        // If the status is submitted: update in the DB 
		$idPaper = $_REQUEST['idPaper']; 
		$status = $_REQUEST['status']; 
		while (list($key, $val) = each($idPaper)) 
		  { 
		    if (isSet($status[$val])) 
		      { 
			$query = "UPDATE Paper SET status='"  
			   . $status[$val] . "' WHERE id='$val'"; 
			$db->execRequete ($query); 
		      } 
		  } 
	      } 
	    // Always list the papers 
	    PapersReviews ($tpl, $db, $TEXTS, $CODES);  
	    break; 
	  } 
 
	case LIST_ACCEPTED_PAPERS: case ASSIGN_CR_PAPERS:  
	  { 
	    if (isSet($_GET['simple']))  
	      $simple=$_GET['simple'];  
	    else $simple=TRUE; 
 
	    if ($_REQUEST['action'] == ASSIGN_CR_PAPERS) 
	      { 
		// Take all the papers with camera-ready file required 
		$status = CAMERA_READY_STATUS; 
		$simple = FALSE; 
	      } 
	    else 
	      $status = $_REQUEST['status']; 
	    AdmListAcceptedPapers($status,$tpl,$db,$simple,$TEXTS);  
	    break; 
	  } 
	   
	case  CONF_PROGRAM: 
	  { 
	    ConferenceProgram ($tpl, $db);  break; 
	  } 
 
	case PDF_CONFIG_PARAMS : 
	  {  
	    UpdatePapersPDF($_POST); 
	    $target= "Admin.php?action=" . PDF_CONFIG_PARAMS; 
	    $tpl->set_var ("TITLE","Formatted PDF"); 
	    $tpl->set_var ("BODY",FormAllPaper($target));            	   
	    break; 
          } 
	 
	case  PDF_SELECT_PAPERS_WITH_REVIEWS : 
	  { 
	    printPapersList(true, session_id());  break; 
	  } 
          
	case PDF_SELECT_PAPERS_WITHOUT_REVIEWS : 
	  { 
	    printPapersList(false, session_id());  break; 
	  } 
 
	case COMPUTE_ASSIGNMENT:  
	  { 
	    if (isSet($_POST['commitAssignment'])) 
	      { 
		if (isSet($_POST['idMin'])) 
		  { 
		    // Commit for a group 
		    $idMin = $_POST['idMin']; 
		    $idMax = $_POST['idMax']; 
		  } 
		else {$idMin = $idMax = -1;} 
		CommitAssignment($idMin, $idMax, $db); 
		$tpl->set_var("BODY", "Assignment committed!"); 
		// SummaryPapersAssignment (&$tpl, $db); 
	      } 
	    else 
	      { 
		// Compute and propose the assignment 
		if (isSet($_GET['nbRev'])) 
		  $maxReviewers = $_GET['nbRev']; 
		else 
		  $maxReviewers = $config['nbReviewersPerItem']; 
 
		ComputeAssignment ($tpl, $db, $maxReviewers); 
	      } 
	    break; 
	  } 
 
	case SUMMARY_ASSIGNMENT:  
	  { 
	    // Set the selected paper topic if necessary 
	    if (isSet($_POST['paperTopic'])) 
	      { 
		$topic = $_POST['paperTopic']; 
		$qConfig = "UPDATE Config SET selectedPaperTopic='$topic'"; 
		$db->execRequete($qConfig); 
	      } 
 
	    // Set the selected reviewer topic if necessary 
	    if (isSet($_POST['reviewerTopic'])) 
	      { 
		$topic = $_POST['reviewerTopic']; 
		$qConfig = "UPDATE Config SET selectedReviewerTopic='$topic'"; 
		$db->execRequete($qConfig); 
	      } 
 
            // Update the assignment if necessary 
	    if (isSet($_POST['changeAssignment'])) 
	      { 
		$assignments = $_POST['assignments']; 
		if (is_array($assignments)) 
		  { 
		    while (list($idPaper, $array2) = each($assignments)) 
		      { 
			$tabMails = array(); 
			while (list($email, $val) = each($array2)) 
			  { 
			    if ($val == 1) 
			      $tabMails[] = $email; 
			    else 
			      DeleteReview ($idPaper, $email, $db); 
			  } 
			if (count($tabMails) > 0) 
			  SQLReview ($idPaper, $tabMails, $db); 
		      } 
		  } 
	      } 
 
	    SummaryPapersAssignment ($tpl, $db, $TEXTS); 
	    break; 
	  } 
 
	case CONFIGURE: 
	  { 
	    $form_mess = ""; 
	    $tpl->set_var("TITLE", $TEXTS->get("TTL_CONFIGURE")); 
	    $tpl->set_file ("form_config", TPLDIR . "FormConfig.tpl"); 
 
 
	    // When the form is submitted 
	    if (isSet ($_POST['confName'])) { 
	      $form_mess = UpdateConfig ($_POST, $db, $tpl, $session);
	    }

	    $config = GetConfig($db); 
	    $tpl->set_var ("CONF_ID_ADMIN", $session->email . " / " .  
			   PWDMember($session->email, 
				     $config['passwordGenerator'])); 
 
	    InstanciateConfigVars ($config, $tpl); 
	    $tpl->set_var ("MESSAGES", $form_mess); 
	    $tpl->parse ("BODY", "form_config"); 
	    break; 
	  } 
	} 
    } 
else 
  $tpl->set_var("BODY", $TEXTS->get("TXT_ADMIN_ONLY")); 
 
} 
// In any case, print the page 
$tpl->pparse("RESULT", "Page"); 
 
?>
