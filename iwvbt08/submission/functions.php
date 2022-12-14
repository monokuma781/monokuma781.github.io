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

// Get a table row 
function GetRow ($query, $db, $mode="array") 
{
  $result = $db->execRequete ($query);
  if ($mode == "array")
    return  $db->ligneSuivante ($result);
  else
    return $db->objetSuivant ($result);
}

/************************************************************

             Papers management

*************************************************************/
// Set the config

function SetConfig ($db, $papersUploaded, $paperWithMissingReview, 
		    $paperWithStatus) 
{
  $qConfig="update Config set papersWithTitle='Any', ".
    "papersWithAuthor='Any', ".
    "papersUploaded='$papersUploaded', ".
    "papersWithStatus='$paperWithStatus', ".
    "papersWithFilter='0', ".
    "papersWithRate='0.00', ".
    "papersWithReviewer='All', ".
    "papersWithTopic='0', ".
    "papersWithConflict='A', ".
    "papersWithMissingReview='$paperWithMissingReview'";
  $db->execRequete($qConfig);
  GetCurrentSelection($db);
}


// Get the config
function GetConfig ($bd, $mode="array")
{
  // There should be only one line in Config
  $query = "SELECT * FROM Config";
  $result = $bd->execRequete ($query);
  if ($mode == "array")
    $config = $bd->ligneSuivante ($result);
  else
    $config = $bd->objetSuivant ($result);
  return $config;
}

// Set the standard infos for the standard template
   
function SetStandardInfo ($bd, &$tpl)
{
  $config = GetConfig($bd, "object");
  $tpl->set_file ("HELP", TPLDIR . "Help.tpl");
  $tpl->set_var("CONF_NAME", $config->confName);
  $tpl->set_var("CONF_ACRONYM", $config->confAcronym);
  $tpl->set_var("CONF_MAIL", $config->confMail);
  $tpl->parse ("HELP", "HELP");
}

function CheckEMail($email){
  // Check the fields of an email
  return eregi("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$", $email);
}

// Check the infos about a paper before inserting
function CheckPaper ($paper, $file, $fileRequired, &$MESSAGES, $db)
{
  $config = GetConfig($db);
  $message = array();
  
  // Some tests...
  if (empty ($paper['title']))  $message[] = 
				  $MESSAGES->get("MISSING_TITLE");

  if ($config['extended_submission_form'] == "Y")
    {
      $last_names = $paper["last_name"];
      $found = false;
      foreach ($last_names as $name)
	if (!empty ($name)) $found = true;
      if (!$found) $message[] = $MESSAGES->get("MISSING_AUTHORS");
    }
  else 
    if (empty($paper['authors']))
      $message[] = $MESSAGES->get("MISSING_AUTHORS");

  if (empty ($paper['emailContact']))  
    $message[] = $MESSAGES->get("MISSING_EMAIL");
  else
    if ($paper['emailContact'] != $paper['confirmEmail'])
      $message[] = $MESSAGES->get("EMAIL_ERROR");
    else if (!CheckEMail($paper['emailContact']))
      $message[] = $MESSAGES->get("INVALID_EMAIL");
    
  if (empty ($paper['abstract']))  
    {
      $message[] = $MESSAGES->get("MISSING_ABSTRACT");
    }
  // Check that the topic is not null or blanck
  if (!empty($paper['topic']))
    {
      $topic = LibelleCodif ("ResearchTopic", $paper['topic'], $db); 
      if (empty($topic))
	$message[] = $MESSAGES->get("MISSING_TOPIC");
    }


  if (empty($paper['format']) and $fileRequired)
    $message[]=  $MESSAGES->get("MISSING_FORMAT");

  if ($fileRequired) {
      if (!is_uploaded_file ($file['tmp_name']))
	$message[] = UploadError ($file, $MESSAGES);
      else {
	// Check the format (always in lowercase)
	$ext = substr($file['name'], strrpos($file['name'], '.') + 1);
	if (strToLower($ext) != $paper['format']) 
	  $message[]= $MESSAGES->get("INVALID_FORMAT") . 
	    " (extension:$ext, format:" . $paper['format'] . ")";
      }
  }
  return $message;
}

// Compute the password to access a paper
function PWDPaper ($id, $seed)
{
  // MD5 encryption
  return substr (md5($seed . $id), 1, 6);
}

// Compute the file name of the submitted file
function FNamePaper ($dir, $id, $ext="pdf")
{
  return $dir  . "/" . PAPER_PREFIX . $id . "." . $ext;
}

// Compute the file name of the camera-ready file
function CRNamePaper ($dir, $status, $id, $ext="pdf")
{
  return "$dir/CR$status/" .  PAPER_PREFIX . "$id.$ext";
}


// Store the file
function StorePaper ($id, $fic, $format, $db)
{
  $config = GetConfig($db);
  // Encode the file name
  $paperName = FNamePaper  ($config['uploadDir'], $id, $format);
  $destination =  $paperName ;

  if (!copy($fic['tmp_name'], $destination))
    return FALSE;
  else
    return TRUE;
}

// Store the camera ready file
function StoreCRPaper ($status, $id, $fic, $format, $db)
{
  $config = GetConfig($db);
  // Encode the file name
  $paperName = FNamePaper  ($config['uploadDir']."/CR".$status, $id, $format);
  $destination =  $paperName ;
  
  if (!is_dir($config['uploadDir']."/"."CR".$status)) 
    mkdir($config['uploadDir']."/"."CR".$status, 0777);

  if (!copy($fic['tmp_name'], $destination))
    return FALSE;
  else
    return TRUE;
}

// Get a paper with its id
function GetPaper ($id, $bd, $mode="array") 
{
  $query = "SELECT * FROM Paper WHERE id = '$id'" ;
  $result = $bd->execRequete ($query);
  if ($mode == "array")
    $paper = $bd->ligneSuivante ($result);
  else
    $paper = $bd->objetSuivant ($result);
  return $paper;
}

// Get the list of authors for a paper
function GetAuthors ($id, $bd, $blind="N", $mode="array", $others="")
{
  $query = "SELECT * FROM Author WHERE id_paper = '$id' ORDER BY position" ;
  $result = $bd->execRequete ($query);
  if ($mode == "array")
    {
      $authors = array();
    }
  else
    {
      // Blind review? Never show the authors
      if ($blind == "Y") return "(blind review)";
      $comma = ""; $authors = "";
    }
  $i = 0;
  while ($author = $bd->objetSuivant($result))
    {
      if ($mode == "array")
	{
	  $authors[$i]["first_name"] = $author->first_name;
	  $authors[$i]["last_name"] = $author->last_name;
	  $authors[$i]["affiliation"] = $author->affiliation;
	}
      else // Create a string
	{
	  $authors .= "$comma $author->first_name $author->last_name" ;
	  // . "($author->affiliation)";	 
	  $comma = ", ";
	}
      $i++;
    }
  if ($mode == "string" and !empty($others)) $authors .= "$comma $others";
  return $authors;
}

