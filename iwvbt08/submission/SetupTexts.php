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
 

$messages = 
array(
      "ERROR_MISSING_SERVER" => "Server name must be set",
      "ERROR_MISSING_DBNAME" => "Database name must be set",
      "ERROR_MISSING_LOGIN" => "Admin login must be set",
      "ERROR_MISSING_PASSWORD" => "Admin password must be set",
      "ERROR_MISSING_EMAIL" => "You must give a valid admin email address",
      "ERROR_MISSING_FIRSTNAME" => "Admin first name must be set",
      "ERROR_MISSING_LASTNAME" => "Admin last name must be set",
      "ERROR_MISSING_AFFILIATION" => "Admin affiliation must be set",
      "ERROR_MISSING_PWDGEN" => "Password generator must be set",
      "ERROR_MISSING_UPDIR" => "Uploaded papers directory must be set",		  
      "CONNECT_ERROR" => "Sorry, unable to connect to",
      "CHECK_DB_FIELDS" => "Please check server name and admin login and password.",
      "DB" =>"Sorry, unable to access to the DB",
      "CHECK_DB_NAME" => ". Please check database name.",
      "ALREADY_INSTALL" => "The MyReview system is already installed!",
      "DBINFO_EXIST" => "File <b>DBInfo.php</b> not found. DBInfo.php must exist in your installation directory.",
      "DBINFO_READ" => "File <b>DBInfo.php</b> must be readable. Please change permissions.",
      "DBINFO_WRITE" => "File <b>DBInfo.php</b> must be writable. Please change permissions.",
      "DIR" => "Directory",
      "UPLOAD_DIR_EXIST" => "must exist. Please create it in your installation directory.",
      "UPLOAD_DIR_READ" => "must be readable. Please change permissions.",
      "UPLOAD_DIR_EXE" => "must be executable. Please change permissions.",
      "UPLOAD_DIR_WRITE" => "must be writable. Please change permissions.",
      "INVALID_EMAIL" => "is not a valid email adress. Please check admin email.",
      "MAGIC_QUOTES" => "magic_quote_gpc directive must be actived. Please check your php config.",
      "FILE_UPLOADS" => "file_uploads directive must be actived. Please check your php config.",
      "DB_PERMISSIONS" => "Tables cannot be created."
);
?>