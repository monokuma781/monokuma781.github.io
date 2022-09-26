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

// This script is used to download a paper. It hides
// the details regarding the name of the paper,  and
// the subdirectory where it is stored.

 session_start(); 

// Load the libraries

require_once ("Util.php");
require_once ("template.inc");

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
$config = GetConfig($db);

// Check for access rights

$session = CheckAccess ("Review.php", $_POST, session_id(), $db, $tpl);

if (is_object($session))
{
  // Actions if the id of a paper is submitted
    if (isSet($_REQUEST['idPaper']))
      {
	$idPaper = $_REQUEST['idPaper'];
	$paper = GetPaper($idPaper, $db, "object");

        // Check that the paper is REALLY assigned to the reviewer
	if ($review = GetReview($idPaper, $session->email, $db))
	  {	   
	    $type = "application/octet-stream";
	    $file = "./".FNamePaper ($config['uploadDir'], $idPaper, $paper->format);   

	    header("Content-disposition: attachment; filename=paper"
		    . $idPaper . "." . $paper->format);
	    header("Content-Type: application/force-download");
	    header("Content-Transfer-Encoding: $type\n");
	    header("Content-Length: ".filesize($file));
	    header("Pragma: no-cache");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
	    header("Expires: 0");
	    readfile_chunked($file); 
	  }
	else echo "No right on this paper";
      }
    else echo "Not connected";
} 
else echo "Not connected";
?>