// Get the list of topics for a paper
function GetPaperTopics ($id_paper, $bd, $mode="array")
{
  $query = "SELECT r.id, r.label FROM PaperTopic p, ResearchTopic r "
    . " WHERE id_paper = '$id_paper' AND r.id=p.id_topic";
  $result = $bd->execRequete ($query);
  if ($mode == "array")
      $topics = array();
  else {
      $comma = ""; $topics = "";
  }
  $i = 0;
  while ($topic = $bd->objetSuivant($result))
    {
      if ($mode == "array")
	  $topics[$topic->id] = $topic->label;
      else // Create a string
	{
	  $topics .= "$comma $topic->label" ;
	  $comma = ", ";
	}
      $i++;
    }
  return $topics;
}

// Count the papers
function CountAllPapers ($bd) 
{
  $query = "SELECT COUNT(*) AS nbPapers FROM Paper ";
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if ($rev)
    return $rev->nbPapers;
  else
    return 0;
}

// Count the reviewers
function CountAllReviewers ($bd) 
{
  $query = "SELECT COUNT(*) AS nbReviewers FROM PCMember "
    . " WHERE roles LIKE '%R%'";
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if ($rev)
    return $rev->nbReviewers;
  else
    return 0;
}


// Get the list of status
function GetListStatus ($db) 
{
  $listS = array();
  $query = "SELECT * FROM PaperStatus ";
  $result = $db->execRequete ($query);
  while ($status =  $db->objetSuivant ($result))
    {
      $listS[$status->id] = array("label" => $status->label, 
				  "mailTemplate" => $status->mailTemplate);
    }
  return $listS;
}

// Get the list of criterias
function GetListCriterias ($db) 
{
  $listC = array();
  $query = "SELECT * FROM Criteria ORDER BY id";
  $result = $db->execRequete ($query);
  while ($cr =  $db->objetSuivant ($result))  {
    $listC[$cr->id] = array("label" => $cr->label, 
			    "explanations" => $cr->explanations,
			    "weight" => $cr->weight);
  }
  return $listC;
}

// Get a review mark
function GetReviewMark ($idPaper, $email, $idCriteria, $bd, $mode="array")
{
  $query = "SELECT * FROM ReviewMark WHERE idPaper = '$idPaper' "
    . "AND email='$email' AND idCriteria='$idCriteria'" ;
  $result = $bd->execRequete ($query);
  if ($mode == "array")
    $reviewMark = $bd->ligneSuivante ($result);
  else
    $reviewMark = $bd->objetSuivant ($result);
  return $reviewMark;
}

/************************************************************

             PC members management

*************************************************************/

// Check the infos 
function CheckPCMember ($member)
{
  $message = "";
  
  // Some tests...

  if (empty ($member['email']))  $message .= "No email<br>";
  else if (!CheckEMail($member['email']))
    $message .= "Invalid mail format. Mails must be of the form X@Y.Z.<br>";
  
  if (empty ($member['firstName']))  $message .= "No first name<br>";
  if (empty ($member['lastName']))  $message .= "No last name<br>";
  if (empty ($member['affiliation']))  $message .= "No affiliation<br>";
  if (!isSet($member['roles']))  
    {
      $member['roles'] = "";
      $message .= "At least one role is required<br>";
    }
  return $message;
}

// Compute the password for a PC member
function PWDMember ($email, $seed)
{
  // MD5 encryption
  return substr (md5($seed . strToLower($email)), 1, 6);
}

// Strip the slashes
function CleanMember ($member)
{
  $member['firstName'] = stripSlashes ($member['firstName']);
  $member['lastName'] = stripSlashes ($member['lastName']);
  $member['affiliation'] = stripSlashes ($member['affiliation']);
  return $member;
}

// Get a member given his email
function GetMember ($email, $db, $mode="array") 
{
  $safe_email = $db->prepareString($email);
  $query = "SELECT * FROM PCMember WHERE email = '$safe_email'" ;
  $result = $db->execRequete ($query);
  if ($mode == "array")
    return $db->ligneSuivante ($result);
  else
    return $db->objetSuivant ($result);
}

// Get a person given his id
function GetPerson ($id_person, $db, $mode="array") 
{
  $query = "SELECT * FROM Person WHERE id = '$id_person'" ;
  $result = $db->execRequete ($query);
  if ($mode == "array")
    return $db->ligneSuivante ($result);
  else
    return $db->objetSuivant ($result);
}

// Check that the member is an administrator
function IsAdmin ($email, $db) 
{
  $member = GetMember ($email, $db);
  return strstr($member['roles'], "A");
}

/************************************************************

             Review management

*************************************************************/

// Strip the slashes
function CleanArray ($arr)
{
  while (list($key, $val) = each($arr))
    $arr[$key] = stripSlashes($val);

  return $arr;
}

// Get a review with its key
function GetReview ($idPaper, $email, $bd) 
{
  // First get the review
  $query = "SELECT * FROM Review "
    . " WHERE idPaper = '$idPaper' and email='$email' "; 

  $result = $bd->execRequete ($query);
  $review = $bd->ligneSuivante ($result);

  if (!$review) return $review;

  // Else  get the criterias
  $listC = GetListCriterias ($bd);
  foreach ($listC as $id => $crVals)
    {
      $qCr = "SELECT * FROM ReviewMark WHERE idPaper='$idPaper' "
	. "AND email='$email' AND idCriteria='$id'";
      $rCr = $bd->execRequete($qCr);
      $cr = $bd->objetSuivant($rCr);
      if (is_object($cr))
	$review[$id] = $cr->mark;
      else
	$review[$id] = "";
    }
  return $review;
}

// Delete a review and its marks
function DeleteReview ($idPaper, $email, $db) 
{
  // Delete the review if it is empty
  $query = "SELECT * FROM Review "
    . " WHERE idPaper = '$idPaper' and email='$email' ";
  $result = $db->execRequete ($query);

  // Do not delete a review already submitted
  if ($rev = $db->objetSuivant($result))
    {
      if ($rev->overall)
        FatalError (sprintf (FE_DELETE_NONEMPTY_REVIEW, $email, $idPaper));
      else
	{
	  $query = "DELETE FROM Review "
	    . " WHERE idPaper = '$idPaper' and email='$email' ";
	  $result = $db->execRequete ($query);
	  // Delete the marks
	  $query = "DELETE FROM ReviewMark "
	    . " WHERE idPaper = '$idPaper' and email='$email' ";
	  $db->execRequete ($query);
	}
    }
}

// Get a rating with its key
function GetRating ($idPaper, $email, $bd) 
{
  $query = "SELECT * FROM Rating "
    . " WHERE idPaper = '$idPaper' and email='$email' "; 

  $result = $bd->execRequete ($query);
  return $bd->objetSuivant ($result);
}

