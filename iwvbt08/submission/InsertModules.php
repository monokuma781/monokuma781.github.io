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

function InsertPaper ($paper, $file, $fileRequired, $mode, &$TEXTS, $db)
{
  // Trim all the values
  TrimArray($paper);

  // Check the informations
  $message = CheckPaper ($paper, $file, $fileRequired, $TEXTS, $db);

  if (count($message) > 0) return $message;

  // Access to the database
  $id = SQLPaper ($paper, $file, $mode, $db);

  return  $id;
} 

// Insert PC members
function InsertPCMember ($member, $mode)
{
  // Check the informations
  $message = CheckPCMember ($member);

  if (!empty($message)) return $message;

  // Connect to the database
  $db = new BD (NAME, PASS, BASE, SERVER);

  // Access to the database
  SQLMember ($member, $mode, $db);

  return ""; 
} 

// Assign reviewers to paper
function InsertReviewers ($idPaper, $tabMails, $db)
{
  // Is there something to check ?

  // Access to the database
  SQLReview ($idPaper, $tabMails, $db);

  return ""; 
} 

// Update reviews
function UpdateReview ($review, $db)
{
  // Check the review?

  // Access to the database
  SQLUpdateReview ($idPaper, $tabMails, $db);
} 
?>