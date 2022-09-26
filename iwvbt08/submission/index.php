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
require_once ("template.inc");
require_once ("Formulaire.class.php");

// Instanciate a template object
$tpl = new Template (".");
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

/* Code for the demo site: change at run time the template. 
   You should remove that for your site.
*/

if (isSet($_POST['template']) and $template = $_POST['template'])
{
  // Copy the chosen template to 'Page.tpl'
  copy (TPLDIR . $template, TPLDIR . "Page.tpl");
}

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
                        "Instructions" => TPLDIR . "Instructions.tpl",
                        "Text" => TPLDIR . "TxtHome.tpl"));

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_HOME_PAGE"));

if (isSet($_GET['authorsInstructions']))
{
  $config = GetConfig ($db);
  $tpl->set_var("SUB_DEADLINE", 
		DBtoDisplay($config['submissionDeadline'],
			    $config['date_format']));
  $tpl->parse("BODY", "Instructions");
}
else
     $tpl->parse("BODY", "Text");

// In any case, print the page
$tpl->pparse("RESULT", "Page");
?>