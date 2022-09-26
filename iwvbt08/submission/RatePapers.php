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
$config = GetConfig($db);

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
                        "TxtPapers2Rate" => TPLDIR . "TxtPapers2Rate.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_PAPERS_TO_RATE"));

$tpl->set_block("TxtPapers2Rate", "RATING_MESSAGE", "HELP_MESSAGE");
$tpl->set_block("TxtPapers2Rate", "ACK_RATING_MESSAGE", "ACK_MESSAGE");
// Extract the 'block' describing a line from the template
$tpl->set_block("TxtPapers2Rate", "PAPER_DETAIL", "PAPERS");
$tpl->set_block("TxtPapers2Rate", "GROUPS_LINKS", "LINKS");
$tpl->set_var("PAPERS", "");
$tpl->set_var("SIZE_RATING", SIZE_RATING);
$tpl->set_var("LINKS", "");

// Initialize the current interval
if (!isSet($_REQUEST['iMin']))
{
  $iMinCur = 1; $iMaxCur = SIZE_RATING;
}
else
{
  $iMinCur = $_REQUEST['iMin'];  $iMaxCur = $_REQUEST['iMax'];
}

$tpl->set_var("IMIN_CUR", $iMinCur);
$tpl->set_var("IMAX_CUR", $iMaxCur);

// First check for access rights
if (isSet($_GET['logout']))
{
  // Delete the current session
  $q = "DELETE FROM Session WHERE idSession='" . session_id() . "'";
  $db->execRequete($q);
}

$session = CheckAccess ("RatePapers.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  // If rates have been submitted: insert/update in the DB
  if (isSet($_POST['rates']))
    {
      while (list($idPaper, $rate) = each ($_POST['rates']))
	{
	  if ($rate != UNKNOWN_RATING)
	    SQLRating ($session->email, $idPaper, $rate, 1, $db);
	  $tpl->parse("ACK_MESSAGE", "ACK_RATING_MESSAGE");
	  $tpl->set_var("HELP_MESSAGE", "");
	}
    }
  else
    {
      // Print the main message
      $tpl->set_var("ACK_MESSAGE", "");
      $tpl->parse("HELP_MESSAGE", "RATING_MESSAGE");
    }
  
  // Check that we  have papers to rate
  $qRates = "SELECT COUNT(*) AS nbMissingRates FROM RatingBox "
    . "WHERE email='$session->email'";
  $rRates = $db->execRequete ($qRates);
  $rObj = $db->objetSuivant($rRates);
  if ($rObj->nbMissingRates == 0)
    {
      $tpl->set_var("BODY", $TEXTS->get("TXT_NO_MORE_RATES"));
    }
  else
    {
      // Show the list 
      $qRate = "SELECT * FROM RateLabel";
      $rRate = $db->execRequete($qRate);
      $rates=array(UNKNOWN_RATING => "?");
      while ($rate=$db->objetSuivant($rRate))
	$rates[$rate->id] = $rate->label;

      $form = new Formulaire ( "POST", "RatePapers.php");

      /* Select the papers marked for rating   */
      $query = "SELECT * FROM Paper p, RatingBox r "
	. "WHERE p.id=r.idPaper AND r.email='$session->email'";

      $i = 0;
      $result = $db->execRequete ($query);
      while ($paper = $db->objetSuivant($result))
	{
	  $i++;
	  // Only show the current group
	  if ($iMinCur <= $i and $i <= $iMaxCur)
	    {
	      // Instanciate paper variables
	      InstanciatePaperVars ($paper, $tpl, $db);
	      
	      // Choose the CSS class
	      if ($i % 2 == 0)
		$tpl->set_var("CSS_CLASS", "even");
	      else
		$tpl->set_var("CSS_CLASS", "odd");

	      $tpl->set_var("SESSION_ID", session_id());
	      $tpl->set_var("CONF_URL", $config['confURL']);
	      
	      $rating = GetRating ($paper->id, $session->email, $db);
	      if (is_object($rating))
		$rate = $rating->rate;
	      else
		$rate = UNKNOWN_RATING;
	      
	      $tpl->set_var("PAPER_RATE", 
			    $form->champSELECT ("rates[$paper->id]", 
						$rates, $rate, 1));
	  
	      /* Instanciate the entities in PAPER_DETAIL. Put the
               result in PAPERS   */
	      $tpl->parse("PAPERS", "PAPER_DETAIL", true);
	    }
	  if ($i > $iMaxCur) break;
	}

      // Create the groups
      $nbPapers = CountAllPapers($db);
      $nb_groups = $nbPapers / SIZE_RATING + 1;
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
      $tpl->parse("BODY", "TxtPapers2Rate");
    }
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>