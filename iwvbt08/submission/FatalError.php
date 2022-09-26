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
 
 
function FatalError ($message, $redirectURL="Admin.php")
{
  $message = "Error: $message Action cancelled";
  echo "<script language='JavaScript1.2'> alert('$message');</script>";

  echo "$message";

  // header ("Location: $redirectURL");
  exit;
}

// Texts for fatal errors

define("FE_FILE_MISSING", "The file %s does not exist.");
define("FE_NO_STATUS", "The paper %d has no status.");
define("FE_PAPERS_WITHOUT_STATUS", "%d papers do not have a status.");
define("FE_MISSING_REVIEWS", "%d reviews are still missing.");
define("FE_DELETE_NONEMPTY_REVIEW", 
               "the review (%s, %d) has been submitted. Cannot delete.");

?>