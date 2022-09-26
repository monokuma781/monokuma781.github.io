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
 
 
// Insert or update a paper
function SQLPaper ($paper, $file, $mode, $db)
{  
  // Get the variables, and escape quotes
  $title = $db->prepareString($paper['title']);  
  $authors = $db->prepareString($paper['authors']);
  $emailContact = $db->prepareString($paper['emailContact']);
  $abstract = $db->prepareString($paper['abstract']);
  $format = $paper['format'];
  $topic = $paper['topic'];
  $now = date('U'); // Unix time

  if ($mode == INSERTION)  
    {
      // Insert
      $query = "INSERT INTO Paper (title, authors, emailContact, "
	.   "abstract, format, topic, submission_date) "
	. "VALUES ('$title', '$authors', '$emailContact', '$abstract', "
	. "'$format', '$topic', $now) ";

      $db->execRequete ($query);
      $id = $db->idDerniereLigne();
    }
  else  
    {
      $id = $paper['id'];

      // Update
      $query = "UPDATE Paper SET title='$title', authors='$authors',  "
        . "emailContact='$emailContact',abstract='$abstract',topic='$topic' " 
	. " WHERE id='$id'";
      $db->execRequete ($query);
    }

  // Insert/update the authors if necessary
  if (isSet($paper['last_name']))
    {
      // Remove all current authors, and replace
      $qDel = "DELETE FROM Author WHERE id_paper='$id'";
      $db->execRequete($qDel);
      // Insert all authors
      $authors = $paper['last_name']; 
      for ($i=0; $i < count($authors); $i++) 
	{
	  if (!empty($authors[$i]))	
	    {   
	      $first_name = $db->prepareString($paper['first_name'][$i]);
	      $last_name = $db->prepareString($paper['last_name'][$i]);
	      $affiliation = $db->prepareString($paper['affiliation'][$i]);
	      SQLAuthor ($id, $i+1, $first_name, $last_name,
			 $affiliation, $db);
	    }
	}
    }

  // Insert/update the answers to questions
  if (isSet($paper['questions']))
    {
      // Remove all current answers, and replace
      $db->execRequete("DELETE FROM PaperAnswer WHERE id_paper='$id'");

      // Insert all answers
      $questions = $paper['questions']; 
      foreach  ($questions as $id_question => $id_answer)
	  $db->execRequete ("INSERT INTO PaperAnswer "
			    . "VALUES ('$id', '$id_question', '$id_answer')");
    }


  // Insert/update the topics if necessary
  if (isSet($paper['topics']))
    {
      // Remove all current topics, and replace
      $qDel = "DELETE FROM PaperTopic WHERE id_paper='$id'";
      $db->execRequete($qDel);
      // Insert all topics
      $topics = $paper['topics']; 
      foreach  ($topics as $topic)
	  $db->execRequete ("INSERT INTO PaperTopic "
			    . "VALUES ('$id', '$topic')");
    }

  // Upload the file if any
  //  if (is_uploaded_file ($file['tmp_name']))//adyilie-commented out
  if (isSet($file['tmp_name'])&&(strcmp($file['tmp_name'],'none')!=0)) {
    //adyilie-changed
    $config = GetConfig ($db, "object");
    if ($config->isCameraReadyOpen=='Y') {
      // Camera-ready phase
      $paper = GetPaper ($id, $db, "object");
      $status=$paper->status;
      $crNum=$paper->CR;
      $crNum++;
      if (StoreCRPaper  ($status, $id, $file, $format, $db)) {
	$fileSize = $_FILES['file']['size'];	      
	$qUpdPaper = 
	  "UPDATE Paper SET CR='$crNum', fileSize='$fileSize', "
	  . "format='$format' WHERE id='$id'";
	$db->execRequete ($qUpdPaper);
      }
      else
	return "Unable to store the file<br>";
    }
    else {
      // Paper submission phase
      if (StorePaper  ($id, $file, $format, $db)) {
	$fileSize = $_FILES['file']['size'];
	$qUpdPaper = 
	  "UPDATE Paper SET isUploaded='Y', fileSize='$fileSize', "
	  . "format='$format' WHERE id='$id'";
	$db->execRequete ($qUpdPaper);
      }
      else
	return "Unable to store the file<br>";
    }
  }
  return $id;
}

