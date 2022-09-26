<?
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
 
 
//Management of codes

require_once ("Util.php");
require_once ("Formulaire.class.php");

function MngtCodes ($_POST, $codeName, $context, $db, &$tpl)
{
    if (isSet($_POST['label']))
      {
	$message = InsertCode ($codeName, $_POST, $_POST['mode'], $db);

	if (!is_numeric($message))
	  {
	    // Error reporting
	    $tpl->set_var("TITLE", "Error: $message");
	    $codif = CleanArray ($_POST);
	    $mode = INSERTION;
	  }
	else
	  {
	    $tpl->set_var("TITLE", "Transaction OK");
	    $mode = INSERTION;
	  }
	// Display the form
	$tpl->set_var("FORM_CODIF", 
		      FormCodif ($codeName, array(), $mode, $context));
      }
    else if (isSet($_GET['id']))
      {
	if (!isSet ($_GET['do']) or $_GET['do']=="modify")
	  {
	    // Ask for code modification
	    $tpl->set_var("TITLE", "Modify");
	    $codif = ChercheCodif ($codeName, $_GET['id'], $db);
	    $tpl->set_var ("FORM_CODIF", 
		   FormCodif($codeName, $codif, MAJ, $context));
	  }
	else
	  {
	    // Delete the code
	    $query = "DELETE FROM $codeName WHERE id=" . $_GET['id'];
	    $db->execRequete ($query);
	    $tpl->set_var ("FORM_CODIF", 
		   FormCodif($codeName, array(), INSERTION, $context));
	  }
      }
    else
      {
	// Insert
	$tpl->set_var("TITLE", "Add a code in table $codeName");
	$tpl->set_var ("FORM_CODIF", 
		   FormCodif($codeName, $_POST, INSERTION, $context));
      }

    // Always print the list of existing codes

    $tpl->set_block("TxtCodes", "DETAIL_CODIF", "CODIFS");
    $tpl->set_var ("CODIFS", "");

  $query = "SELECT * FROM $codeName";
  $result = $db->execRequete ($query);
  while ($code = $db->objetSuivant($result))
    {
      $modLink = Ancre ($context . "&id=$code->id&do=modify", "Modify");
      $delLink = Ancre ($context . "&id=$code->id&do=delete", "Delete");
      $tpl->set_var("CODIF_LABEL", $code->label);
      $tpl->set_var("MODIFY", $modLink);
      $tpl->set_var("DELETE", $delLink);
	  
      $tpl->parse("CODIFS", "DETAIL_CODIF", true);
    }

  $tpl->set_var("ADD_CODE", Ancre($context, "Add a new code"));

  /* Instanciate CODIFS in BODY */
  $tpl->parse("BODY", "TxtCodes");
}

function InsertCode ($nomCodif, $codif, $mode, $db)
{
  // Controle
  $message = ControleCodif ($codif);

  if (!empty($message)) return $message;

  // Insertion ou mise à jour
  $code = SQLCodif ($nomCodif, $codif, $mode, $db);

  return  $code;
} 

function FormCodif ($nomCodif, $codif, $mode, $target)
{
  // Create the form
  $form = new Formulaire ("POST", $target, false);
   
  $form->champCache ("mode", $mode);

  if ($mode != INSERTION)
    { 
      $form->champCache ("id", $codif['id']);
    }
  else
    $codif['label'] = "";
  
  $form->champTexte ("Label", 
		     "label", $codif['label'], 30, 30);

  if ($mode == MAJ)
    $form->champValider ("Modify", "submit");
  else
    $form->champValider ("Insert", "submit");

  return $form->fin(false);
}

  // Insérer une codification
  function SQLCodif ($nomCodif, $codif, $mode, $db)
  {  

    // Get the variables (easier)
    $lib = $codif['label'];

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

function ControleCodif($codif)
{
  $message = "";
  
  // Quelques tests...

  if (empty ($codif['label']))  $message = "Please provide a label<br>";
 
  return $message;
}

// Get a code
  function ChercheCodif ($nomCodif, $code, $db) 
  {
    $query = "SELECT * FROM $nomCodif WHERE id = '$code'" ;
    $result = $db->execRequete ($query);
    return $db->ligneSuivante ($result);
  }

// Get the text of a code
  function LibelleCodif ($nomCodif, $code, $db) 
  {
    $query = "SELECT * FROM $nomCodif WHERE id = '$code'" ;
    $result = $db->execRequete ($query);
    $codif = $db->ligneSuivante ($result);
    return $codif['label'];
  }

?>