// Get from the rating box with its key
function GetRatingBox ($idPaper, $email, $bd) 
{
  $query = "SELECT * FROM RatingBox "
    . " WHERE idPaper = '$idPaper' and email='$email' "; 
  $result = $bd->execRequete ($query);
  return $bd->objetSuivant ($result);
}

// Get a rating value with its key
function GetRatingValue ($idPaper, $email, $bd) 
{
  $query = "SELECT * FROM Rating "
    . " WHERE idPaper = '$idPaper' and email='$email' "; 

  $result = $bd->execRequete ($query);
  $rating = $bd->objetSuivant ($result);
  if (is_object($rating))
    return $rating->rate;
  else
    return RATE_DEFAULT_VALUE;
}

// Get a message its key
function GetMessage ($idMessage, $bd) 
{
  $query = "SELECT * FROM Message "
    . " WHERE idMessage = '$idMessage'";

  $result = $bd->execRequete ($query);
  return $bd->objetSuivant ($result);
}

// Get a correlation with its key
function GetCorrelation ($email1, $email2, $bd) 
{
  $query = "SELECT * FROM Correlation "
    . " WHERE email1='$email1' and email2='$email2' "; 
  $result = $bd->execRequete ($query);
  return $bd->objetSuivant ($result);
}

// Count the reviewers for a paper
function CountReviewers ($idPaper, $bd) 
{
  $query = "SELECT COUNT(*) AS nbRev FROM Review "
    . " WHERE idPaper = '$idPaper'" ;
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if ($rev)
    return $rev->nbRev;
  else
    return 0;
}

// Compute statistics for a paper
function StatPaper ($idPaper, $db) 
{
  $stat = array();
  // Get the criterias, and compute the average for each
  $listC = GetListCriterias ($db);
  foreach ($listC as $id => $crVals)
    {
      $qCr = "SELECT round(AVG(mark),4) AS mark FROM ReviewMark "
	. "WHERE idPaper='$idPaper' "
	. "AND idCriteria='$id'";
      $rCr = $db->execRequete($qCr);
      $cr = $db->objetSuivant($rCr);
      if (is_object($cr))
	$stat[$id] = $cr->mark;
      else
	$stat[$id] = "";
    }
  return $stat;
}

// Get an array of the reviewers for a paper
function GetReviewers ($idPaper, $bd) 
{
  $tabReviewers = array();
  $query = "SELECT p.* FROM Review r, PCMember p "
    . " WHERE r.idPaper = '$idPaper' AND p.email=r.email ";
  $result = $bd->execRequete ($query);
  while ($rev =  $bd->objetSuivant ($result))
    $tabReviewers[] = $rev;

  return $tabReviewers;
}

// Count the papers for a reviewer
function CountPapers ($email, $bd) 
{
  $query = "SELECT COUNT(*) AS nbPapers FROM Review "
    . " WHERE email = '$email' " ;
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if ($rev) 
    return $rev->nbPapers;
  else 
    return 0;
}

// Determine whether there is a conflict for a paper
function PaperInConflict ($idPaper, $bd) 
{
  $query = "SELECT p.* FROM Paper p, Review r1, Review r2"
    . " WHERE p.id=$idPaper AND r1.idPaper=$idPaper AND r2.idPaper=$idPaper "
    . "AND r1.overall IS NOT NULL "
    . "AND r2.overall IS NOT NULL "
    . "AND r1.email != r2.email "
    . "AND ABS(r1.overall - r2.overall) >= " . CONFLICT_GAP ;
    
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if (is_object($rev)) 
    return true;
  else 
    return false;
}

// Determine whether there is a missing review for a paper
function MissingReview ($idPaper, $bd) 
{
  $query = "SELECT p.* FROM Paper p, Review r1"
    . " WHERE p.id=$idPaper AND r1.idPaper=$idPaper AND r1.overall IS NULL";
    
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if (is_object($rev)) 
    return true;
  else 
    return false;
}

// Compute the overall rate for a paper
function Overall ($idPaper, $bd) 
{
  // Note: does not take account of NULL values
  $query = "SELECT AVG(overall) AS overall FROM Review WHERE idPaper=$idPaper";
  
  $result = $bd->execRequete ($query);
  $rev =  $bd->objetSuivant ($result);
  if (is_object($rev)) 
    return $rev->overall;
  else 
    return 0;
}

// Average rating for a user
function AvgUserRating ($email, $bd)
{
  $requete = "SELECT AVG(rate) AS avgRate FROM Rating "
    . "WHERE email='$email' AND rate!=0 AND significance=1";
  $res = $bd->execRequete ($requete);
  $obj = $bd->objetSuivant ($res);
  return $obj->avgRate;
}

// Encapsulate the mail function
function SendMail ($to, $subject, $mail, 
		   $from="", $replyTo="", $cc="")
{
  // Construct the header
  $header = "";
  if (!empty($from)) $header .= "From: $from\r\n";
  if (!empty($cc)) $header .= "Cc: $cc\r\n";
  if (!empty($cc)) $header .= "Reply-to: $replyTo\r\n";

  // Add the signature file
  /*  if (file_exists("Signature"))
    {
      $mail .= readfile ("Signature");
    }
  */
  // Use the standard mail function
  // Sometimes the -f option does not work
  //mail ($to, $subject, $mail, $header, "-f $from");
  mail ($to, $subject, $mail, $header);
}


// Function to select a new batch of papers for rating
function SelectPapersForRating ($db)
{
  $config = GetConfig ($db);

  // Loop on PC members
  $qUsers = "SELECT * FROM PCMember WHERE roles LIKE '%R%' ";
  $rUsers = $db->execRequete($qUsers);
  while ($user = $db->objetSuivant($rUsers))
    {
      $iPaper = 0;
      // Loop on papers
      if ($config['ballot_mode'] == GENERAL_BALLOT)
	$qPapers = "SELECT * FROM Paper";
      else
	// Loop on papers that match the reviewer's topics
	$qPapers = 
	  "SELECT * FROM (Paper p LEFT JOIN PaperTopic t ON id=id_paper), "
	  . " SelectedTopic s "
	  . " WHERE (p.topic=s.idTopic OR t.id_topic=s.idTopic) "
	  . " AND s.email='$user->email' ";

      $rPapers = $db->execRequete($qPapers);
      while ($paper = $db->objetSuivant($rPapers))
	{
	  $conflict = false;
	  // Insert the pair in the rating box
	  SQLRatingBox ($user->email, $paper->id, $db);
	    // Get the rate, if exists
	  $rating = GetRating ($paper->id, $user->email, $db);
	  if (!is_object($rating)) 
	    {
	      // OK, the preference is unset. Check whether there is a conflict
	      $safe_affiliation = $db->prepareString ($user->affiliation);
	      $safe_fname = $db->prepareString ($user->firstName);
	      $safe_lname = $db->prepareString ($user->lastName);
	      $qConflict = "SELECT * FROM Author "
		. " WHERE id_paper='$paper->id' "
		. " AND affiliation='$safe_affiliation'";
	      $rConflict = $db->execRequete($qConflict);
	      if (mysql_num_rows($rConflict) > 0) $conflict = true;
	      $qConflict = "SELECT * FROM Author "
		. " WHERE id_paper='$paper->id' "
		. " AND first_name='$safe_fname' "
		. " AND last_name='$safe_lname' ";
	      $rConflict = $db->execRequete($qConflict);
	      if (mysql_num_rows($rConflict) > 0)  $conflict = true;
	      if ($conflict)
		{
		  // Conflict! The default preference is 0
		  SQLRating ($user->email, $paper->id, 0, 1, $db);
		}
	      else
		{
		  // Check whether some topics match
		  $qTopic = "SELECT * FROM SelectedTopic s "
		    . "WHERE $paper->topic=s.idTopic AND s.email='$user->email' ";
		  $rTopic = $db->execRequete($qTopic);
		  if (mysql_num_rows($rTopic) > 0)
		    {
		      // Match! The default preference is 3
		      SQLRating ($user->email, $paper->id, 3, 1, $db);
		    }
		  else // The default preference is 2
		    {
		      SQLRating ($user->email, $paper->id, 2, 1, $db);
		    }
		}
	    }
	} // Loop on papers
    } // End of loop on users
}

