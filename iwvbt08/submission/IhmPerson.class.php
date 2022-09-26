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
 
class IhmPerson extends IhmBD
{
  // Le constructeur de la classe. Attention à bien penser
  // à appeler le constructeur de la super-classe.
  
  function IhmPerson($nomTable, $bd, $script="moi")
  {
    global $TEXTS;

    // Appel du constructeur de IhmBD
    parent::IhmBD($nomTable, $bd, $script);
  
    // On peut placer les entêtes dès maintenant
    $this->setEntete("title", $TEXTS->get("FRM_REG_TITLE"));
    $this->setEntete("first_name", $TEXTS->get("FRM_FIRST_NAME"));
    $this->setEntete("last_name", $TEXTS->get("FRM_LAST_NAME"));
    $this->setEntete("position", $TEXTS->get("FRM_REG_POSITION"));
    $this->setEntete("email", $TEXTS->get("FRM_REG_EMAIL"));
    $this->setEntete("phone", $TEXTS->get("FRM_PHONE"));
    $this->setEntete("fax", $TEXTS->get("FRM_FAX"));
    $this->setEntete("affiliation", $TEXTS->get("FRM_AFFILIATION"));
    $this->setEntete("address", $TEXTS->get("FRM_ADDRESS"));
    $this->setEntete("country", $TEXTS->get("FRM_COUNTRY"));
    $this->setEntete("city", $TEXTS->get("FRM_CITY"));
    $this->setEntete("zip_code", $TEXTS->get("FRM_ZIP_CODE"));
    $this->setEntete("requirements", $TEXTS->get("FRM_REQUIREMENTS"));
    $this->setEntete("payment_mode", $TEXTS->get("FRM_PAYMENT_MODE"));
  }

  /*****************   Partie publique ********************/
  // Méthode effectuant des contrôles avant mise à jour 
  function controle(&$ligne, &$messages)
  {
    global $TEXTS;

    // Check that all fields are been given
    if (empty($ligne['email']))
      $messages[]  = $TEXTS->get("MISSING_EMAIL");
    else if (!checkEmail($ligne['email']))
      $messages[] =  $TEXTS->get("INVALID_EMAIL");
    
    if (empty($ligne['title']))
      $messages[]  = $TEXTS->get("MISSING_TITLE");
    if (empty($ligne['first_name']))
      $messages[]  = $TEXTS->get("MISSING_FIRST_NAME");
    if (empty($ligne['last_name']))
      $messages[]  = $TEXTS->get("MISSING_LAST_NAME");
    if (empty($ligne['affiliation']))
      $messages[]  = $TEXTS->get("MISSING_AFFILIATION");
    if (empty($ligne['phone']))
      $messages[]  = $TEXTS->get("MISSING_PHONE");
    if (empty($ligne['fax']))
      $messages[]  = $TEXTS->get("MISSING_FAX");
    if (empty($ligne['address']) or
	empty($ligne['city']) or
	empty($ligne['country']) or
	empty($ligne['zip_code'])
	)
      $messages[]  = $TEXTS->get("MISSING_ADDRESS");

    if (count($messages) == 0) 
      return true;
    else
      return false;
  }

