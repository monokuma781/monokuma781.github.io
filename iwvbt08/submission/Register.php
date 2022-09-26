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
 
// Registration page

// Load the libraries
require_once ("Util.php");
require_once ("template.inc");
require_once ("Formulaire.class.php");
require_once ("IhmPerson.class.php");

// Instanciate a template object
$tpl = new Template (".");
// Connect to the database
$db = new BD (NAME, PASS, BASE, SERVER);
$config=GetConfig($db);

// Set the standard conf. infos
SetStandardInfo ($db, $tpl);

// Load the required files and assign them to variables
$tpl->set_file ( array ("Page" => TPLDIR . "Page.tpl",
			"register" => TPLDIR . "TxtRegister.tpl",
			"invoice" => TPLDIR . "TxtInvoice.tpl",
			"MailCancelReg" => TPLDIR . "MailCancelReg.tpl",
			"MailConfirmReg" => TPLDIR . "MailConfirmPayment.tpl")
		 );

// Assignment of the template variables
$tpl->set_var("TITLE", $TEXTS->get("TTL_REGISTRATION"));

// Creation de l'interface sur la table Person
$ihm = new IhmPerson ("Person", $db, "Register.php?register=1");
$ihm->setAutoIncrementedKey ("id");

// Any action required? 
if (isSet($_REQUEST['ihm_action'])) 
  $action = $_REQUEST['ihm_action'];
else 
  $action = "";
$output="";

switch ($action)
{
 case INS_BD:
   // Check the input data
   if (!$ihm->controle ($_REQUEST, $messages))
     {
       foreach ($messages as $m) 
	 $output .="<li><font color='red'>$m</font></li>";
       $output = "<ol>$output</ol>\n";
       $output .= $ihm->formulaire(INS_BD, $_REQUEST);
     }
   else  if ($ihm->insertion($_REQUEST))
     {
       $id_person = $db->idDerniereLigne();
       $ihm->insertChoices ($id_person, $_REQUEST['questions']);
       $person = GetPerson ($id_person, $db, "object");

       // Show the invoice
       $output = ShowInvoice ($person, $db, $tpl, "TxtInvoice.tpl");

       // Send and email
       $confirm = ShowInvoice ($person, $db, $tpl, 
			       "MailConfirmRegistration.tpl");
       SendMail ($person->email, $TEXTS->get("SUBJ_CONFIRM_REG"),
		 $confirm, $config['confMail']);
      }
   break;
   	
 case EDITER:
   // On a demandé l'accès à une ligne en mise à jour
   $ligne  = $ihm->chercheLigne ($_REQUEST);
   $output .= $ihm->formulaire(MAJ_BD,$ligne);
   break;
   
 case "paypal_cancel":
   $person = GetPerson ($_REQUEST['registration'], $db, "object");
   InstantiatePersonVars ($person, $tpl, $db);

   // Delete the registration.
   // Security problem: how can we check that this is REALLY paypal?
   //   $db->execRequete ("DELETE FROM Person "
   //	     . "WHERE id = '$person->id'");
 
   $tpl->parse ("CANCEL_REG", "MailCancelReg");
   SendMail ($config['chairMail'], "Paypal payment concellation",
	     $tpl->get_var("CANCEL_REG"));
   $output = $TEXTS->get("TXT_CANCEL_REG");
   break;

 case "paypal_paid":
   $person = GetPerson ($_REQUEST['registration'], $db, "object");
   InstantiatePersonVars ($person, $tpl, $db);
 
   // Set the payment info
   $db->execRequete ("UPDATE Person SET payment_received='Y' "
		     . "WHERE id = '$person->id'");
   $tpl->parse ("CONFIRM_REG", "MailConfirmReg");
   SendMail ($config['chairMail'], "Paypal payment confirmation",
	     $tpl->get_var("CONFIRM_REG"));
   $output = $TEXTS->get("TXT_CONFIRM_REG");
   break;

 default:
   $tpl->set_var("FORM_REGISTRATION", $ihm->formulaire(INS_BD,array()));   
   $output = $tpl->parse ("REGISTER_BODY", "register");
}

$tpl->set_var("BODY", $output);

// In any case, print the page
$tpl->pparse("RESULT", "Page");

?>