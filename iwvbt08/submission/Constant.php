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
  
define ("VERSION", "1.9.3"); 
  
// Some constants 
define ("HORIZONTAL", "H"); 
define ("VERTICAL", "V"); 
 
define ("INSERTION", "insert"); 
define ("MAJ", "maj"); 
 
// Format for date output: see the PHP documentation (Date() function) 
define ("OUTPUT_DATE_FORMAT", "F d, Y"); 
 
define ("TPLDIR", "./templates/"); 
 
define ("MAX_AUTHORS", 8); 
 
// Administrator actions 
define ("LIST_PC_MEMBERS", 1); 
define ("LIST_PAPERS", 2); 
define ("STATUS_OF_PAPERS", 3); 
define ("CONFIGURE", 4); 
define ("LIST_ACCEPTED_PAPERS", 5); 
define ("CREATE_VOTE", 6); 
define ("COMPUTE_PREDICTION", 7); 
define ("TOPICS", 8); 
define ("CRITERIAS", 9); 
define ("SUMMARY_ASSIGNMENT", 10); 
define ("COMPUTE_ASSIGNMENT", 11); 
define ("CLOSE_SUBMISSION", 12); 
define ("PDF_CONFIG_PARAMS", 14); 
define ("QUERY", 13); 
define ("PDF_SELECT_PAPERS_WITHOUT_REVIEWS", 15); 
define ("PDF_SELECT_PAPERS_WITH_REVIEWS", 16); 
define ("PAPER_STATUS_CODES", 17); 
define ("CLOSE_SELECTION", 18); 
define ("LIST_AUTHORS", 19); 
define ("CONF_SESSIONS", 20); 
define ("ASSIGN_CR_PAPERS", 21); 
define ("CONF_PROGRAM", 22); 
define ("CONF_SLOTS", 23); 
define ("LATEX_OUTPUT", 24); 
define ("REVIEW_QUESTIONS", 25); 
define ("PAPER_QUESTIONS", 26); 
define ("REGISTRATION_QUESTIONS", 27); 
define ("PAYMENT_MODES", 28); 
define ("REGISTRATION_LIST", 29); 
 
define('FPDF_FONTPATH','font/'); 
 
// Codes for messages 
define ("PWD_REVIEWER", "1"); 
define ("REVIEWS_TO_REVIEWERS", "2"); 
define ("STATUS_TO_AUTHORS", "3"); 
define ("FREE_MAIL", "4"); 
define ("MAIL_SELECT_TOPICS", "5"); 
define ("MAIL_RATE_PAPERS", "6"); 
define ("MAIL_PARTICIPATE_FORUM", "7"); 
define ("ALL_REVIEWERS", "All reviewers"); 
define ("ALL_AUTHORS_FREE", "All authors"); 
define ("ALL_AUTHORS", "Notification to all authors"); 
define ("ALL_AUTHORS_ACCEPTED", "Authors of accepted papers"); 

// Rating parameters 
// How many papers must be rated in one pass? 
define ("SIZE_RATING", 20); 
// How many papers shown in one page of the forum? 
define ("SIZE_FORUM", 20); 
 
// Set the maximal size of the manual assignment table 
define ("MAX_ITEMS_IN_ASSIGNMENT", 20); 
 
// Maximal capacity of PHP matching algorithm  
define ("MAX_PAPERS_IN_ROUND", 150); 
 
// How many authors shown simultaneously? 
define ("SIZE_AUTHORS_GROUP", 20); 
define ("MAX_RATING", 5); 
define ("RATE_DEFAULT_VALUE", 2); 
define ("RATE_THRESHOLD", 2); 
define ("CONFIDENCE_THRESHOLD", 0.2); 
define ("UNKNOWN_RATING", "-1"); 
 
// How many registrations shown simultaneously? 
define ("SIZE_REGISTRATIONS_GROUP", 2); 
 
// Prefix of papers names 
define ("PAPER_PREFIX", "p"); 
 
// No reviewer 
define ("NOBODY", "Nobody"); 
 
// Scale for rating papers 
$SCALE = array ("7"=> "Strong Accept", 
		"6" => "Accept", 
		"5" => "Weak Accept",  
		"4" => "Neutral ", 
		"3" => "Weak Reject",  
		"2" => "Reject", 
		"1" => "Strong Reject"); 
 
// Scale for rating papers 
$EXPERTISE = array ("1"=> "Low", 
		      "2" => "Medium", 
		      "3" => "High"); 
 
// Status of papers 
define ("ACCEPT", 'A'); 
define ("REJECT", 'R'); 
 
// Minimal difference to consider that a paper is in conflict 
define ("CONFLICT_GAP", "3"); 
 
// Selection of papers 
define ("NEUTRAL_CHOICE", "Any"); 
define ("SP_ANY_STATUS", "0"); 
define ("SP_ANY_CHOICE", "0"); 
define ("SP_NOT_YET_ASSIGNED", "999"); 
define ("SP_ABOVE_FILTER", "1"); 
define ("SP_BELOW_FILTER", "2"); 
 
// Constants for the discussion mode code 
define ("NO_DISCUSSION", 1);  
define ("LOCAL_DISCUSSION", 2);  
define ("GLOBAL_DISCUSSION", 3);  
 
// Constants for the ballot  mode code 
define ("TOPIC_BASED_BALLOT", 1);  
define ("GENERAL_BALLOT", 2);  
 
// Paypal payment mode 
define ("PAYPAL", "1"); 
 
$FILE_TYPES = array ("pdf" => "PDF", "ps" => "Postscript", 
		     "doc" => "Word", "zip" => "Zip" ); 
 
$ROLES = array ("A" => "Admin", "C" => "Chair", "R" => "Reviewer"); 
 
// Generic code for accepted papers (whatever the specific status) 
define ("CAMERA_READY_STATUS", 99999); 
 
?>