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
 

// Classe gérant les formulaires

// On a besoin d'instancier des objets Tableau
require_once ("Tableau.class.php");
  
// Début de la classe
class Formulaire
{
  // ----   Partie privée : les propriétés et les constantes

  // Propriétés de la balise <FORM>
  var $methode, $action, $nom, $transfertFichier=FALSE;

  // Propriétés de présentation
  var  $orientation="", $centre=TRUE, $classeCSS, $tableau, $title,
    $nbl_horizontales;

  // Propriétés stockant les composants du formulaire
  var $composants=array(), $nbComposants=0;

  // Constructeur de la classe
  function Formulaire ($methode="POST", 
		       $action="",
		       $centre=true,
		       $classe="Form", $nom="Form")
  {
    // Initialisation des propriétés de l'objet avec les paramètres
    $this->methode = $methode;
    $this->action = $action;
    $this->classeCSS = $classe;
    $this->nom = $nom;
    $this->centre = $centre;
    $this->title = "";
  }

  // ----   Partie privée : les méthodes 


  // Méthode pour créer un champ INPUT général
   function champINPUT ($type, $nom, $val, $taille, $tailleMax)
  {
    // Attention aux problèmes d'affichage
    $val = htmlSpecialChars($val);

    // Création et renvoi de la chaîne de caractères
    return "<INPUT TYPE='$type' NAME=\"$nom\" "
          . "VALUE=\"$val\" SIZE='$taille' MAXLENGTH='$tailleMax'>\n";
  }

  // Champ de type texte
   function champTEXTAREA ($nom, $val, $lig, $col)
  {
    return "<TEXTAREA NAME=\"$nom\" ROWS='$lig' "
      . "COLS='$col'>$val</TEXTAREA>\n";
  }

  // Champ pour sélectionner dans une liste
   function  champSELECT ($nom, $liste, $defaut, $taille=1)
  {
    $s = "<SELECT NAME=\"$nom\" SIZE='$taille'>\n";
    while (list ($val, $libelle) = each ($liste))
      {
	// Attention aux problèmes d'affichage
	$val = htmlSpecialChars($val);
	$defaut = htmlSpecialChars($defaut);

        if ($val != $defaut)
	  $s .=  "<OPTION VALUE=\"$val\">$libelle</OPTION>\n";
        else
	  $s .= "<OPTION VALUE=\"$val\" SELECTED>$libelle</OPTION>\n";
      }
    return $s . "</SELECT>\n";
  }

  // Champ CHECKBOX ou RADIO
   function  champBUTTONS ($pType, $pNom, $pListe, $pDefaut, $params)
  {
    if ($pType == "CHECKBOX") $length = $params["LENGTH"];
    else $length = -1;

    // Toujours afficher dans une table
    $libelles=$champs="";
    $nbChoix = 0;
    $result = "<TABLE BORDER=0 CELLSPACING=5 CELLPADDING=2>\n"; 
    while (list ($val, $libelle) = each ($pListe))
      {
	$libelles .= "<TD><B>$libelle</B></TD>";
	$checked = " ";
	if (!is_array($pDefaut))
	  {
	    if ($val == $pDefaut) $checked = "CHECKED";
	$champs .= "<TD><INPUT TYPE='$pType' "
	  . "NAME=\"$pNom\" VALUE=\"$val\" "
	  . " $checked> </TD>\n";//adyilie: moved and changed from below
	  }
	else
	  {
	    if (is_int(strpos($pNom, "[]"))) {
		    $lNom=$pNom;//adyilie: inserted, radio buttons are arrays with indices, checkboxes are not
		} else $lNom=$pNom."[$nbChoix]";
	    if (isSet($pDefaut[$val])) $checked = "CHECKED";
	$champs .= "<TD><INPUT TYPE='$pType' "
	  . "NAME=\"$lNom\" VALUE=\"$val\" "
	  . " $checked> </TD>\n";//adyilie: moved and changed from below
	  }

	$nbChoix++;

	// Eventuellement on place plusieurs lignes dans la table
	if ($pType == "CHECKBOX" and $length == $nbChoix)
	  {
	    $result .= "<TR>" . $libelles . "</TR><TR>"
	      . $champs . "</TR>\n";
	    $libelles = $champs = "";
	    $nbChoix = 0;
	  }
      }

    if (!empty($champs))
      return  $result . "<TR>" . $libelles .  "</TR>\n<TR>" . $champs 
	. "</TR></TABLE>";
    else return $result . "</TABLE>";
  }

   function champPlain ($pLibelle, $pValeur)
   {
     $this->champLibelle ($pLibelle, "", $pValeur, "PLAIN");
   }

