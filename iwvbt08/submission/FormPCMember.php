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

function FormPCMember ($member, $mode, $target)
{
  global $ROLES;

  $choice = array ("Y" => "Yes", "N" =>"No");

  // Create the form
  $form = new Formulaire ("POST", $target);
  $form->champCache ("formPCMember", 1);
  $form->champCache ("mode", $mode);
   
  $form->debutTable();

  if ($mode != INSERTION)
  { 
      $form->champPlain ("Email ", $member['email']);
      $form->champCache ("email", $member['email']);
  }
  else
    {
      if (!isSet($member['email'])) $member['email']="";
      if (!isSet($member['lastName'])) $member['lastName']="";
      if (!isSet($member['firstName'])) $member['firstName']="";
      if (!isSet($member['affiliation'])) $member['affiliation']="";
      if (!isSet($member['roles'])) $member['roles']= "R";
      $form->champTexte ("Email ", "email", $member['email'], 30, 60);
    }

  $sroles = explode(",", $member['roles']);
  foreach ($sroles as $key => $val) $roles[$val]= "";

  $form->champTexte ("First name", "firstName", $member['firstName'], 30, 40);
  $form->champTexte ("Last name", "lastName", $member['lastName'], 30, 40);
  $form->champTexte ("Affiliation", "affiliation", $member['affiliation'], 40);
  $form->champCheckBox ("Roles", "roles[]", $roles, $ROLES, 5);

  $form->finTable();
  $form->champValider ("Submit", "submit");

  return $form->fin(false);
}

?>