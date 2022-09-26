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

$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
			"TxtTblTemplates" => TPLDIR . "TxtTblTemplates.tpl"));

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Assignment of the template variables
$tpl->set_var("TITLE", "Template edition");

$config = GetConfig ($db);

// First check for access rights
 $session = CheckAccess ("Admin.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  if (IsAdmin($session->email, $db))
  {
    $templates=array();
    $template="";
    $body="";
    $contents="";
    if ($handle = opendir(TPLDIR)) {      
      while (false !== ($file = readdir($handle))) {
	if (preg_match("/.tpl$/i", $file)) {
	  $templateName=ereg_replace(".tpl","",$file);
	  $templates[$file]=$templateName;
	  }
      }
      closedir($handle);      
    }
   
    $tpl->set_block("TxtTblTemplates", "TPL_DETAIL", "TEMPLATES");
    $tpl->set_var("TEMPLATES","");
    $tpl->set_var("MESSAGE","");
    $tpl->set_var("TPL_FORM","");
    $i=0;
    foreach($templates as $file=>$name)
      {
	// Choose the CSS class
	if ($i++ %2 == 0)
	  $tpl->set_var("CSS_CLASS", "even");
	else
	  $tpl->set_var("CSS_CLASS", "odd");

	$tpl->set_var("TPL_NAME", $name);
	$tpl->set_var("TPL_COMMENTS", comments($file));
	$tpl->set_var("TPL_FILE", $file);
	if (is_writable(TPLDIR."/".$file)) 
	  //$tpl->set_var("TPL_MODIF", "<a href=\"#\" onClick=\"ShowWindow('EditTemplate.php?selection=$file');\">Modify</A>");
	  $tpl->set_var("TPL_MODIF", "<a href=\"EditTemplate.php?selection=$file\">Modify</A>");
	else $tpl->set_var("TPL_MODIF", "<font color=#FF0000>No Writable</font>");
	$tpl->parse("TEMPLATES", "TPL_DETAIL", true);
      }
    
    if (isSet($_GET['selection'])) {
      $template=$_GET['selection'];   
      $tpl->set_var("TITLE", "Template edition : $template");
      $tpl->set_var("BODY", FormEditTemplate ($template,$contents). "<a href=\"EditTemplate.php\">Back to the list</a>");
    }
    else{
      if (isSet($_POST['edited'])) {
	$contents=$_POST['edited'];
	$template=$_POST['file'];
	$templateFile=TPLDIR."/".$_POST['file'];
	$msg="";
	if (is_writable($templateFile)) {
	  $contents=stripslashes($contents);
	  if (!$handle = fopen($templateFile, 'w')) $msg="<b>Error</b> : $templateFile can't be open";	  	
	  else if (fwrite($handle, $contents) === FALSE) $msg="<b>Error</b> : $templateFile can't be write";
	  else $msg="Your modifications are saved.";
	  fclose($handle);
	}
	else $msg="Error : <b>$templateFile</b> must be writable ! Your modifications are <b>not</b> saved.";	
	$body=FormEditTemplate ($template,$contents);
	$tpl->set_var("TITLE", "Template edition : $template");
	$tpl->set_var("BODY", $msg . $body. "<a href=\"EditTemplate.php\">Back to the list</a>");
      }   
      else $tpl->parse("BODY", "TxtTblTemplates");
    }
  }
}
$tpl->pparse("RESULT", "Page");


function comments($file){
  $handle = fopen(TPLDIR."/".$file, 'r');
  $contents = fread($handle, 8192);  
  fclose($handle); 
  preg_match_all("/<!--COMMENTS([^>]+)-->/",
                 $contents,//"bla bla<!--COMMENTS exemple : >bla bfla"
		 $out,
                 PREG_SET_ORDER);
  if (isSet($out[0][1])) return  $out[0][1];
  else return "none";
}

function FormEditTemplate ($template, $contents)
{
  $form = new Formulaire ("POST", "EditTemplate.php");   
  $form->debutTable(); 

  $form->champCache ("file", $template);

  if ($contents=="") {
    $filename = TPLDIR."/".$template;
    $handle = fopen ($filename, "rb");
    $contents = fread ($handle, filesize ($filename));
    fclose ($handle);
  }

  $form->champFenetre ("Template", "edited", htmlentities($contents), 30, 70);
  $form->finTable();
  $form->champValider ("Save", "submit");
  $form->champAnnuler ("Cancel", "cancel");
  return $form->fin(false);
}


?>