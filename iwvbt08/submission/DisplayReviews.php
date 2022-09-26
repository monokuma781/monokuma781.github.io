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
 
// Create a string with the reviews of a paper
function DisplayReviews ($idPaper, $template, &$tpl, $db,
			 $emailReviewer="", $html=false)
{
  global $EXPERTISE;
  global $SCALE;
  
  // Handle NULL values
  $SCALE[""] = "?";
  $EXPERTISE[""] = "?";

  $config = GetConfig($db);

  // Extract the block with marks. Check that it has 
  // not been done before
  if (!isSet($tpl->varkeys["REVIEW_MARK"]))
    $tpl->set_block($template, "REVIEW_MARK", "REVIEW_MARKS");
  else  
    $tpl->set_var("REVIEW_MARKS", "");

  // Extracts the block with questions
  if (!isSet($tpl->varkeys["REVIEW_QUESTION"]))
    $tpl->set_block($template, "REVIEW_QUESTION", "REVIEW_QUESTIONS");

  $tpl->set_var("REVIEW_QUESTIONS", "");

  // Select one or all reviews, depending on the emailReviewer variable
  if (empty($emailReviewer))
    $qRev = "SELECT idPaper,email FROM Review "
      . "WHERE idPaper='$idPaper'";
  else
    $qRev = "SELECT idPaper, email FROM Review "
      . "WHERE idPaper='$idPaper' "
      . " and email='$emailReviewer'";

  // Initialize the REVIEWS entity to empty string
  $tpl->set_var("REVIEWS", "");

  $listC = GetListCriterias ($db);
  $resRev = $db->execRequete ($qRev);
  $i = 1;
  while ($rid = $db->ligneSuivante($resRev))
    {
      // Get the review + the marks
      $email = $rid['email'];
      $review = GetReview ($rid['idPaper'], $email, $db);
      $reviewer = GetMember ($review['email'], $db);
      $tpl->set_var("REVIEWER_NAME", $reviewer['firstName']
		    . " " . $reviewer['lastName']) ;
      $tpl->set_var("REVIEW_EXT_REV_NAME", $review['fname_ext_reviewer']
		    . " " . $review['lname_ext_reviewer']) ;
      $tpl->set_var("REVIEWER_NO", $i++);
      $tpl->set_var("REVIEW_OVERALL", $review['overall']);

      // Show the marks
      $tpl->set_var("REVIEW_MARKS", "");
      $j = 0;
      foreach ($listC as $id => $crVals)
	{
	  // Choose the CSS class
	  if ($j++ %2 == 0)
	    $tpl->set_var("CSS_CLASS", "even");
	  else
	    $tpl->set_var("CSS_CLASS", "odd");
	
	  $tpl->set_var("CRITERIA", ucfirst($crVals['label']));
	  $tpl->set_var("MARK", $SCALE[$review[$id]]) ;
	  $tpl->parse("REVIEW_MARKS", "REVIEW_MARK", true); 
	}

      $tpl->set_var("REVIEWER_EXPERTISE", 
		    $EXPERTISE[$review['reviewerExpertise']]);
      if ($html)
	{
	  $tpl->set_var("REVIEW_SUMMARY", 
			String2HTML($review['summary']));
	  $tpl->set_var("REVIEW_DETAILS", 
			String2HTML($review['details']));
	  $tpl->set_var("REVIEW_COMMENTS", 
			String2HTML($review['comments']));
	}
      else
	{
	  $tpl->set_var("REVIEW_SUMMARY", $review['summary']);
	  $tpl->set_var("REVIEW_DETAILS", $review['details']);
	  $tpl->set_var("REVIEW_COMMENTS", $review['comments']);
	}
      
      // Put the questions
      $q_questions = "SELECT * FROM ReviewQuestion q, RQChoice c, "
	. " ReviewAnswer a "
	. " WHERE q.id=c.id_question AND a.id_answer=c.id_choice "
	. " AND id_paper=$idPaper AND email='$email' AND public='Y'";

      $tpl->set_var("REVIEW_QUESTIONS", "");

      $rq = $db->execRequete($q_questions);
      while ($question = $db->objetSuivant($rq)) {
	if ($j++ %2 == 0)
	  $tpl->set_var("CSS_CLASS", "even");
	else
	  $tpl->set_var("CSS_CLASS", "odd");
	$tpl->set_var("QUESTION", $question->question);
	$tpl->set_var("ANSWER", $question->choice);
	$tpl->parse ("REVIEW_QUESTIONS", "REVIEW_QUESTION", true);
      }

      $tpl->parse("REVIEWS", $template, true);
    }
  
  return $tpl->get_var("REVIEWS");
}

?>