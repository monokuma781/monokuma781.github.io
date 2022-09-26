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

function FormSelectPapers ($action, &$tpl, $db, &$TEXTS, &$CODES)
{
  // Load the form template
  $tpl->set_file("FormSelectPapers", TPLDIR . "FormSelectPapers.tpl");
  $tpl->set_block("FormSelectPapers", "ALL_QUESTIONS", "QUESTIONS_BLOCK");
  $tpl->set_block("ALL_QUESTIONS", "PAPER_QUESTION", "PAPER_QUESTIONS");
  $tpl->set_block("ALL_QUESTIONS", "REVIEW_QUESTION", "REVIEW_QUESTIONS");
  $tpl->set_var ("QUESTIONS_BLOCK", "");
  $tpl->set_var ("PAPER_QUESTIONS", "");
  $tpl->set_var ("REVIEW_QUESTIONS", "");

  // Let the lists of choices 
  $config = GetConfig($db);
  $statusList = GetListStatus ($db);
  $sl = array(SP_ANY_STATUS => "Any",
              SP_NOT_YET_ASSIGNED => "Not yet assigned");
  foreach ($statusList as $id => $sVals)  $sl[$id]  = $sVals['label'];

  $conflicts = $CODES->get("conflicts");
  $missing = $CODES->get("missing_review");
  $uploaded = $CODES->get("uploaded");
  $filters = $CODES->get("filters");

  $tpl->set_var ("ACTION", $action);

  // Get the list of reviewers
  $listReviewers =  GetCodeList ("PCMember", $db, $id="email", 
				 $name="CONCAT(lastName, ', ', firstName)");
  $listReviewers["All"] = "Any"; 
  ksort ($listReviewers);

  // Get the list of topics
  $listTopics = array ("0" => "Any");
  $qTopics = "SELECT * FROM ResearchTopic ORDER BY label";
  $rTopics = $db->execRequete ($qTopics);
  while ($topic= $db->objetSuivant($rTopics))
    $listTopics[$topic->id] = $topic->label; 

  // Set the default values
  $tpl->set_var ("PAPERS_WITH_TITLE", $config['papersWithTitle']);
  $tpl->set_var ("PAPERS_WITH_AUTHOR", $config['papersWithAuthor']);
  $tpl->set_var 
    ("SP_UPLOADED", SelectField ('spUploaded', $uploaded, 
				 $config['papersUploaded']));
  $tpl->set_var ("SP_STATUS", 
		 SelectField ('spStatus', $sl, $config['papersWithStatus']));
  $tpl->set_var 
    ("SP_FILTER", SelectField ('spFilter', $filters, 
			       $config['papersWithFilter']));
  $tpl->set_var ("SP_RATE", $config['papersWithRate']);
  $tpl->set_var ("SP_REVIEWERS", 
		 SelectField ('spReviewer', $listReviewers, 
			      $config['papersWithReviewer']));
  $tpl->set_var ("SP_TOPICS", 
		 SelectField ('spTopic', $listTopics, 
			      $config['papersWithTopic']));
  $tpl->set_var ("SP_CONFLICTS", 
		 SelectField ('spConflict', $conflicts, 
			      $config['papersWithConflict']));
  $tpl->set_var ("SP_MISSING", 
		 SelectField ('spMissing', $missing, 
			      $config['papersWithMissingReview']));

  // Paper questions
  $nb_pquestions = CreateQuestionsField ($tpl, $db, 
					 $config['papersQuestions'],
					 "PAPER");
  // Review questions
  $nb_rquestions = CreateQuestionsField ($tpl, $db, 
					 $config['reviewsQuestions'],
					 "REVIEW");
  if ($nb_pquestions > 0 or $nb_rquestions > 0)
    $tpl->parse ("QUESTIONS_BLOCK", "ALL_QUESTIONS");
  
  // Create the form and return
  $tpl->parse ("FSP", "FormSelectPapers"); 
  return $tpl->get_var("FSP");
}

function CreateQuestionsField (&$tpl, $db, $db_encoding, $field_type)
{
  if ($field_type == "PAPER") {
    $tb_question = "PaperQuestion";
    $tb_choice = "PQChoice";
    $select_field = "paperQuestions";
  }
  else    {
    $tb_question = "ReviewQuestion";
    $tb_choice = "RQChoice";
    $select_field = "reviewQuestions";
  }

  // First decode the current default values, from Config
  $questions = array();
  if (!empty($db_encoding)) {
    $coded_questions = explode (";", $db_encoding);
    $questions = array();
    foreach ($coded_questions as $question) {
      $q = explode (",", $question);
      $questions[trim($q[0])] = trim($q[1]);
    }
  }

  $nb_questions= 0;
  $rq = $db->execRequete ("SELECT * FROM $tb_question");
  while ($question = $db->objetSuivant($rq))
  {
    // Get the list of choices
    $rc = $db->execRequete("SELECT * FROM $tb_choice "
			   . " WHERE id_question='$question->id'");
    $list_choices = array ("0" => "Any");
    while ($choice = $db->objetSuivant($rc)) {
      $list_choices[$choice->id_choice] = $choice->choice;
    }

    if (isSet($questions[$question->id]))
      $def_val = $questions[$question->id];
    else
      $def_val = SP_ANY_CHOICE;

    $sel_choices = SelectField ("$select_field" . "[$question->id]", 
				$list_choices, $def_val);
    $tpl->set_var ("QUESTION", $question->question);
    $tpl->set_var ("CHOICES", $sel_choices);
    $tpl->parse ("{$field_type}_QUESTIONS", "{$field_type}_QUESTION", true);
    $nb_questions++;
  }
  return $nb_questions;
}
?>