  // Champ de formulaire
   function champForm ($type, $nom, $val, $params, $liste=array())
  {
    // Action selon le type
    switch ($type)
      {
        case "PLAIN":
	  $champ = $val;
	break;

      case "TEXT": case "PASSWORD": case "SUBMIT": case "RESET": 
      case "FILE": case "HIDDEN":
	// Extraction des paramètres de la liste
	if (isSet($params['SIZE']))
	  $taille = $params["SIZE"];
	else  $taille = 0;
	if (isSet($params['MAXLENGTH']) and $params['MAXLENGTH']!=0)
	  $tailleMax = $params['MAXLENGTH'];
	else $tailleMax = $taille;

	// Appel de la méthode champINPUT
	$champ = $this->champINPUT ($type, $nom, $val, $taille, $tailleMax);
	// Si c'est un transfert de fichier: s'en souvenir
	if ($type == "FILE") $this->transfertFichier=TRUE;
	break;

      case "TEXTAREA": 
	$lig = $params["ROWS"]; $col = $params["COLS"];
	// Appel de la méthode champTEXTAREA de l'objet courant
	$champ = $this->champTEXTAREA ($nom, $val, $lig, $col);
	break;
    
      case "SELECT":
	$taille = $params["SIZE"];
	// Appel de la méthode champSELECT de l'objet courant
	$champ = $this->champSelect ($nom, $liste, $val, $taille);
	break;

      case "CHECKBOX": 
	$champ = $this->champBUTTONS ($type, $nom, $liste, $val, $params);
	break;

      case "RADIO":
	// Appel de la méthode champBUTTONS de l'objet courant
	$champ = $this->champBUTTONS ($type, $nom, $liste, $val, array());
	break;

      default: echo "<B>ERREUR: $type est un type inconnu</B>\n";
	break;
      }
    return $champ;
  }

  // Création d'un champ avec son libellé
   function champLibelle ($libelle, $nom, $val,  $type,
			 $params=array(),  $liste=array())
  {
    // On met le libellé en gras
    $libelle = "<B>$libelle</B>";

    if ($this->orientation != HORIZONTAL) {
      // Création de la balise HTML
      $champHTML = $this->champForm ($type, $nom, $val, $params, $liste);    
      // Stockage du libellé et de la balise dans le contenu
      $this->composants[$this->nbComposants] = array("type" => "CHAMP",
						     "libelle" => $libelle,
						     "champ" => $champHTML);
      // Renvoi de l'identifiant de la ligne, et incrémentation
      return $this->nbComposants++;
    }
    else {
      // On place dans le tableau horizontal une colonne avec les champs
      $id_comp = ++$this->nbComposants;
      for ($i=0; $i < $this->nbl_horizontales;  $i++) {
	if (is_array($val) and isSet($val[$i])) 
	  $val_def = $val[$i];
	else
	  $val_def = "";
	$champ = $this->champForm ($type, $nom, $val_def, 
				       $params, $liste);    
	$this->tableau->ajoutEntete(2, $id_comp, $libelle);
	$this->tableau->ajoutValeur("ligne$i", $id_comp, $champ);
      }
    }
  }

  /* **************** METHODES PUBLIQUES ********************/

  function setTitle ($title)
  {
    $this->title=$title;
  }

  // Méthode permettant de récupérer un champ par son identifiant
   function getChamp($idComposant)
  {
    // On récupère le composant, on extrait le champ. Manque les tests...
    $composant = $this->composants[$idComposant];
    return $composant['champ'];
  }

  // Création d'un champ et de son libellé: 
  // appel de la méthode générale, avec juste les paramètres nécessaires
   function champTexte ($libelle, $nom, $val, $taille, $tailleMax=0)
  { 
    return $this->champLibelle ($libelle, $nom, $val, 
			 "TEXT", array ("SIZE"=>$taille,
					"MAXLENGTH"=>$tailleMax));
  }

   function champMotDePasse ($pLibelle, $pNom, $pVal, $pTaille, 
				   $pTailleMax=0)
  { 
    return $this->champLibelle ($pLibelle, $pNom, $pVal, "PASSWORD", 
			 array ("SIZE"=>$pTaille, "MAXLENGTH"=>$pTailleMax));
  }

   function champRadio ($libelle, $nom, $val, $liste)
  {
    return $this->champLibelle ($libelle, $nom, $val, "RADIO", 
				array (), $liste);
  }

   function champCheckBox ($pLibelle, $pNom, $pVal, $pListe, $length=-1)
  {
    return $this->champLibelle ($pLibelle, $pNom, $pVal, "CHECKBOX", 
			 array ("LENGTH"=>$length), $pListe);
  }

   function champListe ($pLibelle, $pNom, $pVal, $pTaille, $pListe)
  {
    return $this->champLibelle ($pLibelle, $pNom, $pVal, "SELECT",
			 array("SIZE"=>$pTaille), $pListe);       
  }

   function champFenetre ($libelle, $nom, $val, $lig, $col)
  {
    return $this->champLibelle ($libelle, $nom, $val, "TEXTAREA",
			 array ("ROWS"=>$lig,"COLS"=>$col));       
  }

   function champValider ($pLibelle, $pNom)
  {
    return $this->champLibelle (" ", $pNom, $pLibelle, "SUBMIT");
  }

