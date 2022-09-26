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

// Create a for to assign reviewers to a paper

function FormAssignReviewers ($idPaper, $target, $db)
{
  $config = GetConfig ($db);

  // Compute the list of reviewers
  $query = "SELECT * FROM PCMember WHERE roles LIKE '%R%' ORDER BY lastName";
  $result = $db->execRequete ($query);

  // An entry when no reviewer has yet been assigned
  $listPCM[NOBODY] = NOBODY;

  while ($pcm = $db->objetSuivant($result))
    {
      $nbPapersAssigned = CountPapers($pcm->email, $db);
      $key = $pcm->email;

      $listPCM[$key] = $pcm->firstName . " " . $pcm->lastName
                   . "  - $nbPapersAssigned papers ";

      $rating = GetRating ($idPaper, $pcm->email, $db);
      if (is_object($rating))
         $listPCM[$key] .= " - rating=$rating->rate";
      }

  // Get the already assigned reviewers.
  $qrev = "SELECT * FROM Review WHERE idPaper='$idPaper' "
     . " ORDER BY email";
  $resRev = $db->execRequete($qrev);
  $i = 1;
  $tabMails = array();
  while ($reviewer = $db->objetSuivant($resRev))
    {
      $tabMails[$i++] = $reviewer->email;
    }

  // Display the number of existing reviewers and another
  // button for adding another one. And at least NB_REVIEWERS of course
  
  $nbRev = count($tabMails) + 1;

  if ($nbRev < $config['nbReviewersPerItem']) 
    $nbRev = $config['nbReviewersPerItem'];

  // Create the form
  $form = new Formulaire ("POST", $target);
   
  $form->champCache ("idPaper", $idPaper);

  $form->debutTable();

  for ($i=1; $i <= $nbRev; $i++)
    {
      if (isSet($tabMails[$i]))
	{
	  $default = $tabMails[$i];
	  $label = "Reviewer $i " . 
	     Ancre ("AssignReviewers.php?action=2&remove=" . 
		    $tabMails[$i] . "&idPaper=" . $idPaper,  " (Remove)");
	}
      else
	{
	  $label = "Reviewer $i ";
	  $default = NOBODY;
	}
      $form->champliste ($label, "tabMails[$i]", $default, 1, $listPCM);
    }


  $form->finTable();

  $form->champValider ("Submit", "submit");

  return $form->fin(false);
}

?>