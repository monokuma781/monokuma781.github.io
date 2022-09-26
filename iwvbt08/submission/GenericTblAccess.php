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
 
 
// Generic code to generate an interface for any table

require_once ("Util.php");
require_once ("Formulaire.class.php");

function GenericTblAccess ($post, $tableName, $context, $db, &$tpl)
{
  $tpl->set_file("Generic", TPLDIR . "TxtGeneric.tpl");
  $tpl->set_var("FEEDBACK", "Table $tableName");

  if (isSet($post['mode']))
    {
      // Insert in the table
      $message = GenericInsertTable ($tableName, $post, $post['mode'], $db);

      if (!is_numeric($message))
	{
	  // Error reporting
	  $tpl->set_var("FEEDBACK", "Error: $message");
	  $codif = CleanArray ($post);
	  $mode = INSERTION;
	}
      else
	{
	  $tpl->set_var("FEEDBACK", "Transaction OK");
	  $mode = INSERTION;
	}
      // Display the form
      $tpl->set_var("FORM_ROW", 
		    GenericForm ($tableName, array(), $mode, $context, $db));
    }
  else if (isSet($_GET['id']))
    {
      if (!isSet ($_GET['do']) or $_GET['do']=="modify")
	{
	  // Ask for code modification
	  $tpl->set_var("FEEDBACK", "Modify");
	  $row = GenericGetRow ($tableName, $_GET['id'], $db);
	  $tpl->set_var ("FORM_ROW", 
			 GenericForm($tableName, $row, MAJ, $context, $db));
	}
      else
	{
	  // Delete the code
	  $query = "DELETE FROM $tableName WHERE id=" . $_GET['id'];
	  $db->execRequete ($query);
	  $tpl->set_var ("FORM_ROW", 
			 GenericForm($tableName, array(), INSERTION, $context, $db));
	}
    }
  else
    {
      // Insert
      $tpl->set_var("FEEDBACK", "Add a row in table $tableName");
      $tpl->set_var ("FORM_ROW", 
		     GenericForm($tableName, $post, INSERTION, $context, $db));
    }

  // Always print the list of existing codes

  $tpl->set_block("Generic", "DETAIL_ROW", "ROWS");
  $tpl->set_var ("ROWS", "");
  $tpl->set_var("HEADERS", "");
 
  $query = "SELECT * FROM $tableName";
  $result = $db->execRequete ($query);
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
	}
      $tpl->set_var("HEADERS", $header);
	
      // Print each line
      for ($i=0; $i < $nbAttr; $i++) 
	{
	  if ($line[$i] == "") $line[$i] = "NULL";
	  $lines .= "<td>" . String2HTML($line[$i]) . "</td>";
	}
      $tpl->set_var("ROWDATA", $lines);
	
      $modLink = Ancre ($context . "&id=" . $line[0] . 
			"&do=modify", "Modify");
      $delLink = Ancre ($context . "&id=" . $line[0] .
			"&do=delete", "Delete");
      $tpl->set_var("MODIFY", $modLink);
      $tpl->set_var("DELETE", $delLink);
      if ($nbLines %2 == 0)
	$tpl->set_var("CSS_CLASS", "even");
      else
	$tpl->set_var("CSS_CLASS", "odd");
      $nbLines++;
      $lines = "";
	
      $tpl->parse("ROWS", "DETAIL_ROW", true);
    }
    
  $tpl->set_var("ADD_ROW", Ancre($context, "Add a new code"));
    
  /* Instanciate ROWS in BODY */
  $tpl->parse("BODY", "Generic");
}

function GenericInsertTable ($tableName, $row, $mode, $db)
{
  // Controle
  $message = GenericCheck ($row);

  if (!empty($message)) return $message;

  // Create the list of fields and the list of values
  $fieldsList = $valList = "";
  $comma = "";
  foreach ($row as $field => $val)
    {
      $val = $db->prepareString($val);
      if ($field != "submit" and $field != "id" and $field!="mode")
	{
	  if ($mode == INSERTION)
	    {
	      $valList .= $comma . "'$val'";
	      $fieldsList .= $comma . $field;
	    }
	  else
	    {
	      $fieldsList .= $comma . "$field='$val'";      
	    }
	  $comma = ", ";
	}
    }
  
  if ($mode == INSERTION)  
    {
      $query = "INSERT INTO $tableName ($fieldsList) "
	. "VALUES ($valList) ";
      $db->execRequete ($query);
      $id = $db->idDerniereLigne();
    }
  else  
    {
      $id = $row['id'];
      $query = "UPDATE $tableName SET $fieldsList "
	. " WHERE id='$id'";
      $db->execRequete ($query);
    }
  return $id;
} 

function GenericForm ($tableName, $row, $mode, $target, $db)
{
  // Create the form
  $form = new Formulaire ("POST", $target, false);
   
  $form->champCache ("mode", $mode);

  if ($mode != INSERTION)
    { 
      $form->champCache ("id", $row['id']);
    }
  
  $form->debutTable();

  // Scan the attributes, and create an input field  
  $fields = mysql_list_fields($db->base, $tableName, $db->connexion);
  $columns = mysql_num_fields($fields);

  for ($i = 0; $i < $columns; $i++) 
    {
      if (mysql_field_name($fields, $i) != "id")
	{
	  $name = mysql_field_name($fields, $i);
	  $length  = mysql_field_len($fields, $i);

	  if (!isSet($row[$name])) $row[$name] = "";

	  $form->champTexte (ucfirst($name), $name, $row[$name], 
			     $length, $length);
	}
    }
  $form->finTable();

  if ($mode == MAJ)
    $form->champValider ("Modify", "submit");
  else
    $form->champValider ("Insert", "submit");

  return $form->fin(false);
}

// Insérer une codification
function GenericSQL ($nomCodif, $codif, $mode, $db)
{  

  // Get the variables (easier)
  $lib = $db->prepareString($codif['label']);

  if ($mode == INSERTION)  
    {
      // Insert
      $query = "INSERT INTO $nomCodif (label) "
	. "VALUES ('$lib') ";
      $db->execRequete ($query);
      $code = $db->idDerniereLigne();
    }
  else  
    {
      // Update
      $code = $codif['id'];
      $query = "UPDATE $nomCodif SET label='$lib' "
	. " WHERE id='$code'";
      $db->execRequete ($query);
    }

  return $code;
}

function GenericCheck($codif)
{
  $message = "";
  
  // Test? 

 
  return $message;
}

// Recherche d'une codif
function GenericGetRow ($tableName, $id, $db) 
{
  $query = "SELECT * FROM $tableName WHERE id = '$id'" ;
  $result = $db->execRequete ($query);
  return $db->ligneSuivante ($result);
}

?>