// Insert or update a PC member
function SQLMember ($member, $mode, $db)
{  
  // Get the variables and prepare for insertion
  $email = $db->prepareString($member['email']);
  $firstName = $db->prepareString($member['firstName']);  
  $lastName = $db->prepareString($member['lastName']);
  $affiliation = $db->prepareString($member['affiliation']);
  $roles = "";
  if (is_array($member['roles']))
    $roles = implode (",", $member['roles']);
      
  if ($mode == INSERTION)  
    {
      // Insert
      $query = "INSERT INTO PCMember (firstName, lastName, email, "
	. "affiliation, roles) "
	. "VALUES ('$firstName', '$lastName', '$email', "
	. "'$affiliation', '$roles') ";
      $db->execRequete ($query);
    }
  else  
    {
      // Update
      $query = "UPDATE PCMember SET firstName='$firstName', "
	. "lastName='$lastName',affiliation='$affiliation', " 
	. " roles='$roles' WHERE email='$email'";
      $db->execRequete ($query);
    }
}

// Insert or update a PC member
function SQLAuthor ($id_paper, $pos, $first_name, 
		    $last_name, $affiliation, $db)
{  
  // Insert
  $query = "INSERT INTO Author(id_paper, position, first_name, "
    . "last_name,affiliation) VALUES ('$id_paper', '$pos', "
    . "'$first_name', '$last_name', '$affiliation') ";
  $db->execRequete ($query);
}

// Insert reviewers
function SQLReview ($idPaper, $tabMails, $db)
{  
  foreach ($tabMails as $id => $email)
    {
      // Never delete! Just insert 
      if ($email != NOBODY)
	if (!GetReview($idPaper, $email, $db))
	  {
	    $query = "INSERT INTO Review (idPaper, email)"
	      . "VALUES ('$idPaper', '$email')";
	    $db->execRequete ($query);
	  }
	else	      // The review exists: do nothing
	  ;
    }
}

// Update review
function SQLUpdateReview ($review, $db)
{  
  $idPaper = $review['idPaper'];
  $email = $review['email'];

  $reviewerExpertise = $review['reviewerExpertise'];
  $summary = $db->prepareString($review['summary']);
  $details = $db->prepareString($review['details']);
  $comments = $db->prepareString($review['comments']);
  $lname_ext = $db->prepareString($review['lname_ext_reviewer']);
  $fname_ext = $db->prepareString($review['fname_ext_reviewer']);

  $query = "UPDATE Review SET " 
    . "reviewerExpertise='$reviewerExpertise', summary='$summary', "
    . "details='$details', comments='$comments', " 
    . "lname_ext_reviewer='$lname_ext', " 
    . "fname_ext_reviewer='$fname_ext' " 
    . "WHERE idPaper=$idPaper and  email='$email'";
  $db->execRequete ($query);

  // Set the dates
  $now = date("U");
  $db_review = GetReview($idPaper, $email, $db);
  if (empty($db_review['submission_date']))
    {
      // Set the submission_date
      $query = "UPDATE Review SET submission_date=$now" 
	. " WHERE idPaper=$idPaper and  email='$email'";
    }
  else
    {
      // Set the last revision date
      $query = "UPDATE Review SET last_revision_date=$now" 
	. " WHERE idPaper=$idPaper and  email='$email'";
    }
  $db->execRequete ($query);

  // Store the marks (and compute the sum of weights)
  $listC = GetListCriterias ($db);
  $totalWeight = 0;
  $weightedMarks = 0;
  foreach ($listC as $id => $crVals)
    {
      $weight = $crVals['weight'];
      $totalWeight += $weight;
      $weightedMarks +=  $weight * $review[$id];
      SQLReviewMark ($idPaper, $email, $id, $review[$id], $db);
    }
  
  // Now compute the overall rate as a weighted average
  if ($totalWeight == 0)
    {
      echo "THE SUM OF WEIGHTS IS NULL. Unable to compute the overall mark<br>";
    }
  else
    {
      $overall = $weightedMarks / $totalWeight;
      $query = "UPDATE Review SET overall=round('$overall',4) " 
	. "WHERE idPaper=$idPaper and  email='$email'";
      $db->execRequete ($query);
    }

  // Insert/update the answers to questions
  if (isSet($review['questions']))
    {
      // Remove all current answers, and replace
      $db->execRequete("DELETE FROM ReviewAnswer "
		       . " WHERE id_paper='$idPaper' AND email='$email' ");

      // Insert all answers
      $questions = $review['questions']; 
      foreach  ($questions as $id_question => $id_answer)
	  $db->execRequete ("INSERT INTO ReviewAnswer (id_paper, email, "
			    . " id_question, id_answer) VALUES ('$idPaper', "
			    . " '$email', '$id_question', '$id_answer')");
    }

}

