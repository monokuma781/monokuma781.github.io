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

function FormAssign ($idPaper, $email)
{
  // Create the form
  $form = new Formulaire ("POST", $target);

  $frm = "<INPUT TYPE='HIDDEN' NAME='idPaper[]' VALUE='$paper->id'>";
  return $frm . 
     $form->champBUTTONS ("RADIO", "status[$paper->id]", 
			  $statusList, $paper->status, array());

}

?>
