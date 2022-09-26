<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2004 Philippe Rigaux
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
 
error_reporting(E_ALL);

// Fonction IsAlreadyInstalled
require_once("InstallFunction.php");

if (IsAlreadyInstalled() == false) { 
   include("Setup.php");
   exit;
}
 
// Constants
require_once ("DBInfo.php");
require_once ("Constant.php");

// HTML output
require_once ("HTML.php");  

// Modules and classes
// require_once ("Table.php");
require_once ("BD.class.php");
require_once ("FatalError.php");
require_once ("CText.php");
require_once ("Codes.class.php");
require_once ("IhmBD.class.php");

// Misc functions
require_once ("functions.php"); 
require_once ("AdminLists.php"); 
require_once ("Session.php");
require_once ("ExecSQL.php");
require_once ("ManageReview.php");
require_once ("PaperReviews.php");
require_once ("DisplayReviews.php");
require_once ("Assignment.php");
require_once ("GenericTblAccess.php");

// Forms
require_once ("FormAbstract.php");
require_once ("FormLoginAuthor.php");
require_once ("FormPCMember.php");
require_once ("FormReview.php");
require_once ("FormSendMail.php");
require_once ("FormAssignReviewers.php");
require_once ("FormSelectPapers.php");
require_once ("FormStatus.php");
require_once ("FormMessage.php");
require_once ("FormSelectTopics.php");

// Forms, titles and small texts: moved to templates/ShortTexts.php

// Prediction module
require_once("ComputePrediction.php");

// Database commands
require_once ("InsertModules.php");
require_once ("SQLCommands.php");

// Pdf generator
require_once ("SettingPdf.php");
require_once("FormPdf.php");
require_once("PrintPdf.php");
require_once("latex_docs.php");

// Set some configuration parameters

// Never escape quotes from external files: incompatible with templates
ini_set ("magic_quotes_runtime", "0");
ini_set ("magic_quotes_sybase", "0");

// If automatic escape is on: suppress the slashes. This
// makes MyReview independent from the magic_quotes_gpc value
// which changes so often...

if (get_magic_quotes_gpc())
{
  NormaliseHTTP($_POST); reset($_POST);
  NormaliseHTTP($_GET); reset($_GET);
  NormaliseHTTP($_REQUEST); reset($_REQUEST);
}

// Get the texts of the application. Hope no one will have
// the weird idea to define a variable named TEXTS....
$TEXTS = new CText(TPLDIR . "ShortTexts.xml");
// Get the codes of the application.
$CODES = new Codes(TPLDIR . "Codes.xml");


?>