// Recherche de l'intitul? d'une codif
function LibelleCodif ($nomCodif, $code, $db) 
{
  $query = "SELECT * FROM $nomCodif WHERE id = '$code'" ;
  $result = $db->execRequete ($query);
  $codif = $db->ligneSuivante ($result);
  return $codif['label'];
}

/***************** INSTANCIATION OF TEMPLATES VARIABLES ************/
function InstanciatePaperVars ($paper, &$tpl, $db, 
			       $id_session="", $html=true)
{
  $config = GetConfig($db);

  if (empty($id_session)) $id_session = session_id();
  $session = GetSession ($id_session, $db); 

  // Instanciate template variables related to a paper
  $tpl->set_var("PAPER_ID", $paper->id);
  $tpl->set_var("PAPER_TITLE", String2HTML($paper->title, $html));

  $blind_review = false;
  if (is_object($session)) {
    // If blind review is 'Y', hide the authors names,
    // except for chairs
    if ($config["blind_review"] == "Y" and 
	!strstr($session->roles, "C"))
      $blind_review = true;
  }
  
  $tpl->set_var("PAPER_AUTHORS", 
		String2HTML(GetAuthors($paper->id, $db, $blind_review, 
				       "string", $paper->authors), $html));
  $tpl->set_var("PAPER_PASSWORD", PWDPaper($paper->id, 
					   $config['passwordGenerator']));
  $tpl->set_var("PAPER_ABSTRACT", String2HTML($paper->abstract,$html));
  $tpl->set_var("PAPER_EMAIL_CONTACT", $paper->emailContact);
  //  $tpl->set_var("PAPER_WEIGHT", $paper->assignmentWeight);
  $tpl->set_var("PAPER_TOPIC", String2HTML(LibelleCodif("ResearchTopic",
					    $paper->topic, $db), $html));
  $tpl->set_var ("PAPER_OTHER_TOPICS",
		 GetPaperTopics ($paper->id, $db, "string"));

  $tpl->set_var("FILE_NAME", 
		FNamePaper($config['uploadDir'], $paper->id, $paper->format));
  $tpl->set_var("CR_NAME", 
		CRNamePaper($config['uploadDir'], $paper->status,
			    $paper->id, $paper->format));
  $tpl->set_var("PAPER_NB_REVIEWERS", CountReviewers ($paper->id, $db));
  $tpl->set_var("PAPER_FILE_SIZE", $paper->fileSize);
  $tpl->set_var("PAPER_ID_CONF_SESSION", $paper->id_conf_session);
  $tpl->set_var("PAPER_POSITION_IN_SESSION", $paper->position_in_session);
}

/***************** INSTANCIATION OF TEMPLATES VARIABLES ************/

function InstanciateMemberVars ($member, &$tpl, $db, $html=true)
{
  // Instanciate template variables related to a PC member
  $config = GetConfig($db);
  $tpl->set_var("MEMBER_EMAIL", $member->email);
  $tpl->set_var("MEMBER_FIRST_NAME", String2HTML($member->firstName, $html));
  $tpl->set_var("MEMBER_LAST_NAME", String2HTML($member->lastName, $html));
  $tpl->set_var("MEMBER_NAME", 
		String2HTML($member->firstName . " " . $member->lastName, 
			    $html));
  $tpl->set_var("MEMBER_PASSWORD", 
		PWDMember($member->email, 
			  $config['passwordGenerator']));
  $tpl->set_var("MEMBER_ROLES",$member->roles); 
  // Get the preferred topics
  $r_topics = 
    $db->execRequete ("SELECT label "
		      . " FROM SelectedTopic s, ResearchTopic r "
		      . "WHERE email='$member->email' AND id=idTopic");
  $m_topics = ""; $comma= "";
  while ($topic = $db->objetSuivant($r_topics)) {
    $m_topics .= $comma . " " . $topic->label; $comma=",";
  }    
  $tpl->set_var ("MEMBER_TOPICS", $m_topics);
}

function InstantiatePersonVars ($person, &$tpl, $db)
{
  // Instanciate template variables related to a person/attendee
  $tpl->set_var("PERSON_ID", $person->id);
  $tpl->set_var("PERSON_EMAIL", $person->email);
  $tpl->set_var("PERSON_FIRST_NAME", $person->first_name);
  $tpl->set_var("PERSON_LAST_NAME", $person->last_name);
  $tpl->set_var("PERSON_REQUIREMENTS", $person->requirements);
  $tpl->set_var("PERSON_POSITION", $person->position);
  $tpl->set_var("PERSON_PHONE", $person->phone);
  $tpl->set_var("PERSON_FAX", $person->fax);
  $tpl->set_var("PERSON_AFFILIATION", $person->affiliation);
  $tpl->set_var("PERSON_ADDRESS", $person->address);
  $tpl->set_var("PERSON_CITY", $person->city);
  $tpl->set_var("PERSON_COUNTRY", $person->country);
  $tpl->set_var("PERSON_ZIP_CODE", $person->zip_code);
  $tpl->set_var("PERSON_PAYMENT_RECEIVED", $person->payment_received);

  $payment_modes = GetCodeList ("PaymentMode", 
				  $db, "id", "mode");
  $tpl->set_var("PERSON_PAYMENT_MODE", 
		$payment_modes[$person->payment_mode]);
}

