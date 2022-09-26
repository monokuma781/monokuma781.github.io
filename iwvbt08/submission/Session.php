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
 
 
require_once ("Formulaire.class.php");

// Search a session

function GetSession ($idSession, $bd) 
{
  $query =  "SELECT * FROM Session WHERE idSession = '$idSession' " ;
  $resultat = $bd->execRequete ($query);
  return $bd->objetSuivant ($resultat);
}

// Vérification qu'une session est valide

function SessionValide ($session, $bd)
{
  // Vérifions que le temps limite n'est pas dépassé

  $maintenant = date ("U");
  if ($session->tempsLimite < $maintenant)
    {
      // Destruction de la session
      session_destroy();

      $requete  = "DELETE FROM Session "
	. "WHERE idSession='$session->idSession'";
      $resultat = $bd->execRequete ($requete);
      return FALSE;
    }
  else // C'est bon !
    return TRUE;
}

// Tentative de création d'une session

function CreerSession ($bd, $email, $motDePasse, $idSession)
{
  $internaute = GetMember ($email, $bd);
  $config = GetConfig($bd);

  // Does the PC member exists?
  if ($internaute)
    {
      // Check the password
      if (PWDMember ($email, $config['passwordGenerator']) 
	  == $motDePasse)
	{
	  // Insert in Session, for 2 hours
	  $maintenant = date ("U");
	  $tempsLimite = $maintenant + 7200; 
	  $roles = $internaute['roles'];
	  $insSession = "INSERT INTO Session (idSession, email, "
	    . "tempsLimite, roles) VALUES ('$idSession', "
	    . "'$email', '$tempsLimite', '$roles')";       
	  $resultat = $bd->execRequete ($insSession);

	  return "";
	}        
      return "<B>Invalid password ! <P></B>\n";
    }      
  else
    {
      return "<B>No user $email !</B><P>\n";
    }
}

function FormIdentification($nomScript, $emailDefaut="")
{
  // Demande d'identification
  $form = new Formulaire ("POST", "$nomScript");
  $form->debutTable(); 
  $form->champTexte("Your email", "email", "$emailDefaut", 30, 60);
  $form->champMotDePasse ("Your password", "motDePasse", "", 30);
  $form->finTable();
  $form->champValider ("Log in", "ident");
  $form->champValider ("Send me my password", "sendpwd");
    
  return $form->fin(false);
}

// Fonction de contrôle d'accès
function CheckAccess ($nomScript, $infoLogin, $idSession, 
		      $bd, &$tpl)
{
  $sessionCourante = GetSession ($idSession, $bd);

  // Cas 1: Vérification de la session courante
  if (is_object($sessionCourante))
    {
      // La session existe. Est-elle valide ?
      if (SessionValide ($sessionCourante, $bd))
	{
	  // Reinitialize the validity period
	  $maintenant = date ("U");
	  $tempsLimite = $maintenant + 7200; 
	  $bd->execRequete ("UPDATE Session SET tempsLimite='$tempsLimite' "
			    . "WHERE idSession = '$idSession'");
	  // On renvoie l'objet session
	  return $sessionCourante;
	}
      else 
	{
	  if (isSet($infoLogin['email']))
	    $email = $infoLogin['email'];
	  else
	    $email = "";
	  $tpl->set_var ("BODY", 
			 "<B>Your session is no longer valid.<P></B>\n"
			 .     FormIdentification($nomScript, $email));
	  return null;
	}
    }
   
  // Cas 2.a: pas de session mais email et mot de passe
     
  if (isSet($infoLogin['email']))
    {
      if (isSet($infoLogin['sendpwd'])) {
	$email=$infoLogin['email'];
	$internaute=GetMember($email,$bd);
	if ($internaute) {
	  $config = GetConfig($bd);
	  $password = PWDMember ($email, $config['passwordGenerator']);
	  $mailTpl = new Template (".");
	  $mailTpl->set_file (array("MailSendPwd" => TPLDIR . "MailSendPwd.tpl"));
	  $mailTpl->set_var("NAME_USER", $internaute['firstName']." ".$internaute['lastName']);
	  $mailTpl->set_var("EMAIL_USER", $email);
	  $mailTpl->set_var("PASSWORD_USER", $password);
	  $mailTpl->parse ("MAIL_BODY", "MailSendPwd");	    
	  $mailTpl->set_var ("CONF_ACRONYM", $config['confAcronym']);	    

	  SendMail ($email,"MyReview login/password", $mailTpl->get_var("MAIL_BODY"),
		    $config['chairMail'], $config['chairMail'], $config['chairMail']);

	  $tpl->set_var ("BODY", 
			 "<B>Your password has been sent to your email</B><P>\n"		
			 . FormIdentification($nomScript, $infoLogin['email']));
	  return null;
	}
	else {
	  $tpl->set_var ("BODY", "<B>No user ".$infoLogin['email']." !</B><P>\n"
			 . "<CENTER><B>Identification failed.</B></CENTER>\n"
			 .     FormIdentification($nomScript, $infoLogin['email']));
	  return null;
	}
      }
      elseif (($message = CreerSession ($bd, $infoLogin['email'], 
					$infoLogin['motDePasse'], $idSession)) == "")
	{
	  // On renvoie l'objet session 
	  return GetSession ($idSession, $bd);
	}
      else 
	{
	  $tpl->set_var ("BODY", $message
			 . "<CENTER><B>Identification failed.</B></CENTER>\n"
			 .     FormIdentification($nomScript, $infoLogin['email']));
	  return null;
	}
    }
    
  // Cas 2.b : print the login form, with the default email
  $tpl->set_var ("BODY", 
		 FormIdentification($nomScript, "")
		 . "Forgot your password? Enter your email and "
		 . "use the 'Send password' button."
		 );
    
}
?>
