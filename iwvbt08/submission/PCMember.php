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

require_once ("template.inc");
require_once ("Util.php");
require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
                         "TxtPostMember" => TPLDIR . "TxtPostMember.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", "Add/modify PC members");

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);

// First check for access rights
 $session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  // After submission, insert
  if (isSet($_POST['email']))
    {
      $message = InsertPCMember ($_POST, $mode);

      // Any error ?
      if (!empty($message))
	{
	  $member = CleanMember($_POST);
	  $tpl->set_var("INFO", 
          "The following errors have been met:<br> $message"
          . "<b>Please correct and submit again.</b>");
	  $tpl->set_var("FormPCMember", 
                  FormPCMember ($member, INSERTION, "PCMember.php"));
	}
      else
	{
	  // Everything is OK. Give the form with the stored values
	  $tpl->set_var("PC_EMAIL", $_POST['email']); 
	  $tpl->parse("INFO", "TxtPostMember");
	  $member = GetMember ($_POST['email'], $db);     
	  $tpl->set_var("FormPCMember", 
                  FormPCMember ($member, MAJ, "PCMember.php")); 
	}
      $tpl->parse("BODY", "INFO");
      $tpl->parse("BODY", "FormPCMember", true);
    }
  else
    {
      // Output the form 
      $tpl->set_var("FormPCMember", 
		    FormPCMember ($member, INSERTION, "PCMember.php"));
      $tpl->parse("BODY", "FormPCMember", true);
    }
}

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>