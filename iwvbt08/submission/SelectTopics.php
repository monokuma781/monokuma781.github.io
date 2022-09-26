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
 
 
// Allows reviewers to select their preferred topics

 session_start(); 

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
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
			"TxtSelectTopics" => TPLDIR . "TxtSelectTopics.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_RESEARCH_TOPICS"));

// Logout required?
if (isSet($_GET['logout']))
{
  // Delete the current session
  $q = "DELETE FROM Session WHERE idSession='" . session_id() . "'";
  $db->execRequete($q);
}
// First check for access rights
 $session = CheckAccess ("SelectTopics.php", $_POST, 
			 session_id(), $db, $tpl);

if (is_object($session))
{
  $tpl->set_var("MESSAGE", $TEXTS->get("TXT_SELECT_TOPICS"));

  if (isSet($_POST['topics']))
    {
      // Clean the DB
      $qDelete = "DELETE FROM SelectedTopic WHERE email='$session->email'";
      $db->execRequete ($qDelete);

      // Insert in the DB
      foreach ($_POST['topics'] as $key => $val)
	{
	  $qInsert = "INSERT INTO SelectedTopic (email, idTopic) "
	     . "VALUES ('$session->email', '$val')";
	  $db->execRequete($qInsert);
	}
      $tpl->set_var("MESSAGE", $TEXTS->get("TXT_TOPICS_SELECTED"));
    }
  
  // Get the existing list of selected topics
  $qTopics = "SELECT * FROM SelectedTopic WHERE email='$session->email'";
  $rTopics = $db->execRequete ($qTopics);
  $topics = array();
  while ($topic = $db->objetSuivant($rTopics)) 
    $topics[$topic->idTopic] = 1;

  $tpl->set_var ("FORM_TOPICS",
		 FormSelectTopics ($topics, "SelectTopics.php", $db));
  $tpl->parse("BODY", "TxtSelectTopics");
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>