function InstanciateConfigVars ($config, &$tpl)
{
  global $FILE_TYPES, $CODES;

  $yesNo = array ('Y'  => 'Yes', 'N' => 'No');

  // Instanciate template variables
  $tpl->set_var("CONF_URL", $config['confURL']);
  $tpl->set_var("CONF_ACRONYM", $config['confAcronym']);
  $tpl->set_var("CONF_NAME", $config['confName']);
  $tpl->set_var("CONF_MAIL", $config['confMail']);
  $tpl->set_var("CONF_CURRENCY", $config['currency']);
  $tpl->set_var("CONF_DATE_FORMAT", $config['date_format']);
  $tpl->set_var("CONF_PAYPAL_ACCOUNT", $config['paypal_account']);
  $tpl->set_var("CONF_UPLOAD_DIR", $config['uploadDir']);
  $tpl->set_var("CONF_PASSWORD_GENERATOR", $config['passwordGenerator']);
  $tpl->set_var("CONF_CHAIR_MAIL", $config['chairMail']);
  $tpl->set_var("SHOW_SUBMISSION_DEADLINE", 
		DBtoDisplay($config['submissionDeadline'],
			    $config['date_format']));

  $tpl->set_var("SHOW_CR_DEADLINE",
                DBtoDisplay($config['cameraReadyDeadline'],
                            $config['date_format']));

  $tpl->set_var("CONF_REVIEW_DEADLINE", 
		DateField ("reviewDeadline",
			   "", 
			   $config['reviewDeadline'],
			   $CODES));
  $tpl->set_var("CONF_SUBMISSION_DEADLINE", 
		DateField ("submissionDeadline",
			   "", $config['submissionDeadline'],
			   $CODES));
  $tpl->set_var("CONF_CAMERA_READY_DEADLINE", 
		DateField ("cameraReadyDeadline",
			   "", $config['cameraReadyDeadline'],
			   $CODES));
  
  $tpl->set_var("CONF_NB_REV_PER_PAPER",$config['nbReviewersPerItem']);

  $currentTypes = explode(';', $config['fileTypes']);
  foreach ($currentTypes as $key => $val)  $defArray[$val] = $val;
  $tpl->set_var("LIST_FILE_TYPES",
		CheckBoxFields ('fileTypes[]', $FILE_TYPES, $defArray));
  $tpl->set_var("LIST_BLIND_REVIEW",
		RadioFields ('blind_review', $yesNo, $config['blind_review']));
  $tpl->set_var("LIST_MULTI_TOPICS",
		RadioFields ('multi_topics', $yesNo, $config['multi_topics']));
  $tpl->set_var("LIST_TWO_PHASES_SUBMISSION",
		RadioFields ('two_phases_submission', 
			     $yesNo, $config['two_phases_submission']));
  $tpl->set_var("LIST_EXTENDED_SUBMISSION_FORM",
		RadioFields ('extended_submission_form', 
			     $yesNo, $config['extended_submission_form']));
  $tpl->set_var("LIST_ABSTRACT_SUBMISSION_OPEN",
		RadioFields ('isAbstractSubmissionOpen', 
			     $yesNo, $config['isAbstractSubmissionOpen']));
  $tpl->set_var("LIST_PAPER_SUBMISSION_OPEN",
		RadioFields ('isSubmissionOpen', 
			     $yesNo, $config['isSubmissionOpen']));
  $tpl->set_var("LIST_CAMERA_READY_SUBMISSION_OPEN",
		RadioFields ('isCameraReadyOpen', 
			     $yesNo, $config['isCameraReadyOpen']));
  $tpl->set_var("LIST_DISCUSSION_MODE",
		RadioFields ('discussion_mode', 
			     $CODES->get("discussion_mode"),
			     $config['discussion_mode']));
  $tpl->set_var("LIST_BALLOT_MODE",
		RadioFields ('ballot_mode', 
			     $CODES->get("ballot_mode"),
			     $config['ballot_mode']));
  $tpl->set_var("SEND_ON_ABSTRACT",
		RadioFields ('mailOnAbstract', $yesNo, 
			     $config['mailOnAbstract']));
  $tpl->set_var("SEND_ON_UPLOAD",
		RadioFields ('mailOnUpload', $yesNo, 
			     $config['mailOnUpload']));
  $tpl->set_var("SEND_ON_REVIEW",
		RadioFields ('mailOnReview', $yesNo, 
			     $config['mailOnReview']));

}

/************************ MAILS ***************************************/

function InstanciateMailReviewer ($template,
				  $config, $member, &$tpl, $db)
{
  // Instanciate template variables in a reviewer mail
  $tpl->set_var("NUMBER_OF_PAPERS", CountAllPapers($db));
  $tpl->set_var("NAME_REVIEWER", $member['firstName'] . " " 
		. $member['lastName']);
  $tpl->set_var("EMAIL_REVIEWER", $member['email']);
  $tpl->set_var("CONF_URL", $config['confURL']);
  $tpl->set_var("CONF_ACRONYM", $config['confAcronym']);
  $tpl->set_var("PASSWORD_REVIEWER", 
		PWDMember($member['email'], $config['passwordGenerator']));
  $tpl->set_var("REVIEW_DEADLINE", 
		DBtoDisplay($config['reviewDeadline'], $config['date_format']));
  $tpl->parse("MESSAGE", $template);
  return $tpl->get_var("MESSAGE");
}

function InstanciateMailAuthor ($config, $paper, &$tpl, $db)
{
  // Instanciate all the variables
  InstanciateConfigVars($config, $tpl);
  InstanciatePaperVars($paper, $tpl, $db);
  
  // Create the status message for authors
  $query = "SELECT * FROM PaperStatus WHERE id='$paper->status'";

  $status = GetRow ($query, $db, "object");
  if (is_object($status))
    {
      if (!file_exists(TPLDIR . $status->mailTemplate))
	FatalError (sprintf (FE_FILE_MISSING, $status->mailTemplate));

      $tpl->set_file($status->mailTemplate, TPLDIR . $status->mailTemplate);
      $tpl->parse ("STATUS", $status->mailTemplate);
    }
  else
    FatalError (sprintf (FE_NO_STATUS, $paper->status));

  $message = $tpl->get_var("STATUS");
  $reviews = DisplayReviews ($paper->id, "TxtShowAuthorsReview", 
			     $tpl, $db);

  return $message . $reviews;
}

