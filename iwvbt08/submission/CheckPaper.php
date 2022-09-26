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

// This script allows authors to check that a paper
// is correctly uploaded

// Load the libraries
require_once ("Util.php");
require_once ("template.inc");

// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
$config = GetConfig($db);

// Check for access rights
if (isSet($_REQUEST['idPaper']) and isSet($_REQUEST['password'])) 
{
  $idPaper = $_REQUEST['idPaper'] ;
  $password = $_REQUEST['password'];
  
  // Check that the paper exists!
  $paper = GetPaper ($idPaper, $db, "object");
  if (!is_object($paper)) {
    echo "<script language='JavaScript1.2'>alert('No paper $idPaper found'); "
      . "</script>";
  }
  else 
    {
      // Check the password
      $true_password = PWDPaper($idPaper,$config['passwordGenerator']);
      if ($true_password != $password)
	{	
	  echo "<script language='JavaScript1.2'>alert('invalid password');"
	    . "</script>";
	}
      else
	{
	  // Everything is OK: send the file
	  $type = "application/octet-stream";
	  $file = "./".FNamePaper ($config['uploadDir'], $idPaper, 
				   $paper->format);   

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
    }
}
else
echo "<script language='JavaScript1.2'>alert('invalid access to CheckPaper'); "
. "</script>";
?>
