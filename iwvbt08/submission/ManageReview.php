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
 
// Mail subjects
 
function ManageReview ($_POST, $review, &$tpl, $db, &$TEXTS)
{
  $config = GetConfig($db);
  $paper = GetPaper ($review['idPaper'], $db, "obj");
  $member = GetMember ($review['email'], $db, "obj");

  // Instanciation of templates variables
  InstanciatePaperVars ($paper, $tpl, $db);
  InstanciateConfigVars ($config, $tpl);

  // Get the list of criterias 
  $listC = GetListCriterias($db);

  if (!isSet($_POST['mode']))
  {
    // Show the form
    $tpl->parse("BODY", "TxtInfoReview");
    $tpl->set_var ("FormReview", 
                   FormReview ($review, MAJ, "Review.php", $listC, $TEXTS));
      $tpl->parse("BODY", "FormReview", true);
	}
  else  {
    $tpl->parse("BODY", "TxtInfoPostReview");
    
    SQLUpdateReview ($_POST, $db);
    $review = GetReview ($review['idPaper'], $review['email'],
			 $db);
	
    $tpl->set_var ("FormReview", FormReview ($review, MAJ,
					     "Review.php", $listC, $TEXTS));
    $tpl->parse("BODY", "FormReview", true);
      
    // Send a mail to confirm review submission
    $tpl->set_file ("MailAckReview", TPLDIR . "MailAckReview.tpl");
    $tpl->set_file ("ShowReview", TPLDIR . "TxtShowReview.tpl");
    $tpl->set_var("REVIEW",
		  DisplayReviews ($review['idPaper'], 
				  "ShowReview", $tpl, $db,
				  $review['email'], false));
    $tpl->set_var("NAME_REVIEWER", "$member->firstName $member->lastName");
	
    $message = $tpl->parse("MESSAGE", "MailAckReview");

    if ($config['mailOnReview'] == 'Y')
      $carbon_copy = $config['chairMail'];
    else
      $carbon_copy = "";

    SendMail ($review['email'], $config['confAcronym'] . 
	      " - " . $TEXTS->get("SUBJ_ACK_REVIEW") . " " . $paper->id,
	      $tpl->get_var("MESSAGE"),
	      $config['chairMail'], 
	      $config['chairMail'], $carbon_copy);
  }
 
  $tpl->set_var("BACK", 
		"<a href='Review.php'>Back to your list of papers</a><br>");
  $tpl->parse("BODY", "BACK", true);
}
?>