// Function to commit the automatic assignment computation
function CommitAssignment($idMin, $idMax, $db)
{
  // Delete existing reviews and existing review marks
  if ($idMin == -1)
    {
      // Remove all current assignment
      $qCleanReview="DELETE FROM Review WHERE submission_date IS NULL";
    }
  else
    {
      // Remove all asignments of the current group
      $qCleanReview="DELETE FROM Review "
	. "WHERE idPaper BETWEEN $idMin AND $idMax AND submission_date IS NULL";
    }
  $db->execRequete($qCleanReview);
  
  // Loop on papers
  $qPapers = "SELECT * FROM Paper ORDER BY id";
  $rPapers = $db->execRequete($qPapers);
  while ($paper = $db->objetSuivant($rPapers))
    {
      // Take all the reviewers assigned to the paper
      $qAssignment = "SELECT * FROM Assignment WHERE idPaper='$paper->id'";
      $rAssignment = $db->execRequete($qAssignment);
      $tabMails = array();
      $weightPaper = 0;
      while ($assignment = $db->objetSuivant($rAssignment))
	{
	  $tabMails[] = $assignment->email;
	  $weightPaper += $assignment->weight;
	}
      // Insert the assignment in the Review table
      SQLReview ($paper->id, $tabMails, $db);

      // Update the weight of the paper *** difficult to maintain
      // $qUpdPaper = "UPDATE Paper SET assignmentWeight='$weightPaper' "
      // . "WHERE id='$paper->id'";
      // $db->execRequete ($qUpdPaper);
    }
}

// Recursive function to display messages
function DisplayMessages ($idPaper, $idParent, $db, 
			  $answer=true, $target="Review.php")
{
  $qMessages = "SELECT * FROM Message "
    . "WHERE idPaper='$idPaper' AND idParent='$idParent'";
  
  // Open the list
  $messages = "<ol>\n";
  $result = $db->execRequete ($qMessages);
  while ($message = $db->objetSuivant ($result))
    {
      if ($idParent == 0 or $idParent == "")
	$typeMess = "Message";
      else
	$typeMess = "Answer";
	
      $messages .= "<li><b>From</b> $message->emailReviewer,  <b>Date</b>:"
	. "$message->date:\n"
	. "<b>$typeMess</b>: " . nl2br($message->message);
      
      if ($answer)
	$messages .= "<a href='$target?newMessage=1&idPaper="
	  . "$idPaper&idParent=$message->idMessage'>"
	  . " (Answer)</a></li>\n";

      // Recursive call for descendant messages
      $messages .= DisplayMessages ($idPaper, $message->idMessage, 
				    $db, $answer, $target);
    }
  // Close the list
  $messages .= "</ol>\n";
  return $messages;
}

// Function that marks all the papers in the current selection
function GetCurrentSelection ($db)
{
  // Initialize the current selection
  $qCS = "UPDATE Paper SET inCurrentSelection='N'";
  $db->execRequete ($qCS);

  // crWhere : applies to selection on paper
  // crJoin  : applies to outer join Paper Review

  $crJoin = $crWhere = " 1 = 1 ";
  if (isSet($_POST['paperQuestions'])) 
    $paperQuestions = $_POST['paperQuestions'];
  else
    $paperQuestions = array();

  if (isSet($_POST['reviewQuestions'])) 
    $reviewQuestions = $_POST['reviewQuestions'];
  else
    $reviewQuestions = array();

  // Take into account the filtering criterias.
  $config = GetConfig ($db);
  if ($config['papersWithStatus'] == SP_ANY_STATUS)
    $crWhere .= "";
  else if ($config['papersWithStatus'] == SP_NOT_YET_ASSIGNED)
    $crWhere .= " AND status IS NULL ";
  else 
    $crWhere .= " AND status='" . $config['papersWithStatus'] . "'";

  // Full text search on titles?
  if ($config['papersWithTitle'] != "Any")
    $crWhere .= " AND title LIKE '%" . $config['papersWithTitle'] . "%' ";

  // Show uploaded or not yet uploaded papers?
  if ($config['papersUploaded'] == "Y") $crWhere .= " AND isUploaded = 'Y' ";
  if ($config['papersUploaded'] == "N") $crWhere .= " AND isUploaded = 'N' ";

  // Show papers for a specific reviewer?
  if ($config['papersWithReviewer'] != "All")
    $crWhere .= " AND email='" . $config['papersWithReviewer'] . "' ";

  // Sort the papers on the average 'overall' field
  $query = "SELECT p.* FROM Paper p LEFT JOIN Review r ON p.id=r.idPaper"
    .  " WHERE $crWhere ";

  $res = $db->execRequete ($query);
  while ($paper = $db->objetSuivant($res))
    {
      $keep = $keep2 = $keep3 = $keep4 = $keep5 = false;
      $keep6 = $keep7 = true;

      // Should we consider conflicts?
      if ($config['papersWithConflict'] == "Y"
	  and PaperInConflict($paper->id,$db))
	{
	  $keep = true;
	}
      else if ($config['papersWithConflict'] == "N"
	       and !PaperInConflict($paper->id,$db))
	{
	  $keep = true;
	}
      else if ($config['papersWithConflict'] == "A")
	$keep = true;

      // Should we consider missing review?
      if ($config['papersWithMissingReview'] == "Y"
	  and MissingReview($paper->id,$db)) 
	$keep2 = true;
      else if ($config['papersWithMissingReview'] == "N"
	       and !MissingReview($paper->id,$db))
	$keep2 = true;
      else if ($config['papersWithMissingReview'] == "A")
	$keep2 = true;

      // Take care of the filter
      $overall = Overall ($paper->id, $db);
      if ($config['papersWithFilter'] == SP_ABOVE_FILTER
	  and $overall >= $config['papersWithRate'])
	$keep3 = true;
      else if ($config['papersWithFilter'] == SP_BELOW_FILTER
	       and $overall <= $config['papersWithRate'])
	$keep3 = true;
      else if ($config['papersWithFilter'] == 0)
	$keep3 = true;

      // Take care of authors
      if ($config['papersWithAuthor'] != "Any"
	  and !empty($config['papersWithAuthor']))
	{
	  $authors = GetAuthors($paper->id, $db, false,
				"string", $paper->authors);
	  if (strpos($authors,  $config['papersWithAuthor']))
	    $keep4 = true;
	}
      else $keep4 = true;

      // Show papers for a specific topic?
      if ($config['papersWithTopic'] != 0)
	{
	  $the_topic = $config['papersWithTopic'];
	  if ($paper->topic == $the_topic)
	    $keep5 = true; // OK with the main topic
	  else {
	    $rt = $db->execRequete 
	      ("SELECT * FROM PaperTopic WHERE id_paper='$paper->id' "
	       . " AND id_topic='$the_topic'");
	    if ($db->objetSuivant($rt))
	      $keep5 = true; // found in other topics
	    else
	      $keep5 = false;
	  }
	}
      else $keep5 = true;

      // Now look at the paper question
      reset ($paperQuestions);
      foreach ($paperQuestions as $id_question => $id_choice) {
	if ($id_choice != SP_ANY_CHOICE) {
	  // Check that the paper's answer matches the choice
	  $rq = $db->execRequete 
	    ("SELECT * FROM PaperAnswer WHERE id_paper='$paper->id' "
	     . "AND id_question='$id_question' AND id_answer='$id_choice'");
	  if (!is_object($db->objetSuivant($rq)))
	    { $keep6 = false; break; } // No need to look further
	}
      }

      // Now look at the review question
      reset ($reviewQuestions);
      foreach ($reviewQuestions as $id_question => $id_choice) {
	if ($id_choice != SP_ANY_CHOICE) {
	  // Check that some reviewer's answer matches the choice
	  $rq = $db->execRequete 
	    ("SELECT * FROM Review r, ReviewAnswer a "
	     . " WHERE id_paper='$paper->id' AND r.email=a.email "
	     . "AND id_question='$id_question' AND id_answer='$id_choice'");
	  if (!is_object($db->objetSuivant($rq)))
	    { $keep7 = false; break; } // No need to look further
	}
      }

      if ($keep and $keep2 and $keep3 and $keep4 and $keep5 
	  and $keep6 and $keep7)
	{
	  $qCS = "UPDATE Paper SET inCurrentSelection='Y' WHERE id=$paper->id";
	  $db->execRequete($qCS);
	}
    }
}