function UpdateConfig ($config, $db, &$tpl, &$session)
{
  $form_mess =  "";

  if ($_POST['nbReviewersPerItem']<=0) 
    $form_mess="Wrong nb reviewers per paper" . $form_mess;
  
  if (!is_dir($_POST['uploadDir'])) 
    $form_mess="Uploaded papers directory <b>".$_POST['uploadDir']
      ."</b> ".$messages['UPLOAD_DIR_EXIST']."<br>".$form_mess;
  if (!is_readable($_POST['uploadDir']))
    $form_mess="Uploaded papers directory <b>".$_POST['uploadDir']
      ."</b> ".$messages['UPLOAD_DIR_READ']."<br>".$form_mess;
  if (!@file_exists($_POST['uploadDir']."/."))
    $form_mess="Uploaded papers directory <b>".$_POST['uploadDir']
      ."</b> ".$messages['UPLOAD_DIR_EXE']."<br>".$form_mess;
  if (!is_writable($_POST['uploadDir'])) 
    $form_mess="Uploaded papers directory <b>".$_POST['uploadDir']
      ."</b> ".$messages['UPLOAD_DIR_WRITE']."<br>".$form_mess; 
     
  if (!CheckEMail($_POST['confMail'])) 
    $form_mess="<b>Conference mail is not valid</b><br>".$form_mess;
  if (!CheckEMail($_POST['chairMail'])) 
    $form_mess="<b>Chair mail is not valid</b><br>".$form_mess;
  
  $submission_deadline =  
    $_POST['submissionDeadline']['_day'] . "/"  .
    $_POST['submissionDeadline']['_month'] . "/" .
    $_POST['submissionDeadline']['_year'] ;
  $review_deadline =  
    $_POST['reviewDeadline']['_day'] . "/"  .
    $_POST['reviewDeadline']['_month'] . "/" .
    $_POST['reviewDeadline']['_year'] ;
  $cr_deadline =  
    $_POST['cameraReadyDeadline']['_day'] . "/"  .
    $_POST['cameraReadyDeadline']['_month'] . "/" .
    $_POST['cameraReadyDeadline']['_year'] ;
  
  if (!isCorrectOrder($submission_deadline,
		      $review_deadline)) 
    $form_mess="<b>Review deadline must follow the submission deadline</b>".$form_mess;
  if (!isCorrectOrder($review_deadline, $cr_deadline)) 
    $form_mess="<b>camera ready deadline must be after review deadline</b>".$form_mess;
  
  if ($form_mess != "") {
    return $form_mess;
  } 
  
  // Update config table
  $confName = $db->prepareString($config['confName']);
  $confAcronym = $db->prepareString($config['confAcronym']);
  $confMail = $config['confMail'];
  $currency = $config['currency'];
  $date_format = $config['date_format'];
  $paypal_account = $config['paypal_account'];
  $confURL = $config['confURL'];
  $chairMail = $config['chairMail'];
  
  $passwordGenerator = $db->prepareString($config['passwordGenerator']);
  $uploadDir = $config['uploadDir'];

  $fileTypes = implode ($config['fileTypes'], ';');
  $extended_submission_form = $config['extended_submission_form'];
  $two_phases_submission = $config['two_phases_submission'];
  $blind_review = $config['blind_review'];
  $multi_topics = $config['multi_topics'];
  $isAbstractSubmissionOpen = $config['isAbstractSubmissionOpen'];
  $isSubmissionOpen = $config['isSubmissionOpen'];
  $discussion_mode = $config['discussion_mode'];
  $ballot_mode = $config['ballot_mode'];
  $isCameraReadyOpen = $config['isCameraReadyOpen'];
  
  if (isSet($config['sizeOfBallot']))
    $sizeOfBallot = $config['sizeOfBallot'];
  else
    $sizeOfBallot = 99999;
  
  $nbReviewersPerItem = $config['nbReviewersPerItem'];
  
  $mailOnAbstract = $config['mailOnAbstract'];
  $mailOnUpload = $config['mailOnUpload'];
  $mailOnReview = $config['mailOnReview'];
  
  $submissionDeadline = DisplaytoDB($submission_deadline);
  $reviewDeadline = DisplaytoDB($review_deadline);
  $cameraReadyDeadline = DisplaytoDB($cr_deadline);

  $query = "UPDATE Config SET currency='$currency', confName='$confName', "
    . "paypal_account='$paypal_account', "
    . "confAcronym='$confAcronym', confMail='$confMail', confURL='$confURL',"
    . "passwordGenerator='$passwordGenerator', blind_review='$blind_review', "
    .  "multi_topics='$multi_topics', "
    .  "extended_submission_form='$extended_submission_form', "
    .  "two_phases_submission='$two_phases_submission', "
    . "isAbstractSubmissionOpen='$isAbstractSubmissionOpen', "
    . "isSubmissionOpen= '$isSubmissionOpen', "
    .  "uploadDir='$uploadDir', fileTypes='$fileTypes',"
    . "nbReviewersPerItem='$nbReviewersPerItem', "
    . "ballot_mode='$ballot_mode', sizeOfBallot='$sizeOfBallot', "
    . "discussion_mode='$discussion_mode', "
    . "isCameraReadyOpen='$isCameraReadyOpen',"
    . "chairMail='$chairMail',mailOnAbstract='$mailOnAbstract', "
    . "mailOnUpload='$mailOnUpload', mailOnReview='$mailOnReview', "
    . "submissionDeadline='$submissionDeadline', "
    . " reviewDeadline='$reviewDeadline', "
    . " cameraReadyDeadline='$cameraReadyDeadline', "
    . "date_format='$date_format'" ;
  $db->execRequete ($query);
}

