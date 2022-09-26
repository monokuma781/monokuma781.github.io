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
 
// List of papers with their reviews
function PapersReviews (&$tpl, $db, &$TEXTS, &$CODES)
{
  global $EXPERTISE;

  ini_set ("memory_limit", "100M");

  $target = "Admin.php?action=3"; 

  // If required, hide the selection form
  if (isSet($_REQUEST['hide_selection_form'])) {
    $db->execRequete ("UPDATE Config SET show_selection_form='N'");
  }
  else if (isSet($_REQUEST['show_selection_form'])) {
    $db->execRequete ("UPDATE Config SET show_selection_form='Y'");
  }
  
  // Get the configuration
  $config = GetConfig ($db); 
 
  $tpl->set_var("TITLE", "Status of  papers"); 
 
  // Extract the block for each paper 
  $tpl->set_block("StatusOfPapers", "SELECTION_FORM"); 
  $tpl->set_block("StatusOfPapers", "SHOW_SELECTION_FORM"); 
  $tpl->set_block("StatusOfPapers", "PAPER_DETAIL", "PAPERS"); 
  $tpl->set_block("StatusOfPapers", "REVIEW_CRITERIA", "REVIEW_CRITERIAS"); 
  $tpl->set_block("PAPER_DETAIL", "PAPER_INFO", "PAPER_DATA"); 
  $tpl->set_block("PAPER_DETAIL", "REVIEW_MARK", "REVIEW_MARKS"); 
  $tpl->set_block("PAPER_DETAIL", "REVIEWER", "REVIEWER_INFO"); 
  $tpl->set_var("PAPERS", ""); 
 
  // Show the form for filtering papers, if required 
  if ($config['show_selection_form'] == 'Y') {
    $tpl->set_var("FORM_SELECT_PAPERS",  
		  FormSelectPapers (STATUS_OF_PAPERS, $tpl, $db,  
				    $TEXTS, $CODES)); 
    $tpl->set_var ("SHOW_SELECTION_FORM", "");
  }
  else {
    $tpl->set_var ("SELECTION_FORM", "");
  }

  // Create the list of lisst to toggle all the selected papers 
  $comma = $listLinks = ""; 
  $statusList = GetListStatus($db); 
  foreach ($statusList as $id => $sVals) 
  { 
   $listLinks .= $comma . " <a href='#' onClick=\"TogglePaperStatus('$id')\">" 
            . $sVals['label'] . "</a>"; 
   $comma = ",";   
  } 
  $tpl->set_var("TOGGLE_LIST", $listLinks); 
 
  // Header of the  table, taken from table Criteria 
  $tpl->set_var("REVIEW_CRITERIAS", ""); 
  $listC = GetListCriterias($db); 
  foreach ($listC as $id => $crVals) 
  { 
    $tpl->set_var("CRITERIA",ucfirst($crVals['label']));    
    $tpl->parse ("REVIEW_CRITERIAS", "REVIEW_CRITERIA", true); 
  } 
 
  // Sort the papers on the average 'overall' field 
  $query = "SELECT p.id, round(AVG(overall),4) AS overall " 
     . "FROM Paper p LEFT JOIN Review r ON p.id=r.idPaper " 
     .  " WHERE inCurrentSelection='Y' " 
     . "  GROUP BY p.id"; 
  $result = $db->execRequete($query); 
 
  $arrPaper = array(); 
  while ($paper = $db->objetSuivant($result)) 
    $arrPaper[$paper->id] = $paper->overall;  
 
  // Sort in descending order 
  arsort($arrPaper); 
  reset ($arrPaper); 
 
  // List the papers in order 
  $iPaper = 0; 
  while (list ($idPaper, $overall) = each ($arrPaper)) 
    { 
      $iPaper++; 
      // Choose the CSS class 
      if ($iPaper % 2 == 0) 
	$tpl->set_var("CSS_CLASS", "even"); 
      else 
	$tpl->set_var("CSS_CLASS", "odd"); 
 
      $paper = GetPaper ($idPaper, $db, "object"); 
 
      InstanciatePaperVars ($paper, $tpl, $db); 
      $tpl->set_var("PAPER_RANK", $iPaper); 
      $tpl->set_var("SESSION_ID", session_id()); 
      $tpl->set_var("CONF_URL", $config['confURL']); 
 
      $qRev = "SELECT idPaper, email FROM Review " 
             . "WHERE idPaper='$paper->id'"; 
 
      $nb_reviewers = CountReviewers ($paper->id, $db); 
 
      $tpl->set_var("NB_REVIEWERS", Max(1, $nb_reviewers)); 
      $tpl->set_var("ID_MESSAGE", REVIEWS_TO_REVIEWERS); 
      $tpl->set_var("ID_MESSAGE_STATUS", STATUS_TO_AUTHORS); 
      $tpl->set_var("TARGET", urlencode($target)); 
 
      $tpl->set_var ("FORM_STATUS", FormStatus($paper, $target, $db)); 
 
      $tpl->parse("PAPER_DATA", "PAPER_INFO"); 
 
      $resRev = $db->execRequete ($qRev); 
      while ($rid = $db->ligneSuivante($resRev)) 
	{ 
          // Get the review + its marks 
          $review = GetReview ($rid['idPaper'], $rid['email'], $db); 
 
	  $reviewer = GetMember ($review['email'], $db, "object"); 
          InstanciateMemberVars ($reviewer, $tpl, $db); 
          $tpl->parse("REVIEWER_INFO", "REVIEWER"); 
 
	  if (isSet($review['reviewerExpertise'])) 
	    $tpl->set_var("EXPERTISE",  
                          $EXPERTISE[$review['reviewerExpertise']]); 
	  else 
	    $tpl->set_var("EXPERTISE","?"); 
 
          $tpl->set_var("REVIEWER_MARK", $review['overall']); 
 
	  $review = DefaultVal($review); 
          reset($listC); 
          $tpl->set_var("REVIEW_MARKS", ""); 
          foreach ($listC as $id => $label) 
          { 
     	    $tpl->set_var("MARK", $review[$id]); 
            $tpl->parse ("REVIEW_MARKS", "REVIEW_MARK", true); 
          } 
 
	  $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
	  $tpl->set_var("PAPER_DATA", ""); 
      }  
 
      // Show the paper even without reviewer 
      if ($nb_reviewers == 0) 
	{ 
	  $tpl->set_var("REVIEW_MARKS", ""); 
	  $tpl->set_var("REVIEWER_MARK", ""); 
	  $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
	} 
       
      // Summary for the paper 
      $statPaper = StatPaper ($paper->id, $db); 
      $tpl->set_var("NB_REVIEWERS", 1); 
      $tpl->set_var("PAPER_DATA","<td>&nbsp;</td><th>Summary:</th>"); 
      $tpl->set_var("REVIEWER_INFO","&nbsp;"); 
      $tpl->set_var("REVIEWER_MARK", $overall); 
 
      reset($listC); 
      $tpl->set_var("REVIEW_MARKS", ""); 
      foreach ($listC as $id => $label) 
       { 
        $tpl->set_var("MARK", $statPaper[$id]); 
        $tpl->parse ("REVIEW_MARKS", "REVIEW_MARK", true); 
       } 
 
      $tpl->parse("PAPERS", "PAPER_DETAIL", true); 
    } 
  $tpl->parse("BODY", "StatusOfPapers"); 
} 
 
// Give a default value for missing rates 
function DefaultVal ($arr) 
{ 
  $def = "?"; 
  while (list($key, $val) = each($arr)) 
    if (empty($val)) $arr[$key] = $def; 
 
  return $arr; 
} 
 
?>
