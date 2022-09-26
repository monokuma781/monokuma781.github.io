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
 
 
// Simple script to print informations about a paper

// Load the libraries
require_once ("Util.php");

$db = new BD (NAME, PASS, BASE, SERVER);
$config=GetConfig($db);

// First check for access rights.
$idSession = $_REQUEST['idSession'];
$session = CheckAccess ("PdfShowInfos.php", $_POST, 
			$idSession, $db, $tpl);

if (is_object($session))
{
  $idPaper = $_REQUEST['idPaper'];  

  // Check the context to determine what must be printed
  if (IsAdmin($session->email, $db))
    {
      // It is an administrator. Print everything
      PrintAllReviews($idPaper, $idSession);     
    }
  else if (GetReview($idPaper, $session->email, $db))
     {
       // It is a reviewer, check the 'allReviews' parameter
       if (isSet($_REQUEST['allReviews']))
	 if ($_REQUEST['allReviews'])
	   {
	     PrintAllReviews($idPaper, $idSession); 
	   }
	 else
	   {
	     PrintReview($idPaper, $session->email, $idSession);
	   }
     }
}
else
{
  echo "You do not have access to this page";
}
?>