// Insert or update a rate on a paper
function SQLRating ($email, $idPaper, $rate, $significance, $db)
{  
  // Get the rate, if exists
  $rating = GetRating ($idPaper, $email, $db);

  if (!is_object($rating)) 
    {
      // Insert
      $query = "INSERT INTO Rating (idPaper, email, rate, significance)"
	. "VALUES ('$idPaper', '$email', '$rate', $significance) ";
      $db->execRequete ($query);
    }
  else  
    {
      // Update
      $query = "UPDATE Rating SET rate='$rate', significance=$significance"
	. " WHERE email='$email' AND idPaper='$idPaper'";
      $db->execRequete ($query);
    }
}

// Insert or update a paper in a rating box
function SQLRatingBox ($email, $idPaper, $db)
{  
  // Get the rate, if exists
  $rating = GetRatingBox ($idPaper, $email, $db);

  if (!is_object($rating)) 
    {
      // Insert
      $query = "INSERT INTO RatingBox (idPaper, email)"
	. "VALUES ('$idPaper', '$email') ";
      $db->execRequete ($query);
    }
}

// Insert or update a correlation
function SQLCorrelation ($email1, $email2, $correlation, $nbCoRated, $db)
{  
  // Get the correlation, if exists
  $corr = GetCorrelation ($email1, $email2, $db);

  if (!is_object($corr)) 
    {
      // Insert
      $query = "INSERT INTO Correlation (email1, email2, correlation, nbCoRated)"
	. "VALUES ('$email1', '$email2', '$correlation', $nbCoRated) ";
      $db->execRequete ($query);
    }
  else  
    {
      // Update
      $query = "UPDATE Correlation SET correlation='$correlation', "
	. "nbCoRated='$nbCoRated' WHERE email1='$email1' AND email2='$email2'";
      $db->execRequete ($query);
    }
}

// Insert or update a review mark
function SQLReviewMark ($idPaper, $email, $idCriteria, $mark, $db)
{  
  // Get the review mark,  if exists
  $revMark = GetReviewMark ($idPaper, $email, $idCriteria, $db, "object");

  if (!is_object($revMark)) 
    {
      // Insert
      $query = "INSERT INTO ReviewMark (idPaper, email, idCriteria, mark)"
	. "VALUES ('$idPaper', '$email', '$idCriteria', '$mark') ";
    }
  else  
    {
      // Update
      $query = "UPDATE ReviewMark SET mark='$mark' WHERE "
	. " idPaper='$idPaper' AND email='$email' AND idCriteria='$idCriteria'";
    }
  $db->execRequete ($query);
}

function SQLMessage ($message, $db)
{
  // Insertion   
  if (!isSet($message['idParent']))  
    $idParent = 0;
  else
    $idParent = $message['idParent'];
  
  $idPaper = $message['idPaper'];
  $mess = $db->prepareString($message['message']);
  $emailReviewer = $db->prepareString($message['emailReviewer']);

  $query = "INSERT INTO Message (idParent, idPaper, message, date, "
    . "emailReviewer) "
    . " VALUES ('$idParent', '$idPaper', '$mess', NOW(), '$emailReviewer')";

  $db->execRequete ($query);
}
 
?>