  // Rédéfinition du formulaire
  function formulaire ($action, $ligne)
  {
    global $TEXTS, $CODES;

    $config = GetConfig ($this->bd, "object");

    // Création de l'objet formulaire
    $form = new Formulaire ("POST", $this->nomScript, false);
    $form->setTitle ($this->nomTable);

    $titles = $CODES->get("person_title");

    $form->champCache ("ihm_action", $action);

    // Pas de mise à jour? On calcule la valeur de l'id
    if ($action != MAJ_BD)
      {
	$ligne['id'] = $this->GetNextPersonID();
      }

    $form->champCache ("id",  $ligne['id']);
    $form->champCache ("payment_received",  'N');

    $form->debutTable (VERTICAL,array(),$nbLignes=1, 
		       $TEXTS->get("FRM_REG_FORMTITLE"));

    // Vérifier que la valeur par défaut existe
    foreach ($this->schemaTable as $nom => $options)
      if (!isSet($ligne[$nom])) $ligne[$nom] = "";

    $form->champRadio ($this->entetes['title'], "title",
		     $ligne['title'], $titles);
    $form->champTexte ($this->entetes['last_name'], "last_name",
		       $ligne['last_name'], 30, 60);
    $form->champTexte ($this->entetes['first_name'], "first_name",
		       $ligne['first_name'], 30, 60);
    $form->champTexte ($this->entetes['position'], "position",
		       $ligne['position'], 30, 60);
    $form->champTexte ($this->entetes['affiliation'], "affiliation",
		       $ligne['affiliation'], 30, 60);
    $form->champTexte ($this->entetes['email'], "email",
		       $ligne['email'], 30, 60);
    $form->champTexte ($this->entetes['phone'], "phone",
		       $ligne['phone'], 20, 20);
    $form->champTexte ($this->entetes['fax'], "fax",
		       $ligne['fax'], 20, 20);
    $form->champFenetre ($this->entetes['address'],
		       "address", $ligne['address'], 3, 30);
    $form->champTexte ($this->entetes['city'], "city",
		       $ligne['city'], 20, 50);
    $form->champTexte ($this->entetes['country'], "country",
		       $ligne['country'], 20, 50);
    $form->champTexte ($this->entetes['zip_code'], "zip_code",
		       $ligne['zip_code'], 10, 20);
    $form->champFenetre ($this->entetes['requirements'],
		       "requirements", $ligne['requirements'], 2, 30);

    $payment_modes = GetCodeList ("PaymentMode", 
				   $this->bd, "id", "mode");
    if (empty($ligne['payment_mode']))
      $ligne['payment_mode'] = PAYPAL;
    if (count($payment_modes) > 1)
      $form->champRadio ($this->entetes['payment_mode'], 
		       "payment_mode", 
		       $ligne['payment_mode'], $payment_modes);
    else
      $form->champCache ("payment_mode", PAYPAL);

    // OK now ask the questions!
    $res = $this->bd->execRequete ("SELECT * FROM RegQuestion");
    while ($question = $this->bd->objetSuivant ($res)) {
      // Take the list of possible choices
      $list_choices = array();
      $rc = $this->bd->execRequete("SELECT * FROM RegChoice "
			     . " WHERE id_question='$question->id' "
			     . " ORDER BY position" );
      while ($choice = $this->bd->objetSuivant ($rc)) {
	if (!isSet($id_choice)) $id_choice = $choice->id_choice; 
	$list_choices[$choice->id_choice] = 
	  "$choice->choice ($choice->cost {$config->currency}s)" ;
      }

      // Take the default value
      if (!isSet($ligne['questions'][$question->id]))
	$def_answer = $id_choice ; // the first possible choice is the default
      else 
	$def_answer = $ligne['questions'][$question->id];

      // Create the form question .. if there are choices!
      if (count($list_choices) > 0)
	$form->champRadio ($question->question, 
			   "questions[$question->id]", 
			   $def_answer, $list_choices);
      unset ($id_choice);
    }

    $form->finTable();
	
    if ($action == MAJ_BD)
      $form->champValider ("Modify", "submit");
    else
      $form->champValider ("Register", "submit");
    return $form->formulaireHTML();
  }

  function insertChoices ($id_person, $choices)
  {
    // Loop on the choices
    foreach ($choices as $id_question => $id_choice) {
      $i_pchoice = "INSERT INTO PersonChoice (id_person, "
	. "id_question, id_choice) "
	. " VALUES ('$id_person', '$id_question', '$id_choice') ";

      $this->bd->execRequete ($i_pchoice);
    }
  }

  function GetNextPersonID ()
  {
    $result = $this->bd->execRequete ("SELECT Max(id)+1 AS id FROM Person");
    $o = $this->bd->objetSuivant ($result);
    return $o->id;
  }

  function showInvoice ()
  {
  }

}
?>
