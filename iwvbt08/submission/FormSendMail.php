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

function FormSendMail ($to, $subject, $body)
{
  // Create the form
  $form = new Formulaire ("POST", "SendMail.php");
   
  $form->debutTable();

  // Add a hidden field to inform the script that a mail
  // must be sent

  $form->champCache ("sendmail", 1);

  $form->champTexte ("To ", "to", $to, 50, 100);
  $form->champTexte ("Subject", "subject", $subject, 50, 100);
  $form->champFenetre ("Body", "body", $body, 20, 50);
  
  $form->finTable();
  $form->champValider ("Send", "submit");
  $form->champAnnuler ("Cancel", "cancel");

  return $form->fin(false);
}

?>