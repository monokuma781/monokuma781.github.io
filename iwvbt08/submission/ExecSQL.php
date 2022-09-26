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
 
   require_once ("Util.php");

// Execute any SELECT query and put the result in a template

function ExecQuery($query, &$tpl)
{
  // Connect to the DB with restricted rights
  $db = new BD (SQLUser, pwdSQL, BASE, SERVER);

  // Remove any /
  $query = stripSlashes ($query);
  $result = $db->execRequete ($query);

  // Problème ? On affiche le message d'erreur
  if (!$result or $db->enErreur())
  {  
    $tpl->set_var("SQL_RESULT", mysql_error());
    return;
  }  

  // Create the result
  $nbLines = 0;
  $lines = "";
  while ($line = $db->tableauSuivant ($result))
  {
    // Before the first line, show the result header
    if ($nbLines == 0)
    {
      // Show the attribute names
      $nbAttr = $db->nbrAttributs ($result);
      $header = "";
      for ($i=0; $i < $nbAttr; $i++) 
	{
	  $header .= "<th>" . $db->nomAttribut ($result, $i) . "</th>";
	}
      $header = "<tr class='header'>$header</tr>\n";
    }
  
    // Print each line
    if ($nbLines % 2 == 0)
      $class = " class='even'";
    else
      $class = " class='odd'";
      
    $lines .= "<tr $class>";
    for ($i=0; $i < $nbAttr; $i++) 
    {
      if ($line[$i] == "") $line[$i] = "NULL";
      $lines .= "<td>" . $line[$i] . "</td>";
    }
    $lines .= "</tr>\n";
    $nbLines++;
  }

  if ($nbLines == 0)
  {
    $tpl->set_var("SQL_RESULT", "Empty result");
  }
  else
    {
      $tpl->set_var("LINES", $header . $lines);
      $tpl->parse("SQL_RESULT", "RESULT");
    }
}
?>