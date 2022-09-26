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

function FormAbstract ($abstract, $mode, $target, $db, 
		       &$TEXTS, $upload=false, 
		       $tmp_sid=0) 
{
  global $FILE_TYPES;

  $config = GetConfig($db);

  // If mega upload is used, the target is upload.cgi, else
  // the target is given by the function parameter
  if ($upload and $config['use_megaupload'] == 'Y')
    $action = '/cgi-bin/upload.cgi?sid=' . $tmp_sid;
  else
    $action = $target;

  $form = new Formulaire ("POST", $action);

  $form->champCache ("mode", $mode);
   
  $form->debutTable(VERTICAL, array("BORDER"=>1), 1,
		    $TEXTS->get("FRM_SUBMISSION_TITLE"));

  if ($mode != INSERTION)
    { 
      $id_paper = $abstract["id"];
      $form->champCache ("id", $id_paper);
      // Get the current list of authors
      $authors = GetAuthors($id_paper, $db);
      $my_topics = GetPaperTopics($id_paper, $db);
    }
  else
    {
      if (!isSet($abstract['title'])) $abstract['title']="";
      if (!isSet($abstract['authors'])) $abstract['authors']="";
      if (!isSet($abstract['emailContact'])) $abstract['emailContact']="";
      if (!isSet($abstract['confirmEmail'])) $abstract['confirmEmail']="";
      if (!isSet($abstract['abstract'])) $abstract['abstract']="";
      if (!isSet($abstract['format'])) $abstract['format']="";
      if (!isSet($abstract['topic'])) $abstract['topic']="";
      if (!isSet($abstract['last_name'])) 
	$authors=array();
      else
	{
	  // Get the default values for authors
	  $i=0;
	  foreach ($abstract['last_name'] as $last_name)
	    {
	      if (!empty($last_name))
		{
		  $authors[$i]["first_name"] = $abstract['first_name'][$i];
		  $authors[$i]["last_name"] = $last_name;
		  $authors[$i]["affiliation"] = $abstract['affiliation'][$i];
		  $i++;
		}
	    }	  
	}
      $my_topics = array();
    }

  if (!isSet($abstract['title'])) $abstract['title']="";
  if (!isSet($abstract['authors'])) $abstract['authors']="";

  $form->champTexte ($TEXTS->get("FRM_PAPER_TITLE"), 
				 "title", $abstract['title'], 55, 255);
  $form->champTexte ($TEXTS->get("FRM_PAPER_EMAIL_CONTACT"), 
		     "emailContact", $abstract['emailContact'], 55, 60);
  $form->champTexte ($TEXTS->get("FRM_PAPER_CONFIRM"), 
		     "confirmEmail", $abstract['confirmEmail'], 55, 60);
  if ($config['extended_submission_form'] == "N")
    $form->champTexte ($TEXTS->get("FRM_PAPER_AUTHORS"), "authors", 
		       $abstract['authors'], 55, 255);
  else
    {
      $defaultAuthors = $abstract['authors'];
      // Create an HTML table to display the list of authors
      $tableau = new Tableau (2, array("BORDER"=>1));
      // Create headers
      $tableau->setAfficheEntete (1, FALSE);
      $tableau->ajoutEntete(2, "first_name", $TEXTS->get("FRM_FIRST_NAME"));
      $tableau->ajoutEntete(2, "last_name", $TEXTS->get("FRM_LAST_NAME"));
      $tableau->ajoutEntete(2, "affiliation", $TEXTS->get("FRM_AFFILIATION"));
      // Create a local form 
      $local_form = new Formulaire ();
      for ($i=0; $i < MAX_AUTHORS; $i++)
	{
	  // Get the default values
	  if (isSet($authors[$i]))
	    {
	      $first_name = $authors[$i]["first_name"];
	      $last_name = $authors[$i]["last_name"];
	      $affiliation = $authors[$i]["affiliation"];
	    }
	  else
	    $first_name = $last_name = $affiliation = "";
	  $tableau->ajoutValeur ($i, "first_name", 
				 $local_form->getChamp
				 ($local_form->champTexte 
				  ("", "first_name[$i]", 
				   $first_name, 15, 30)));
	  $tableau->ajoutValeur ($i, "last_name", 
				 $local_form->getChamp
				 ($local_form->champTexte 
				  ("", "last_name[$i]", 
				   $last_name, 15, 30)));
	  $tableau->ajoutValeur ($i, "affiliation", 
				 $local_form->getChamp
				 ($local_form->champTexte 
				  ("", "affiliation[$i]", 
				   $affiliation,20,100)));
	}
      $form->champPLAIN ($TEXTS->get("FRM_AUTHORS"),$tableau->tableauHTML());
      
      // Add a field for other authors
      $form->champTexte ($TEXTS->get("FRM_OTHER_AUTHORS"), "authors", 
			 $abstract['authors'], 55, 255);
    }

  // OK now ask the questions!
  $res = $db->execRequete ("SELECT * FROM PaperQuestion");
  while ($question = $db->objetSuivant ($res)) {
    // Take the list of possible choices
    $list_choices = array();
    $rc = $db->execRequete("SELECT * FROM PQChoice "
			    . " WHERE id_question='$question->id' "
			   . " ORDER BY position" );

    $id_choice = 0;
    while ($choice = $db->objetSuivant ($rc)) {
      if ($id_choice==0) $id_choice = $choice->id_choice; 
      $list_choices[$choice->id_choice] = $choice->choice;
    }

    // Take the default value
    if ($mode == INSERTION)
      $def_answer = $id_choice ; // the first possible choice is the default
    else {
      $ra = $db->execRequete ("SELECT * FROM PaperAnswer "
			      . " WHERE id_question='$question->id' "
			      . " AND id_paper='$id_paper'");
      $answer = $db->objetSuivant($ra);
      $def_answer = $answer->id_answer;
    }

    // Create the form question .. if there are choices!
    if (count($list_choices) > 0)
      $form->champRadio ($question->question, "questions[$question->id]", 
			 $def_answer, $list_choices);
    unset ($id_choice);
  }

  // Get the abstract
  $form->champFenetre ($TEXTS->get("FRM_PAPER_ABSTRACT"), 
		       "abstract", $abstract['abstract'], 15, 40);

  // Get the list of topics
  $qTopic = "SELECT * FROM ResearchTopic";
  $rTopic = $db->execRequete($qTopic);
  $topics = array();
  while ($topic = $db->objetSuivant($rTopic))
    $topics[$topic->id] = $topic->label;
  if (count($topics) != 0) {
      $form->champListe ($TEXTS->get("FRM_PAPER_TOPIC"), "topic", 
		     $abstract['topic'], 1, $topics);

      if ($config['multi_topics'] == "Y") {
	// Show a list of secondary topics
	$form->champCheckBox ($TEXTS->get("FRM_PAPER_TOPICS"), 
			      "topics", $my_topics, $topics, 5);
      }
  }
  else 
    $form->champCache ("topic", "");


  if ($upload)
    {
      $form->champFichier ($TEXTS->get("FRM_PAPER_UPLOAD"), "file", 30);
      // Ask for the file type
      $fileTypes = explode(";", $config['fileTypes']);
      // Always at least one format!
      if (count($fileTypes) == 0) $fileTypes['pdf'] = "PDF";
      if (!isSet($abstract['format'])) $abstract['format'] = "pdf";

      if (count($fileTypes) == 1)
	{
	  // One format accepted: hidden field
	  $form->champCache ("format", $config['fileTypes']);
	} 
      else
	{
	  // Several types accepted: give the choice
	  foreach ($fileTypes as $key => $val)
	    if (isSet($FILE_TYPES[$val]))
	      $arrayTypes[$val] = $FILE_TYPES[$val];
	  $form->champRadio ($TEXTS->get("FRM_PAPER_FORMAT"), "format", 
			     $abstract['format'],  $arrayTypes);
	}
    }
  else
    {
      $form->champCache ("format", "pdf");
    }
  
  $form->finTable();


  $form->ajoutTexte("<P>");

  if ($upload and $config['use_megaupload']=='Y') {
    // Add some fields to the form, for megaupload support
    $form->champCache("sessionid",$tmp_sid);
    $form->ajoutTexte("<script language='javascript' type='text/javascript' "
		      . " src='megaupload/script.js'></script>");
    $form->ajoutTexte("<input type='button' name='mysubmit' value='"
		      . $TEXTS->get("FRM_SUBMIT")."' onClick='postIt();'>");
  }
  else
    $form->champValider ($TEXTS->get("FRM_SUBMIT"), "submit");
  
  return $form->formulaireHTML();
}

?>