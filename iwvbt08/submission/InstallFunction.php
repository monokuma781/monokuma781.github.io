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
 

require_once ("DBInfo.php");

function IsAlreadyInstalled() 
{
  // Try to connect
  $connexion = @mysql_pconnect (SERVER, NAME, PASS);

  if (!$connexion) return false;

  // Try to access the DB given in DBInfo.php
  if (!mysql_select_db (BASE, $connexion)) return false;
  
  // Take the list of tables, check whether table Config exists
  $list_tables = mysql_list_tables(BASE);    
  if (!$list_tables) return false;
  
  $configExist=false;
  while ($row = mysql_fetch_row($list_tables)) {
    if (($row[0]=="config") or ($row[0]=="Config")) {$configExist=true;}
  }
  
  mysql_free_result($list_tables);
  if (!$configExist) return false;  
  
  $result = mysql_query("select * from Config;");

  if (mysql_num_rows($result)==0) return false;
  
  return true;
}

?>
