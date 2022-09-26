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
 
require_once ("template.inc"); 
require_once ("Util.php"); 
require_once ("Formulaire.class.php"); 
 
// List all the papers with various actions 
function AdmListPapers (&$tpl, $db, &$TEXTS, &$CODES) 
{ 
  // If required, hide the selection form
  if (isSet($_REQUEST['hide_selection_form'])) {
    $db->execRequete ("UPDATE Config SET show_selection_form='N'");
  }
  else if (isSet($_REQUEST['show_selection_form'])) {
    $db->execRequete ("UPDATE Config SET show_selection_form='Y'");
  }

  $tpl->set_var("TITLE", $TEXTS->get("TTL_SUBMITTED_PAPERS"));  
  $tpl->set_var("PDF_SELECT_PAPERS_WITHOUT_REVIEWS",  
		PDF_SELECT_PAPERS_WITHOUT_REVIEWS); 

  /* Select all the papers and list them. 
     First extract the 'block' describing a line from the template */ 
 
  $tpl->set_block("TxtListPapers", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block("TxtListPapers", "SELECTION_FORM"); 
  $tpl->set_block("TxtListPapers", "SHOW_SELECTION_FORM"); 
  $tpl->set_block("PAPER_DETAIL", "REVIEWER", "REVIEWERS"); 
  $tpl->set_var("PAPERS",""); 
 
  $config = GetConfig($db); 

   // Show the form for filtering papers, if required 
  if ($config['show_selection_form'] == 'Y') {
    $tpl->set_var("FORM_SELECT_PAPERS",  
		  FormSelectPapers (LIST_PAPERS, $tpl, $db,  
				    $TEXTS, $CODES)); 
    $tpl->set_var ("SHOW_SELECTION_FORM", "");
  }
  else {
    $tpl->set_var ("SELECTION_FORM", "");
  }

 $nbPapers = 0; 
 
  // Check whether the paper must be removed 
  if (isSet($_REQUEST['instr'])) 
    if ($_REQUEST['instr'] == "remove" and isSet($_REQUEST['idPaper'])) 
    { 
      $idPaper = $_REQUEST['idPaper']; 
 
      // Delete the reviews, then the paper 
      $qDelReviews = "SELECT email FROM Review WHERE idPaper = '$idPaper'"; 
      $rRev = $db->execRequete ($qDelReviews); 
      while ($rev = $db->objetSuivant($rRev)) 
        DeleteReview ($idPaper, $rev->email, $db); 
 
      // Delete the file 
      $paper = GetPaper($idPaper, $db, "object"); 
      $file_name = FNamePaper ($config['uploadDir'], $idPaper, $paper->format); 
      @unlink ($file_name); 
 
      $qDel = "DELETE FROM Paper WHERE id=$idPaper"; 
      $db->execRequete($qDel); 
 
      $qDelRating = "DELETE FROM Rating WHERE idPaper=$idPaper"; 
      $db->execRequete($qDelRating); 
 
      $qDelRatingBox = "DELETE FROM RatingBox WHERE idPaper=$idPaper"; 
      $db->execRequete($qDelRatingBox); 
 
      $db->execRequete("DELETE FROM Author WHERE id_paper=$idPaper"); 
    } 
 
  $query = "SELECT * FROM Paper WHERE inCurrentSelection='Y' ORDER BY id"; 
  $result = $db->execRequete ($query); 
  $i = 0; 
  while ($paper = $db->objetSuivant($result)) 
    { 
      $tpl->set_var("REVIEWERS",""); 
      $nbPapers++; 
      InstanciatePaperVars ($paper, $tpl, $db); 
      // Choose the CSS class 
      if ($i++ %2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      $tpl->set_var("SESSION_ID", session_id()); 
      $tpl->set_var("CONF_URL", $config['confURL']); 
       
      /* Show the list of reviewers */ 
      $query = "SELECT p.* FROM Review r, PCMember p " 
     . " WHERE r.idPaper = '$paper->id' AND p.email=r.email "; 
      $resRev = $db->execRequete ($query); 
      while ($rev =  $db->objetSuivant ($resRev)) 
      { 
        InstanciateMemberVars ($rev, $tpl, $db); 
        $tpl->parse ("REVIEWERS", "REVIEWER", true); 
       } 
 
      /* Check if the file has been downloaded   */ 
      if ($paper->isUploaded == 'Y') 
	$tpl->set_var("DOWNLOAD",  
          Ancre (FNamePaper($config['uploadDir'], $paper->id, $paper->format),  
		 $TEXTS->get("TXT_DOWNLOAD"))); 
      else 
	$tpl->set_var("DOWNLOAD", $TEXTS->get("TXT_NOT_DOWNLOADED")); 
	   
      /* Instanciate the entities in PAPER_DETAIL. Put in PAPERS   */ 
      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
    } 
 
 
  /* Instanciate PAPERS in TxtListOfPapers. Put the result in BODY */ 
  $tpl->parse("BODY", "TxtListPapers"); 
} 
 
// List all the accepted papers 
function AdmListAcceptedPapers ($statusID, &$tpl, $db, $simple, &$TEXTS) 
{ 
  $config = GetConfig($db); 
   
  // Check whether papers are assigned to sessions 
  if (isSet($_REQUEST['form_assign_session']))  { 
    foreach ($_REQUEST['conf_session'] as $id_paper => $id_session) { 
      if (!empty($id_session)) { 
	$pos_in_session = trim($_REQUEST['position_in_session'][$id_paper]); 
	if (!empty($pos_in_session)) 
	  $db->execRequete ("UPDATE Paper SET id_conf_session='$id_session', " 
			    . "position_in_session='$pos_in_session' " 
			    . "WHERE id='$id_paper'"); 
	else  
	  $db->execRequete ("UPDATE Paper SET id_conf_session='$id_session' " 
			    . "WHERE id='$id_paper'"); 
      } 
    } 
  } 
   
  /*  First extract the 'blocks' describing a line from the template */ 
  if ($simple==1)  
    { 
      $tpl->set_var("LINK", "<a href=\"Admin.php?action=" 
		    . LIST_ACCEPTED_PAPERS  
		    . "&status=$statusID&simple=2\">More infos</a>"); 
      $template = "TxtAcceptedPapersSimple"; 
    } 
  else  
    {  
      $template = "TxtAcceptedPapers"; 
      $tpl->set_var("LINK", "<a href=\"Admin.php?action=" 
		    . LIST_ACCEPTED_PAPERS 
		   ."&status=$statusID&simple=1\">Simple list</a>" ); 
    } 
   
  $tpl->set_block($template, "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block($template, "OPEN_FORM_ASSIGN"); 
  $tpl->set_block($template, "CLOSE_FORM_ASSIGN"); 
  $tpl->set_var("PAPERS", ""); 
 
  if ($statusID == CAMERA_READY_STATUS) 
    { 
    $tpl->set_block("PAPER_DETAIL", "SELECT_ASSIGN"); 
    $tpl->set_var("TITLE", $TEXTS->get("TTL_ASSIGN_CR_PAPERS")); 
    $tpl->set_var("STATUS_LABEL", "accepted"); 
    $query = "SELECT p.id, IFNULL(id_conf_session,0) id_conf_session, " 
      . " IFNULL(position_in_session,999) position_in_session " 
      . " FROM Paper as p,  PaperStatus s WHERE p.status=s.id " 
      . " AND cameraReadyRequired ='Y' " 
      . "ORDER BY id_conf_session DESC, position_in_session ASC"; 
    $tpl->set_var("LINK", ""); 		  
    $status['cameraReadyRequired'] = 'Y'; 
    } 
  else 
    { 
      $tpl->set_var("TITLE", $TEXTS->get("TTL_ACCEPTED_PAPERS")); 
      // Get the status label 
      $status = GetRow ("SELECT * FROM PaperStatus WHERE id='$statusID'", $db); 
      $tpl->set_var("STATUS_LABEL", $status['label']); 
      $tpl->set_var("OPEN_FORM_ASSIGN", "");   
      $tpl->set_var("CLOSE_FORM_ASSIGN", "");   
      $query = "SELECT * FROM Paper WHERE status='$statusID' ORDER BY id"; 
    } 
 
   
  $conf_sessions[""] = $TEXTS->get("TXT_NOT_ASSIGNED"); 
  $conf_sessions = GetCodeList ("ConfSession", $db, "id", "name", 
				$conf_sessions); 
 
  // OK. Now execute the query, fetch the papers, display 
  $result = $db->execRequete ($query); 
  $i= 0; 
  while ($paper = $db->objetSuivant($result)) 
    { 
      $paper = GetPaper ($paper->id, $db, "object"); 
      // Choose the CSS class 
      if ($i++ %2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      // Instanciate the entities in PAPER_DETAIL. Put the result in PAPERS   
      InstanciatePaperVars ($paper, $tpl, $db); 
      $tpl->set_var("SESSION_ID", session_id()); 
       
      if ($config['isCameraReadyOpen']=='N')  
     	$tpl->set_var("CR_PAPER", $TEXTS->get("TXT_CR_NOT_OPEN")); 
      elseif ($status['cameraReadyRequired']=='N')  
	$tpl->set_var("CR_PAPER", $TEXTS->get("TXT_CR_NOT_REQUIRED")); 
      elseif ($paper->CR > 0)  
	$tpl->set_var("CR_PAPER", $TEXTS->get("TXT_CR_UPLOADED")); 
      else $tpl->set_var("CR_PAPER", $TEXTS->get("TXT_CR_NOT_UPLOADED")); 
       
      // Check if the file has been uploaded   
      if ($paper->CR > 0){ 
	$tpl->set_var("DOWNLOAD",  
		      Ancre ( 
			     FNamePaper($config['uploadDir']. 
					"/CR".$paper->status,  
					$paper->id, $paper->format),  
			     $TEXTS->get("TXT_DOWNLOAD"))); 
      } 
      else { 
	$tpl->set_var("DOWNLOAD", $TEXTS->get("TXT_CR_NOT_UPLOADED"));	 
      } 
      $tpl->set_var ("SESSION_LIST",  
		     SelectField ("conf_session[$paper->id]", $conf_sessions,  
				  $paper->id_conf_session)); 
      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
    }  
   
  /* Instanciate PAPERS in TxtListOfPapers. Put the result in BODY */ 
  $tpl->parse("BODY", $template); 
} 
 
// Program of the conference 
function ConferenceProgram (&$tpl, $db) 
{ 
  $config = GetConfig($db); 
   
  // Load the program template file 
  $tpl->set_file ("conf_program", TPLDIR . "ConfProgram.tpl"); 
  $tpl->set_block ("conf_program", "SESSION_DETAIL", "SESSIONS"); 
  $tpl->set_block ("SESSION_DETAIL", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block ("SESSION_DETAIL", "CHAIR", "SHOW_CHAIR"); 
  $tpl->set_var("SESSIONS", ""); 
   
  $q_sessions = "SELECT c.id, name, chair, comment as sess_comment, " 
    . " end as slot_end, begin as slot_begin " 
    . " FROM ConfSession c, Slot s " 
    . "WHERE s.id=c.id_slot ORDER BY slot_date, begin, end, c.id"; 
  $sess = $db->execRequete ($q_sessions); 
  while ($session = $db->objetSuivant ($sess)) 
    { 
      $tpl->set_var("PAPERS", ""); 
      $tpl->set_var("CONF_SESSION_NAME", $session->name); 
      $tpl->set_var("CONF_SLOT_NAME",  
		    $session->slot_begin . "-" . $session->slot_end); 
      $tpl->set_var("CONF_SESSION_CHAIR", $session->chair); 
      if (empty ($session->chair)) 
	  $tpl->set_var("SHOW_CHAIR", ""); 
      else 
	  $tpl->parse("SHOW_CHAIR", "CHAIR"); 
 
      $tpl->set_var("CONF_SESSION_COMMENT", $session->sess_comment); 
 
      $q_papers = "SELECT * FROM Paper " 
	. "WHERE id_conf_session=$session->id ORDER BY position_in_session"; 
      $rp = $db->execRequete ($q_papers); 
      while ($paper = $db->objetSuivant ($rp)) 
	{ 
	  // Instanciate the entities in PAPER_DETAIL.  
	  InstanciatePaperVars ($paper, $tpl, $db); 
	  $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
	}  
      $tpl->parse("SESSIONS", "SESSION_DETAIL", true); 
    } 
  $tpl->parse("BODY", "conf_program"); 
} 
 
// List all the authors with their papers 
function AdmListAuthors ($target, &$tpl, $db, &$TEXTS) 
{ 
  $tpl->set_var("TITLE", $TEXTS->get("TTL_ALL_AUTHORS"));  
  // Load the template 
  $tpl->set_file ("TxtListAuthors", TPLDIR . "TxtListAuthors.tpl"); 
 
  /* Select all the papers and list them. 
     First extract the 'block' describing a line from the template */ 
 
  $tpl->set_block("TxtListAuthors", "AUTHOR_DETAIL", "AUTHORS"); 
  $tpl->set_block("AUTHOR_DETAIL", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block("TxtListAuthors", "GROUPS_LINKS", "LINKS"); 
  $tpl->set_var("AUTHORS",""); 
  $tpl->set_var("LINKS", ""); 
 
  $config = GetConfig($db); 
  $nbAuthors = 0; 
 
  // Initialize the current interval 
  if (!isSet($_REQUEST['iMin'])) 
    { 
      $iMinCur = 1; $iMaxCur = SIZE_AUTHORS_GROUP; 
    } 
  else 
    { 
      $iMinCur = $_REQUEST['iMin'];  $iMaxCur = $_REQUEST['iMax']; 
    } 
 
  $query = "SELECT last_name, first_name, affiliation FROM Author " 
    ." GROUP BY last_name, first_name " 
    .        " ORDER BY last_name, first_name"; 
  $result = $db->execRequete ($query); 
  $i = 0; 
  while ($author = $db->objetSuivant($result)) 
    { 
      $i++; 
      if ($i >= $iMinCur and $i <= $iMaxCur) 
	{ 
	  // Choose the CSS class 
	  if ($i %2 == 0) 
	    $tpl->set_var("CSS_CLASS", "even"); 
	  else 
	    $tpl->set_var("CSS_CLASS", "odd"); 
	   
	  $tpl->set_var("SESSION_ID", session_id()); 
	  $tpl->set_var("AUTHOR_LAST_NAME", $author->last_name); 
	  $tpl->set_var("AUTHOR_FIRST_NAME", $author->first_name); 
	  $tpl->set_var("AUTHOR_AFFILIATION", $author->affiliation); 
	   
	  /* Get the papers for this author */ 
	  $tpl->set_var("PAPERS", ""); 
	  $safe_fname = $db->prepareString ($author->first_name); 
	  $safe_lname = $db->prepareString ($author->last_name); 
	  $resP =  
	    $db->execRequete("SELECT * FROM Paper p, Author a " 
			     . "WHERE a.last_name='$safe_lname' " 
			     . "AND a.first_name='$safe_fname' " 
			     . "AND a.id_paper = p.id "); 
	  while ($paper = $db->objetSuivant($resP)) 
	    { 
	      InstanciatePaperVars ($paper, $tpl, $db); 
	      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
	    } 
	  /* Instanciate the entities in AUTHOR_DETAIL. Put in AUTHORS   */ 
	  $tpl->parse("AUTHORS", "AUTHOR_DETAIL", true); 
	} 
    } 
 
  // Create the groups 
  $nbAuthors = $i; 
  $nb_groups = $nbAuthors / SIZE_AUTHORS_GROUP + 1; 
  for ($i=1; $i <= $nb_groups; $i++) 
    { 
      $iMin = (($i-1) *  SIZE_RATING) + 1; 
      if ($iMin >= $iMinCur and $iMin <= $iMaxCur) 
	$link = "<font color=red>$i</font>"; 
      else 
	$link =$i; 
      $tpl->set_var("LINK", $link); 
       
      $tpl->set_var("IMIN_VALUE", $iMin); 
      $tpl->set_var("IMAX_VALUE", $iMin + SIZE_RATING -1); 
      $tpl->parse("LINKS", "GROUPS_LINKS", true); 
    } 
 
  /* Instanciate PAPERS in TxtListOfPapers. Put the result in BODY */ 
  $tpl->parse("BODY", "TxtListAuthors"); 
} 
 
// List all the PC members with various actions 
function AdmListMembers ($_POST, &$tpl, $db, &$TEXTS) 
{ 
  $tpl->set_var("TITLE", $TEXTS->get("TTL_PROGRAM_COMMITTEE")); 
 
  // After submission, insert 
  if (isSet($_POST['email'])) 
    { 
      $email = $_POST['email']; 
      if (!isSet($_POST['formPCMember'])) 
	{ 
	  if ($_POST['instr'] == "modify") 
	    { 
	      // Just show the form with default values 
	      $tpl->set_var("MESSAGE", "Modify PC Member infos"); 
	      $member = GetMember ($email, $db);      
	      $tpl->set_var("FORM_MEMBER",  
			FormPCMember ($member, MAJ, "Admin.php?action=1"));  
	    } 
	  else 	  if ($_POST['instr'] == "remove") 
	    { 
	      // Delete the reviewer and her reviews 
	      $qDel = "DELETE FROM PCMember WHERE email = '$email'"; 
	      $db->execRequete($qDel); 
	      $qDelReviews = "DELETE FROM Review WHERE email = '$email'"; 
	      $db->execRequete($qDelReviews); 
	      $qDelTopics = "DELETE FROM SelectedTopic WHERE email = '$email'"; 
	      $db->execRequete($qDelTopics); 
	      $qDelReviewMark =  
                    "DELETE FROM ReviewMark WHERE email = '$email'"; 
	      $db->execRequete($qDelReviewMark); 
	      $qDelRatingBox = "DELETE FROM RatingBox WHERE email = '$email'"; 
	      $db->execRequete($qDelRatingBox); 
	      $qDelRating = "DELETE FROM Rating WHERE email = '$email'"; 
	      $db->execRequete($qDelRating); 
	      $tpl->set_var("FORM_MEMBER",  
			FormPCMember (array(), INSERTION,  
				      "Admin.php?action=1"));  
	      $tpl->set_var("MESSAGE", "PC member $email has been removed"); 
	    } 
	} 
      else 
	{ 
	  // Data comes from the Form 
	  $message = InsertPCMember ($_POST, $_POST['mode']); 
 
	  // Any error ? 
	  if (!empty($message)) 
	    { 
	      $member = CleanMember($_POST); 
	      $tpl->set_var("ERROR_MESSAGE", $message); 
	      $tpl->parse("MESSAGE", "TxtPostError"); 
  
	      $tpl->set_var("FORM_MEMBER",  
                  FormPCMember ($member, INSERTION, "Admin.php?action=1")); 
	    } 
	  else 
	    { 
	      // Everything is OK. Give the form with the stored values 
	      $tpl->set_var("PC_EMAIL", $_POST['email']);  
	      $tpl->parse("MESSAGE", "TxtPostMember"); 
	      $member = GetMember ($_POST['email'], $db);      
	      $tpl->set_var("FORM_MEMBER",  
                  FormPCMember ($member, MAJ, "Admin.php?action=1"));  
	    } 
	} 
    } 
  else 
    { 
      /* Display the form */ 
      $tpl->set_var("MESSAGE", "Add a PC member"); 
      $tpl->set_var("FORM_MEMBER",  
		FormPCMember (array(), INSERTION, "Admin.php?action=1")); 
    } 
 
  /* Select all the members and list them. 
     First extract the 'block' describing a line from the template */ 
 
  $tpl->set_block("TxtListMembers", "MEMBER_DETAIL", "MEMBERS"); 
 
  $tpl->set_var("MEMBERS", ""); 
  $query = "SELECT * FROM PCMember ORDER BY lastName"; 
  $result = $db->execRequete ($query); 
  $i= 0; 
  while ($member = $db->objetSuivant($result)) 
    { 
      InstanciateMemberVars ($member, $tpl, $db); 
      // Choose the CSS class 
      if ($i++ %2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      $tpl->set_var("PC_NAME", $member->firstName . " " . $member->lastName); 
      $tpl->set_var("PC_EMAIL", $member->email); 
      $tpl->set_var("PC_CODED_EMAIL", urlEncode($member->email)); 
      $tpl->set_var("ID_MESSAGE", PWD_REVIEWER); 
 
      $tpl->parse("MEMBERS", "MEMBER_DETAIL", true); 
    } 
  $tpl->parse("BODY", "TxtListMembers"); 
} 
 
// List the papers for a reviewer 
function AdmReviewerPapers ($email, &$tpl, $db, &$TEXTS) 
{ 
  $config = GetConfig($db); 
  $tpl->set_var("CONF_URL", $config['confURL']); 
  $tpl->set_var("SESSION_ID", session_id()); 
 
  $tpl->set_var("TITLE", $TEXTS->get("TTL_PAPERS_TO_REVIEW")); 
 
  /* Select all the papers and list them. 
     First extract the 'bloc' describing a line from the template */ 
 
  $tpl->set_block("TxtPapersToReview", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block("TxtPapersToReview", "FORUM_LINK"); 
  $tpl->set_block("PAPER_DETAIL", "ALL_REVIEWS", "REVIEWS"); 
  $tpl->set_block("PAPER_DETAIL", "MY_REVIEW", "REVIEW"); 
  //  $tpl->set_block("PAPER_DETAIL", "FORUM", "FORUM_PART"); 
 
  if ($config['discussion_mode'] != GLOBAL_DISCUSSION) 
    $tpl->set_var("FORUM_LINK", ""); 
 
  $tpl->set_var("PAPERS",""); 
 
  $query = "SELECT p.*, overall FROM Review r, Paper p" 
    . " WHERE email='$email' AND p.id=r.idPaper " ;
  //    . " AND isUploaded='Y'"; 
  $result = $db->execRequete ($query); 
  $nbPapers = $i = 0; 
  while ($paper = $db->objetSuivant($result)) 
    { 
      // Choose the CSS class 
      if ($i++ %2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      $nbPapers++; 
      // Instanciate vars of the paper 
      InstanciatePaperVars ($paper, $tpl, $db); 
      if (!$paper->overall) 
	$tpl->set_var("TXT_SUBMIT_REVIEW",  
		      "<font color='red'>" . 
		      $TEXTS->get("TXT_SUBMIT_REVIEW") . "</font>"); 
      else 
	$tpl->set_var("TXT_SUBMIT_REVIEW",  
		      "<font color='green'>" . 
		      $TEXTS->get("TXT_UPDATE_REVIEW") . "</font>"); 
 
      if ($config['discussion_mode'] != NO_DISCUSSION) 
	{ 
	  // Show all other reviews, do not propose to see only my review 
	  $tpl->set_var("REVIEW",""); 
	  // Show the messages 
	  $tpl->set_var("MESSAGES", 
			DisplayMessages($paper->id, 0, $db)); 
	  $tpl->parse("FORUM", "Forum"); 
	  $tpl->parse("REVIEWS","ALL_REVIEWS"); 
	} 
      else 
	{ 
	  // Show only my review, no forum, no other reviews 
	  $tpl->parse("REVIEW","MY_REVIEW"); 
	  $tpl->set_var("REVIEWS",""); 
	} 
      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
    } 
 
  if ($nbPapers == 0) 
    $tpl->set_var("PAPERS", "No papers"); 
 
  $tpl->parse("BODY", "TxtPapersToReview"); 
} 
 
 
// List the papers in the forum 
function AdmForum ($email, &$tpl, $db, &$TEXTS, $i_min, $i_max) 
{ 
  $config = GetConfig($db); 
  $tpl->set_var("CONF_URL", $config['confURL']); 
  $tpl->set_var("SESSION_ID", session_id()); 
  $class= 'even'; 
 
  /* Select all the papers which are NOT in conflict with the 
    reviewer and list them. 
     First extract the 'bloc' describing a line from the template */ 
 
  $query = "SELECT p.* FROM Paper p, Rating ra " 
    . " WHERE ra.email='$email' AND ra.idPaper=p.id AND ra.rate > 0 "; 
 
  $result = $db->execRequete ($query); 
  $nbPapers = 0; 
  $i = 0; 
  while ($paper = $db->objetSuivant($result)) 
    { 
      $nbPapers++; 
      $i++; 
      if ($i >= $i_min and $i <= $i_max) 
	{ 
	  if ($class == 'even') $class = 'odd'; else $class='even'; 
	  $tpl->set_var("CSS_CLASS", $class); 
	  // Instanciate vars of the paper 
	  InstanciatePaperVars ($paper, $tpl, $db); 
      // Show all other reviews, do not propose to see only my review 
	  $tpl->set_var("REVIEW",""); 
	  // Show the messages 
	  $tpl->set_var("MESSAGES", 
			DisplayMessages($paper->id, 0, $db, TRUE, "Forum.php")); 
	  $tpl->parse("FORUM", "Forum"); 
 
	  $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
	} 
    } 
   
  // Create the groups 
  $nb_groups = $nbPapers / SIZE_FORUM + 1; 
  for ($i=1; $i <= $nb_groups; $i++) 
    { 
      $iMin = (($i-1) *  SIZE_FORUM) + 1; 
      if ($iMin >= $i_min and $iMin <= $i_max) 
	$link = "<font color=red>$i</font>"; 
      else 
	$link =$i; 
      $tpl->set_var("LINK", $link); 
       
      $tpl->set_var("IMIN_VALUE", $iMin); 
      $tpl->set_var("IMAX_VALUE", $iMin + SIZE_FORUM -1); 
      $tpl->parse("LINKS", "GROUPS_LINKS", true); 
    } 
 
  if ($nbPapers == 0) 
    $tpl->set_var("PAPERS", "No papers"); 
   
  $tpl->parse("BODY", "TxtPapersInForum"); 
} 
 
// Summary of paper assignment 
function SummaryPapersAssignment (&$tpl, $db, &$TEXTS) 
{ 
  // This function might potentially reach the memory 
  // limit of PHP. Check that this does not happen 
  if (function_exists ("memory_get_usage")) 
    { 
      // The following instruction can raise the memory limit 
      ini_set ("memory_limit", "100M"); 
      ini_set ("max_execution_time", "300"); // 5 mns  
    } 
 
  $config = GetConfig($db); 
  /* Check whether there is a prefered topic for papers */ 
  if ($config['selectedPaperTopic']) 
    $prefPaperTopic = $config['selectedPaperTopic'] ; 
  else 
    $prefPaperTopic= "%"; 
 
  /* Check whether there is a prefered topic for reviewers */ 
  if ($config['selectedReviewerTopic']) 
    $prefReviewerTopic = $config['selectedReviewerTopic'] ; 
  else 
    $prefReviewerTopic= "%"; 
 
  $tpl->set_var("TITLE", $TEXTS->get("TTL_SUMMARY_ASSIGNMENT")); 
 
  // Decompose the blocks of the template 
  $tpl->set_block("TxtSummaryAssignment", "MEMBER_DETAIL", "MEMBERS"); 
  $tpl->set_block("TxtSummaryAssignment", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block("TxtSummaryAssignment", "NAVIGATION_TABLE", "NAVIGATION"); 
  $tpl->set_block("PAPER_DETAIL", "ASSIGNMENT_DETAIL", "ASSIGNMENTS"); 
 
  $tpl->set_var("ASSIGNMENTS", ""); 
  $tpl->set_var("MEMBERS", ""); 
 
  // Get the list of topics 
  $qTopic = "SELECT * FROM ResearchTopic"; 
  $rTopic = $db->execRequete($qTopic); 
  $defaultTopic = "Any"; 
  $topics = array(0 => $defaultTopic); 
  $fTopic = new Formulaire ("",""); 
  while ($topic = $db->objetSuivant($rTopic)) 
    $topics[$topic->id] = $topic->label; 
 
  // Show the selection list 
  if (count($topics) != 0) 
    { 
      $tpl->set_var ("LIST_PAPER_TOPICS", 
	   $fTopic->champSELECT ("paperTopic", $topics, $prefPaperTopic, 1)); 
      $tpl->set_var ("LIST_REVIEWER_TOPICS", 
      $fTopic->champSELECT ("reviewerTopic", $topics, $prefReviewerTopic, 1)); 
    } 
  else 
    { 
      $tpl->set_var ("LIST_PAPER_TOPICS","No topics"); 
      $tpl->set_var ("LIST_PAPER_TOPICS","No topics"); 
    } 
 
  /* Store the list of reviewers in an array (+easier, +efficient). 
     Note: one cannot keep the object because of the PHP 5 reference 
     mechanism 
  */ 
  if ($prefReviewerTopic != '%') 
    { 
      $qMembers = "SELECT DISTINCT * FROM PCMember p, SelectedTopic s " 
	 . "WHERE p.email = s.email AND idTopic='$prefReviewerTopic' " 
	 . " AND roles LIKE '%R%' ORDER BY lastName"; 
    } 
  else 
    { 
      $qMembers = "SELECT DISTINCT * FROM PCMember " 
	. "WHERE roles LIKE '%R%' ORDER BY lastName"; 
    } 
 
  $members = array(); 
  $lMembers = ""; 
  $rMembers = $db->execRequete($qMembers); 
  $nb_members = 0; 
  while ($member = $db->objetSuivant($rMembers)) 
    { 
      $nb_members++; 
      $members[$nb_members] = $member->email; 
    } 
 
  // Same thing for papers 
  $query =  
    "SELECT DISTINCT p.* FROM (Paper p LEFT JOIN PaperTopic t  " 
    .   " ON p.id=t.id_paper) " 
    . " WHERE topic LIKE '$prefPaperTopic' OR t.id_topic='$prefPaperTopic' " 
    . " ORDER BY id"; 
  $papers=array(); 
  $result = $db->execRequete ($query); 
  $nb_papers = 0; 
  while ($paper = $db->objetSuivant($result)) 
    { 
      $nb_papers++; 
      $papers[$nb_papers] = $paper->id; 
    } 
 
  // Manage the navigation table 
  if ($nb_papers > MAX_ITEMS_IN_ASSIGNMENT 
      or $nb_members > MAX_ITEMS_IN_ASSIGNMENT) 
    { 
      // Show the navigation table 
      $tpl->set_var("NB_PAPERS", $nb_papers); 
      $tpl->set_var("NB_REVIEWERS", $nb_members); 
      $tpl->set_var("MAX_ITEMS_IN_ASSIGNMENT", MAX_ITEMS_IN_ASSIGNMENT); 
 
      if (isSet($_REQUEST['i_paper_min'])) 
	{ 
	  // The request comes from the navigation table 
	  $i_paper_min = $_REQUEST['i_paper_min']; 
	  $i_paper_max = min($_REQUEST['i_paper_max'], $nb_papers); 
	  $i_member_min = $_REQUEST['i_member_min']; 
	  $i_member_max = min($_REQUEST['i_member_max'], $nb_members); 
	} 
      else 
	{ 
	  $i_paper_min = 1; $i_paper_max = min($nb_papers, 
					       MAX_ITEMS_IN_ASSIGNMENT); 
	  $i_member_min = 1; $i_member_max = min($nb_members, 
						 MAX_ITEMS_IN_ASSIGNMENT); 
	} 
      // Show the navigation table 
      $tpl->set_var("NAV_TABLE", ""); 
      $lines = ""; 
      $script="Admin.php?action=10"; 
      for ($i = 1; $i <= $nb_papers;  $i+=MAX_ITEMS_IN_ASSIGNMENT) 
	{ 
	  $line=""; 
	  for ($j=1; $j <= $nb_members; $j+=MAX_ITEMS_IN_ASSIGNMENT) 
	    { 
	      $link = $script . "&i_paper_min=$i" 
		. "&i_paper_max=" . ($i + MAX_ITEMS_IN_ASSIGNMENT -1) 
		. "&i_member_min=$j" 
		. "&i_member_max=".($j + MAX_ITEMS_IN_ASSIGNMENT - 1); 
 
	      if ($i==$i_paper_min and $j==$i_member_min) 
		$line .= "<td bgcolor=lightblue><a href='$link'>" 
		  . "<font color=white>$i/$j</font></a></td>"; 
	      else 
		$line .= "<td><a href='$link'>$i/$j</a></td>"; 
	    } 
	  $lines .=  "<tr>$line</tr>\n"; 
	} 
      $tpl->set_var("NAV_TABLE", $lines); 
      $tpl->parse("NAVIGATION", "NAVIGATION_TABLE");    } 
  else 
    { 
      // Hide the navigation table 
      $i_paper_min = $i_member_min = 1; 
      $i_paper_max = $nb_papers; $i_member_max = $nb_members; 
      $tpl->set_var("NAVIGATION", ""); 
    } 
  // Put the current values in the template 
  $tpl->set_var("I_PAPER_MIN", $i_paper_min); 
  $tpl->set_var("I_PAPER_MAX", $i_paper_max); 
  $tpl->set_var("I_MEMBER_MIN", $i_member_min); 
  $tpl->set_var("I_MEMBER_MAX", $i_member_max); 
 
  //  echo "I paper min=$i_paper_min I paper max = $i_paper_max<br>"; 
 
  // OK, now create the table. First the columns' headers 
  for ($j=$i_member_min; $j <= $i_member_max; $j++) 
    { 
      $member = GetMember ($members[$j], $db, "object"); 
      InstanciateMemberVars ($member, $tpl, $db); 
      $tpl->set_var("MEMBER_NB_PAPERS", CountPapers($members[$j], $db)); 
      $tpl->parse("MEMBERS", "MEMBER_DETAIL", true); 
    } 
 
  // then each line 
  $tpl->set_var("PAPERS", ""); 
  for ($i = $i_paper_min; $i <= $i_paper_max; $i++) 
    { 
      // Choose the CSS class 
      if ($i%2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      $paper = GetPaper ($papers[$i], $db, "object"); 
      // Instanciate the paper entities  
      InstanciatePaperVars ($paper, $tpl, $db); 
      $tpl->set_var("SESSION_ID", session_id()); 
      $tpl->set_var("CONF_URL", $config['confURL']); 
 
      // Get the ratings of each PC member 
      for ($j=$i_member_min; $j <= $i_member_max; $j++) 
	  { 
	    $email = $members[$j]; 
	    $val = GetRatingValue ($paper->id, $email, $db);  
	    $tpl->set_var("MEMBER_EMAIL", $email); 
 
            $tpl->set_var("BG_COLOR", "white"); 
	    $tpl->set_var("PAPER_RATING", $val); 
	    $tpl->set_var("CHECKED_YES", ""); 
	    $tpl->set_var("CHECKED_NO", "checked"); 
 
	    // Check if the paper is assigned 
	    $review = GetReview($paper->id, $email, $db); 
            if ($review) 
              { 
		$tpl->set_var("BG_COLOR", "yellow"); 
		$tpl->set_var("PAPER_RATING", $val); 
		$tpl->set_var("CHECKED_YES", "checked"); 
		$tpl->set_var("CHECKED_NO", ""); 
	       } 
 
	    // Add to the assignment line 
	    $tpl->parse("ASSIGNMENTS", "ASSIGNMENT_DETAIL", true); 
	  } 
      // Add to the list of papers 
      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
      $tpl->set_var("ASSIGNMENTS", ""); 
    } 
  /* Put the result in BODY */ 
  $tpl->parse("BODY", "TxtSummaryAssignment"); 
} 
 
// List all the registrations 
function AdmListRegistrations (&$tpl, $db, &$TEXTS, &$CODES) 
{ 
  $tpl->set_file ("ListRegistrations", TPLDIR . "TxtListRegistrations.tpl"); 
  $tpl->set_block("ListRegistrations", "GROUPS_LINKS", "LINKS"); 
  $tpl->set_block("ListRegistrations", "REGISTRATION_DETAIL",  
		  "REGISTRATIONS"); 
  $tpl->set_var("REGISTRATIONS",""); 
  $tpl->set_var("LINKS", ""); 
 
  $tpl->set_var("TITLE", $TEXTS->get("TTL_LIST_REGISTRATIONS"));  
 
  // Initialize the current interval 
  if (!isSet($_REQUEST['iMin'])) 
    { 
      $iMinCur = 1; $iMaxCur = SIZE_REGISTRATIONS_GROUP; 
    } 
  else 
    { 
      $iMinCur = $_REQUEST['iMin'];  $iMaxCur = $_REQUEST['iMax']; 
    } 
 
  /* Removal of a registration */ 
  if (isSet($_REQUEST['remove'])) { 
    $id_person = $_REQUEST['id_person']; 
    $db->execRequete ("DELETE FROM Person WHERE id='$id_person'"); 
  } 
 
  /* Payment */ 
  if (isSet($_REQUEST['confirm_payment'])) { 
    $id_person = $_REQUEST['id_person']; 
    $db->execRequete ("UPDATE Person SET payment_received='Y' " 
		      . " WHERE id='$id_person'"); 
  } 
 
  $config = GetConfig($db); 
  $nbPersons = 0; 
 
  $query = "SELECT * FROM Person ORDER BY last_name"; 
  $result = $db->execRequete ($query); 
  $i = 0; 
  while ($person = $db->objetSuivant($result)) 
    { 
      $nbPersons++; 
      InstantiatePersonVars ($person, $tpl, $db); 

      // Get the choices 
      $q_choices = 
	"SELECT * FROM PersonChoice p, RegQuestion r, RegChoice c "
	. " WHERE p.id_person='$person->id' AND p.id_question=r.id "
	. " AND c.id_choice=p.id_choice ";
      $r_choices = $db->execRequete ($q_choices);
      $list_choices = "";
      while ($choice = $db->objetSuivant($r_choices)) {
	$list_choices .= "<li>$choice->question: $choice->choice</li>";
      }
      $tpl->set_var("PERSON_CHOICES", "<ol>$list_choices</ol>");

      // Choose the CSS class 
      if ($i++ %2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      if ($nbPersons >= $iMinCur and $nbPersons <= $iMaxCur)  
	$tpl->parse("REGISTRATIONS", "REGISTRATION_DETAIL", true); 
    } 
  $tpl->set_var("REGISTRATION_COUNT", $nbPersons); 
   
  // Create the groups 
  $nb_groups = $nbPersons / SIZE_REGISTRATIONS_GROUP ; 
  if ($nb_groups * SIZE_REGISTRATIONS_GROUP < $nbPersons) 
    $nb_groups++; 
 
  for ($i=1; $i <= $nb_groups; $i++) 
    { 
      $iMin = (($i-1) *  SIZE_REGISTRATIONS_GROUP) + 1; 
      if ($iMin >= $iMinCur and $iMin <= $iMaxCur) 
	$link = "<font color=red>$i</font>"; 
      else 
	$link =$i; 
      $tpl->set_var("LINK", $link); 
       
      $tpl->set_var("IMIN_VALUE", $iMin); 
      $tpl->set_var("IMAX_VALUE", $iMin + SIZE_REGISTRATIONS_GROUP -1); 
      $tpl->parse("LINKS", "GROUPS_LINKS", true); 
    } 
 
  /* Instanciate PAPERS in TxtListOfPapers. Put the result in BODY */ 
  $tpl->parse("BODY", "ListRegistrations"); 
} 
 
?>