   function champAnnuler ($pLibelle, $pNom)
  {
    return $this->champLibelle (" ", $pNom, $pLibelle, "RESET");
  }

   function champFichier ($pLibelle, $pNom, $pTaille)
  {
    return $this->champLibelle ($pLibelle, $pNom, "", "FILE",
			 array ("SIZE"=>$pTaille));
  }

   function champCache ($nom, $valeur)
  {
    return $this->champLibelle ("", $nom, $valeur, "HIDDEN");
  }

  // Ajout d'un texte quelconque 
   function ajoutTexte ($texte)
  {
    // On ajoute un élément dans le tableau $composants
    $this->composants[$this->nbComposants] = array("type"=>"TEXTE",
					    "texte" => $texte);
    // Renvoi de l'identifiant de la ligne, et incrémentation
    return $this->nbComposants++;
  }

  // Début d'une table, mode horizontal ou vertical
   function debutTable ($orientation=VERTICAL, 
			$attributs=array(),$nbLignes=1, $title="")
  {
    // On instancie un objet pour créer ce tableau HTML
    $tableau = new Tableau (2, $attributs);
    $this->orientation = $orientation;
    $this->nbl_horizontales = $nbLignes;

    if (!empty($title))
      $tableau->setLegende ($title);

    // Jamais d'affichage de l'entête des lignes
    $tableau->setAfficheEntete (1, FALSE);

    // Action selon l'orientation
    if ($orientation == VERTICAL) {
      // Pas d'affichage de l'entête des colonnes
      $tableau->setAfficheEntete (2, FALSE);
      
      // On crée un composant dans lequel on place le tableau
      $this->composants[$this->nbComposants] =  
	array("type"=>"DEBUTTABLE",
	      "orientation"=> $orientation,
	      "tableau"=> $tableau);
      
      // Renvoi de l'identifiant de la ligne, et incrémentation
      return $this->nbComposants++;
    }
    else
      {
	$this->tableau = $tableau;
      }
  }

  // Fin d'une table
  function finTable ()
  {
    if ($this->orientation == HORIZONTAL) {
      $this->orientation = "";
      $this->champPLAIN ("", $this->tableau->tableauHTML());
      $this->tableau = "";
    }
    else
      {
      // Insertion d'une ligne marquant la fin de la table
      $this->composants[$this->nbComposants++] = array("type"=>"FINTABLE");
      $this->orientation = "";
      }
  }

  // Fin du formulaire, avec affichage éventuel.
  // NB: on peut faire une version qui effectue directement les 'echo',
  // ce qui évite de transmettre une grosse chaîne de caractères en retour

   function formulaireHTML ()
  {
    // On met un attribut ENCTYPE si on transfère un fichier
    if ($this->transfertFichier) $encType = "ENCTYPE='multipart/form-data'";
    else                         $encType="";

    $formulaire = "";
    // Maintenant, on parcourt les composants et on crée le HTML
    foreach ($this->composants as $idComposant => $description)
      {
	// Agissons selon le type de la ligne
	switch ($description["type"])
	  {
	  case "CHAMP":
	  // C'est un champ de formulaire
	    $libelle = $description['libelle'];
	    $champ = $description['champ'];
	    if ($this->orientation == VERTICAL)
	      { 
		$this->tableau->ajoutValeur($idComposant, "libelle", $libelle);
		$this->tableau->ajoutValeur($idComposant, "champ", $champ);
	      }
	    else if ($this->orientation == HORIZONTAL)
	      {
		;
		$this->tableau->ajoutEntete(2, $idComposant, $libelle);
		$this->tableau->ajoutValeur("ligne", $idComposant, $champ);
	      }
	    else
	      $formulaire .= $libelle . $champ;
	    break;

	  case "TEXTE":
	  // C'est un texte simple à insérer
	    $formulaire .= $description['texte'];
	    break;
	    
	  case "DEBUTTABLE":
	    // C'est le début d'un tableau HTML
	    $this->orientation = $description['orientation'];
	    $this->tableau = $description['tableau'];
	    break;
	    
	  case "FINTABLE":
	    // C'est la fin d'un tableau HTML
	    $formulaire .= $this->tableau->tableauHTML();
	    $this->orientation="";
	    break;

	  default: // Ne devrait jamais arriver...
	    echo "<P>ERREUR CLASSE FORMULAIRE!!<P>";
	  }
      }

    // Encadrement du formulaire par les balises
    $formulaire = "\n<FORM  METHOD='$this->methode' " . $encType
              . "ACTION='$this->action' NAME='$this->nom'>" 
              . $formulaire . "</FORM>";

    // Il faut éventuellement le centrer
    if ($this->centre) $formulaire = "<CENTER>$formulaire</CENTER>\n";;

    // On retourne la chaîne de caractères contenant le formulaire
    return $formulaire;
  }

  function fin($bool)
  {
    return $this->formulaireHTML();
  }
  // Fin de la classe
}
?>