/****************************** DATES ********************************/

/*
function date_format($date, $format="d/m/Y")
{
  return date ($format, $date);
}
*/

function DBtoDisplay($date, $date_format){
  $tab=explode('-',$date);
  return date ($date_format, mktime (0, 0, 0, $tab[1], $tab[2], $tab[0]));
}

function DisplaytoDB($date){
  $tab=explode('/',$date);
  return $tab[2]."-".$tab[1]."-".$tab[0];
}

function is_num($str){
  return preg_match("/^\d+$/",$str);
}

function isCorrectOrder($date1,$date2){
  return (strtotime(DisplaytoDB($date1))<=strtotime(DisplaytoDB($date2)));
}

function isCorrectDate($date){
  /* controle de la longueur de la chaine jj/mm/aaaa = 10 */
  if(strlen($date)==10){
    if(substr($date,2,1)=="/" && substr($date,5,1)=="/"){
      /* les caract?res 1 et 6 sont des " / "  */
      if (is_num(substr($date,0,2)) && is_num(substr($date,3,2)) 
	  && is_num(substr($date,6,4))) {
	$jour=intval(substr($date,0,2)); /* PHP num?rote les chaines depuis 0 */
	$mois=intval(substr($date,3,2));
	$annee=intval(substr($date,6,4));
	if($mois>=1 && $mois<=12){  /* verifie que le mois verifie 1<mois<12 */
	  if($jour<=longueurMois($mois,$annee)){ /* controle le jour par */
	    return true;                        /* rapport a la longueur du mois */
	  }    
	  else {
	    return false;
	  }
	}
	else {
	  return false;
	}
      }
      else {
	return false;
      }
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
}


function longueurMois($mois,$annee){
  if ($mois==4 || $mois==6 || $mois==9 || $mois==11) return 30;
  else if (($mois==2) && estBissextile($annee)) return 29;
  else if ($mois==2) return 28;
  else return 31;
}

function estBissextile($ans){
  if ((($ans % 4 == 0) && $ans % 100 != 0) || $ans % 400 == 0)
    return true;/*c'est une ann?e bissextile */
  else
    return false;/*ce n'en est pas une */
}


function UploadError($file, &$MESSAGES)
{
  // Get the error code (only available in PHP 4.2 and later)
  if (isSet($file['error']))
    {
      // Ok the code exists
      switch ($file['error']) 
	{
	case UPLOAD_ERR_NO_FILE:
	  return $MESSAGES->get("MISSING_FILE");
	  break;
	  
	case UPLOAD_ERR_INI_SIZE: 	case UPLOAD_ERR_FORM_SIZE:
	  return $MESSAGES->get("FILE_TOO_LARGE");
	  break;
	  
	case UPLOAD_ERR_PARTIAL:
	  return $MESSAGES->get("PARTIAL_UPLOAD");
	  break;
      
	default:
	  return "Unknown upload error";
	}
    }
  else
    {
      // No way to know what is going on
      return "Unable to upload the file";
    }
}

// The following function strips slashes from
// an HTTP input. Note: parameter is passed by reference

function NormaliseHTTP(&$arr)
{
  // Scan the array
  foreach ($arr as $key => $value) 
    {
      if (!is_array($value)) // Let's go
	{
	  $arr[$key] = stripSlashes($value);
	}
      else  // Recursive call.
	{
	  NormaliseHTTP($arr[$key]);
	}
    }
  reset($arr);
}

// The following function trims (removal of white spaces
// from the beginning and end of a string) all the elements of an array
// Useful for cleaning HTTP inputs
function TrimArray(&$arr)
{
  // Scan the array
  foreach ($arr as $key => $value) 
    {
      if (!is_array($value)) // Let's go
	$arr[$key] = trim($value);
      else  // Recursive call
	TrimArray($arr[$key]);
    }
}

// The following function prepares a string for HTML output.
// All special characters are replaced by their entity ref.,
// and the newlines are converted to <br>
function String2HTML ($str, $html=true)
{
  if ($html)
    return nl2br(htmlSpecialChars($str));
  else
    return $str;
}

/****************************** TEST CONFIG FOR GRAPHS ********************************/

function graphsTestConfig()
{
  if (function_exists("gd_info"))
    {
      $gdInfos = gd_info();
      if (($gdInfos["PNG Support"]==1)
	  &&($gdInfos["JPG Support"]==1)) return true;
      else  return false;
    }
  else {
    return false;
  }

  return false;
}

// Select list for HTML Forms
function  SelectField ($nom, $liste, $defaut, $taille=1)
  {
    $s = "<SELECT NAME=\"$nom\" SIZE='$taille'>\n";
    while (list ($val, $libelle) = each ($liste))
      {
	// Attention aux probl?mes d'affichage
	$val = htmlSpecialChars($val);
	$defaut = htmlSpecialChars($defaut);
	
        if ($val != $defaut)
	  $s .=  "<OPTION VALUE=\"$val\">$libelle</OPTION>\n";
        else
	  $s .= "<OPTION VALUE=\"$val\" SELECTED>$libelle</OPTION>\n";
      }
    return $s . "</SELECT>\n";
  }

// Get a code list
function GetCodeList ($tableName, $db, $id="id", $name="name",
		      $output=array()) 
{
  $res = $output;
  $result = $db->execRequete ("SELECT $id, $name FROM $tableName");
  while ($cursor = $db->ligneSuivante ($result))
    {
    $res[$cursor[$id]] = $cursor[$name];
    }
  return $res;
}

// Options list for HTML Forms
function  OptionsList ($liste, $defaut)
  {
    $s = "";
    while (list ($val, $libelle) = each ($liste))
      {
	// Attention aux probl?mes d'affichage
	$val = htmlSpecialChars($val);
	$defaut = htmlSpecialChars($defaut);

        if ($val != $defaut)
	  $s .=  "<OPTION VALUE=\"$val\">$libelle</OPTION>\n";
        else
	  $s .= "<OPTION VALUE=\"$val\" SELECTED>$libelle</OPTION>\n";
      }
    return $s;
  }

// Radio list HTML Forms
function  RadioFields ($nom, $liste, $defaut)
  {
    // Always dispay in a table
    $libelles=$champs="";
    $nbChoix = 0;
    $result = "<TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2>\n"; 
    while (list ($val, $libelle) = each ($liste))
      {
	$libelles .= "<TD><B>$libelle</B></TD>";
	$checked = " ";
	if ($val == $defaut) $checked = "CHECKED";

	$champs .= "<TD><INPUT TYPE='RADIO' "
	  . "NAME=\"$nom\" VALUE=\"$val\" $checked> </TD>\n";
      }

    if (!empty($champs))
      return  $result . "<TR>" . $libelles .  "</TR>\n"
	 . "<TR>" . $champs . "</TR></TABLE>";
    else return $result . "</TABLE>";
  }

// Checkbox list HTML Forms
function  CheckBoxFields ($nom, $liste, $listChecked)
  {
    // Always dispay in a table
    $libelles=$champs="";
    $nbChoix = 0;
    $result = "<TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2>\n"; 
    while (list ($val, $libelle) = each ($liste))
      {
	$libelles .= "<TD><B>$libelle</B></TD>";
	$checked = " ";
	if (array_key_exists ($val, $listChecked)) 
              $checked = "CHECKED";

	$champs .= "<TD><INPUT TYPE='CHECKBOX' "
	  . "NAME=\"$nom\" VALUE=\"$val\" $checked> </TD>\n";
      }

    if (!empty($champs))
      return  $result . "<TR>" . $libelles .  "</TR>\n"
	 . "<TR>" . $champs . "</TR></TABLE>";
    else return $result . "</TABLE>";
  }

// Encode the array of questions choices for the current selection
function encodeQuestions ($field_name)
{
  $questions = ""; $sep="";

  if (isSet($_POST[$field_name])) 
    $arr_questions = $_POST[$field_name];
  else
    $arr_questions = array();

  foreach ($arr_questions as $id_question => $id_choice)
    { $questions .= "$sep $id_question,$id_choice"; $sep=";"; }
  return $questions;
}

function ShowInvoice ($person, $db, &$tpl, $template)
{
  // Show the invoice
  $tpl->set_file ($template, TPLDIR . $template);
  $tpl->set_block ($template, "ROW_CHOICE", "ROWS_CHOICES");
  $tpl->set_block ($template, "PAYPAL_PAYMENT");
  $tpl->set_block ($template, "OTHER_PAYMENT");
  $tpl->set_block ("PAYPAL_PAYMENT", "PAYPAL_ITEM", "PAYPAL_ITEMS");
  $tpl->set_var ("ROWS_CHOICES", "");
  $tpl->set_var ("PAYPAL_ITEMS", "");

  InstantiatePersonVars ($person, $tpl, $db);

  $config = GetConfig ($db);
  InstanciateConfigVars ($config, $tpl);
  $tpl->set_var ("PAYPAL_BUSINESS", $config["paypal_account"]);
  $tpl->set_var ("PAYPAL_CURRENCY", $config["currency"]);
  $tpl->set_var ("REGISTRATION_ID", $person->id);

  $total = 0.;

  $q_invoice = "SELECT c.id_question, question, choice, cost "
    . " FROM RegQuestion q, RegChoice c, PersonChoice p "
    . " WHERE q.id=p.id_question AND c.id_question=p.id_question "
    . " AND c.id_choice=p.id_choice AND p.id_person='$person->id'";
  $r_invoice = $db->execRequete ($q_invoice);
  while ($l_invoice = $db->objetSuivant($r_invoice)) {
    $tpl->set_var("REG_QUESTION", $l_invoice->question);
    $tpl->set_var("REG_CHOICE", $l_invoice->choice);
    $tpl->set_var("REG_COST", $l_invoice->cost);

    $tpl->set_var("ITEM_NAME", $l_invoice->question . " - "
		  . $l_invoice->choice);
    $tpl->set_var("ITEM_ID", $l_invoice->id_question);
    $tpl->set_var("ITEM_AMOUNT", $l_invoice->cost);

    $total += $l_invoice->cost;
    $tpl->parse("ROWS_CHOICES", "ROW_CHOICE", true);
    $tpl->parse("PAYPAL_ITEMS", "PAYPAL_ITEM", true);
  }

  $tpl->set_var ("TOTAL_COST", $total);

  if ($person->payment_mode != PAYPAL)
    $tpl->set_var("PAYPAL_PAYMENT", "");
  else
    $tpl->set_var("OTHER_PAYMENT", "");

  $tpl->parse ("RESULT", $template);
  return $tpl->get_var("RESULT");
}

// Date input
function DateField ($field_name, $field_seed, $default, &$codes)
{
  // Decode the default date
  $day = substr ($default, 8, 2);
  $month = substr ($default, 5, 2);
  $year = substr ($default, 0, 4);

  // Create the lists
  $cur_year = Date("Y");
  for ($d = 1; $d <= 31; $d++) $list_days[$d] = $d;
  for ($y = $cur_year - 1; $y <= $cur_year + 5; $y++) 
    $list_years[$y] = $y;
  $list_months = $codes->get("months");

  // Create the select fields
  $day_field = SelectField ($field_name . "[" . $field_seed . "_day]",
			    $list_days, $day);
  $month_field = SelectField ($field_name . "[" . $field_seed . "_month]",
			    $list_months, $month);
  $year_field = SelectField ($field_name . "[" . $field_seed . "_year]",
			    $list_years, $year);

  return $day_field . $month_field . $year_field;
}

//adyilie-added to support better downloads
function readfile_chunked ($filename) { 
  $chunksize = 1*(1024*1024); // how many bytes per chunk 
  $buffer = ''; 
  $handle = fopen($filename, 'rb'); 
  if ($handle === false) { 
    return false; 
  } 
  while (!feof($handle)) { 
    $buffer = fread($handle, $chunksize); 
    print $buffer; 
  } 
  return fclose($handle); 
} 

//adyilie-  Get the post data from the param file (tmp_sid.param)
function getPostData($up_dir, $tmp_sid){
  $param_array = array();
  $paramFileName = $up_dir . $tmp_sid . ".params";
  $fh = fopen($paramFileName, 'r') or 
    die("<br><center>Failed to open parameter file $paramFileName</center>.\n");
  
  while(!feof($fh)){
    $buffer = fgets($fh, 4096);
    parse_str(trim($buffer),$param_array);
  }
  
  fclose($fh);
  unlink($paramFileName);
  
  $value = $param_array["abstract"];
  if (is_string($value)) {
    $new_value = str_replace("|","\n",$value);
    $new_value = str_replace("\n\n","\n",$new_value);
    $param_array["abstract"]=$new_value;
  }
  return $param_array;
}

?>
