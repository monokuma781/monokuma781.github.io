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
require_once ("Formulaire.class.php");

function FormReview ($review, $mode, $target, $listCriterias, &$TEXTS)
{
  global $EXPERTISE;
  global $SCALE;

  // Connect to the database
  $db = new BD (NAME, PASS, BASE, SERVER);

  // Create the form
  $form = new Formulaire ("POST", $target);
  $form->champCache ("mode", $mode);
  $email = $review['email'];
  $id_paper = $review['idPaper'];

  if ($mode != INSERTION)    { 
    $form->champCache ("idPaper", $review['idPaper']);
    $form->champCache ("email", $review['email']);
  }
  
  $form->debutTable(VERTICAL, array("BORDER"=>1), 1,
		    $TEXTS->get("FRM_REVIEW_TITLE"));
  
  // Show the list of criterias
  foreach ($listCriterias as $id => $crVals) {
    $label = $crVals['label'];
    $expl = $crVals['explanations'];
    if (!isSet($review[$id])) 
      $review[$id] = 4;
    else if ($review[$id] == "") 
      $review[$id] = 4;
    
    $form->champListe (ucfirst($label) . "<P class='expl'>$expl</P>",  
		       "$id", 
		       $review[$id], 1, $SCALE);
  }
  
  $form->champListe ($TEXTS->get("FRM_REVIEW_EXPERTISE"), 
		     "reviewerExpertise", 
		     $review['reviewerExpertise'], 1, 
                                          $EXPERTISE);

  $form->champFenetre ($TEXTS->get("FRM_REVIEW_SUMMARY"), 
                   "summary", $review['summary'], 5, 40);
  $form->champFenetre ($TEXTS->get("FRM_REVIEW_COMMENTS"), 
                   "details", $review['details'], 15, 40);
  
  // Questions
  $res = $db->execRequete ("SELECT * FROM ReviewQuestion");
  while ($question = $db->objetSuivant ($res)) {
    // Take the list of possible choices
    $list_choices = array();
    $rc = $db->execRequete("SELECT * FROM RQChoice "
			    . " WHERE id_question='$question->id' "
			   . " ORDER BY position " );
    while ($choice = $db->objetSuivant ($rc)) {
      if (!isSet($id_choice)) $id_choice = $choice->id_choice; 
      $list_choices[$choice->id_choice] = $choice->choice;
    }

    // Take the default value
    $ra = $db->execRequete ("SELECT * FROM ReviewAnswer "
			    . " WHERE id_question='$question->id' "
			    . " AND id_paper='$id_paper' "
			    . " AND email='$email'");
    $answer = $db->objetSuivant($ra);
    if (is_object($answer))
      $def_answer = $answer->id_answer;
    else
      $def_answer = $id_choice;
    
    // Create the form question .. if there are choices!
    if (count($list_choices) > 0)
      $form->champRadio ($question->question, "questions[$question->id]", 
			 $def_answer, $list_choices);
    unset ($id_choice);
  }

  // Other fields
  $form->champFenetre ($TEXTS->get("FRM_REVIEW_PCCOMMENTS"), 
                   "comments", $review['comments'], 4, 40);
  $form->champTexte ($TEXTS->get("FRM_REVIEW_FNAME_EXT"),
		     "fname_ext_reviewer", $review['fname_ext_reviewer'], 
		     30, 60);
  $form->champTexte ($TEXTS->get("FRM_REVIEW_LNAME_EXT"),
		     "lname_ext_reviewer", $review['lname_ext_reviewer'], 
		     30, 60);

  $form->finTable();
  $form->champValider ($TEXTS->get("FRM_SUBMIT"), "submit");

  return $form->fin(false);
}

?>