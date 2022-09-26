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

function FormMessage ($idPaper, $idParent, $email, $target, 
		      $db, &$TEXTS)
{
  $paper = GetPaper($idPaper, $db);
  $f = new Formulaire ("POST", $target, false);

  if ($idParent != "" and $idParent != 0)
    {
      $father = GetMessage ($idParent, $db);
      $f->ajoutTexte ("<b>" . $TEXTS->get("FRM_ANSWER_TO") . "</b>: "
		      . nl2br($father->message) . "<br><b>".
		      $TEXTS->get("FRM_SENT_BY") . 
		      "</b>: $father->emailReviewer");
      $typeMess = $TEXTS->get("FRM_ANSWER");
    }
  else      $typeMess = $TEXTS->get("FRM_MESSAGE");

  $f->debutTable();

  // Hidden fields: id of the the paper, email of the reviewer,
  // and id of the father message (if any)
  $f->champCache ("idPaper", $idPaper);
  $f->champCache ("emailReviewer", $email);

  if ($idParent != "" and $idParent != 0)
    {
      $f->champCache ("idParent", $idParent);
    }

  $f->champPlain ($TEXTS->get("FRM_PAPER_TITLE"), $paper['title']);
  $f->champFenetre ($typeMess, "message", "", 4, 50);
  $f->finTable();
  $f->champValider ($TEXTS->get("FRM_SUBMIT"), "submit");

  return $f->fin(false);
}