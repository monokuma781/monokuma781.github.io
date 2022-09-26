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

  class Formulaire
  {
    // ----   Partie privée : les propriétés

    var $methode, $script, $nom, $classeCSS; 
    var $modeTable = FALSE,  $orientation, $transfertFichier=0;
    var $entetes=array(), $champs=array(), $nbChamps=0, 
          $nbLignes=0, $centre=true;

    var $contenuForm  = "";

    // ----   Partie privée : les méthodes 

    // Constructeur de la classe
    function Formulaire ($pMethode, $pScript, $pCentre=true,
                           $pClasse="Form", $pNom="Form")
    {
      $this->methode = $pMethode;
      $this->script = $pScript;
      $this->classeCSS = $pClasse;
      $this->nom = $pNom;
      $this->centre = $pCentre;
    }


    // Méthode pour créer un champ INPUT général
    function champINPUT ($pType, $pNom, $pVal, $pTaille, $pTailleMax)
    {
     // Création de la balise
     $s = "<INPUT TYPE='$pType' NAME=\"$pNom\" "
         . "VALUE=\"$pVal\" SIZE='$pTaille' MAXLENGTH='$pTailleMax'>\n";
     // Renvoi de la chaîne de caractères
     return $s;
    }

    // Champ de type texte
    function champTEXTAREA ($pNom, $pVal, $pLig, $pCol)
    {
      return "<TEXTAREA NAME=\"$pNom\" ROWS='$pLig' "
             . "COLS='$pCol'>$pVal</TEXTAREA>\n";
    }

    // Champ pour sélectionner dans une liste
    function  champSELECT ($pNom, $pListe, $pDefaut, $pTaille=1)
    {
      $s = "<SELECT NAME=\"$pNom\" SIZE='$pTaille'>\n";
      while (list ($val, $libelle) = each ($pListe))
      {
        if ($val != $pDefaut)
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
	      }
	    else
	      {
		if (isSet($pDefaut[$val])) $checked = "CHECKED";
	      }

	    $champs .= "<TD><INPUT TYPE='$pType' "
	       . "NAME=\"$pNom\" VALUE=\"$val\" "
	       . " $checked> </TD>\n";
	    $nbChoix++;

	    // Put many rows in the table
	    if ($pType == "CHECKBOX" and $length == $nbChoix)
	      {
		$result .= "<TR>" . $libelles . "</TR><TR>"
		   . $champs . "</TR>\n";
                $libelles = $champs = "";
		$nbChoix = 0;
	      }
      }

	if (!empty($champs))
	  return  $result . $libelles .  "</TR>\n<TR>" . $champs 
	  . "</TR></TABLE>";
	else return $result . "</TABLE>";
      }


    // Champ de formulaire
    function champForm ($pType, $pNom, $pVal, $params, $pListe=array())
    {
      switch ($pType)
      {
        case "PLAIN":
	  $champ = $pVal;
	break;

        case "TEXT": case "PASSWORD": case "SUBMIT": 
        case "RESET": case "FILE":
	  if (isSet($params['SIZE']))
	    $taille = $params["SIZE"];
	  else  $taille = 0;
	  if (isSet($params['MAXLENGTH'])
	      and $params['MAXLENGTH']!=0)
	    $tailleMax = $params['MAXLENGTH'];
	  else $tailleMax = $taille;

	  // Appel de la méthode champINPUT de l'objet courant
	  $champ = $this->champINPUT ($pType, $pNom, 
				      $pVal, $taille, $tailleMax);
	  // Si c'est un transfert de fichier: s'en souvenir
	  if ($pType == "FILE") $this->transfertFichier=TRUE;
	  break;

        case "TEXTAREA": 
              $lig = $params["ROWS"]; $col = $params["COLS"];
              // Appel de la méthode champTEXTAREA de l'objet courant
              $champ = $this->champTEXTAREA ($pNom, $pVal, $lig, $col);
              break;
    
        case "SELECT":
              $taille = $params["SIZE"];
              // Appel de la méthode champSELECT de l'objet courant
              $champ = $this->champSelect ($pNom, $pListe, $pVal, $taille);
              break;

        case "CHECKBOX": 
	  $champ = $this->champBUTTONS ($pType, $pNom, $pListe, 
					$pVal, $params);
	break;

        case "RADIO":
              // Appel de la méthode champBUTTONS de l'objet courant
	  $champ = $this->champBUTTONS ($pType, $pNom, $pListe, 
					$pVal, array());
	  break;

        default: echo "<B>ERREUR: $pType est un type inconnu</B>\n";
              break;
      }
      return $champ;
    }

    // Affichage d'un champ avec son libellé
    function champLibelle ($pLibelle, $pNom, $pVal,  $pType="TEXT",
                        $params=array(),  $pListe=array())
    {
      if ($pType == "PLAIN")
	$champHTML = $pVal;
      else
	$champHTML = $this->champForm ($pType, $pNom, $pVal, $params, 
                                                $pListe);    

      // Affichage du champ en tenant compte de la présentation
      if ($this->modeTable)
      {
        if ($this->orientation == VERTICAL)
        { 
          // Nouvelle ligne, avec libellé et champ dans deux cellules
          // On l'ajoute dans le contenu
          $this->contenuForm .= "<TR><TD><B>$pLibelle</B></TD>"
	     . "<TD>$champHTML</TD></TR>\n";
        }
        else
        {
          // On ne peut pas afficher maintenant : on stocke dans les tableaux
          $this->entetes[$this->nbChamps] = "<B>" . $pLibelle . "</B>";
          $this->champs[$this->nbChamps] = $champHTML;
          $this->nbChamps++;
        }
      }
      else  
      {
        // Affichage simple
        $this->contenuForm .= "$pLibelle  $champHTML";
      }      
   }

   // Partie publique
  
   function champTexte ($pLibelle, $pNom, $pVal, $pTaille, $pTailleMax=0)
   { 
     $this->champLibelle ($pLibelle, $pNom, $pVal, 
                             "TEXT", array ("SIZE"=>$pTaille,
                                            "MAXLENGTH"=>$pTailleMax));
   }

   function champMotDePasse ($pLibelle, $pNom, $pVal, $pTaille, 
                                  $pTailleMax=0)
   { 
     $this->champLibelle ($pLibelle, $pNom, $pVal, 
                             "PASSWORD", array ("SIZE"=>$pTaille,
                                            "MAXLENGTH"=>$pTailleMax));
   }

   function champRadio ($pLibelle, $pNom, $pVal, $pListe)
   {
     $this->champLibelle ($pLibelle, $pNom, $pVal, 
                               "RADIO", array (), $pListe);
   }

   function champCheckBox ($pLibelle, $pNom, $pVal, $pListe, $length=-1)
   {
     $this->champLibelle ($pLibelle, $pNom, $pVal, 
			  "CHECKBOX", 
			  array ("LENGTH"=>$length), $pListe);
   }

   function champListe ($pLibelle, $pNom, $pVal, $pTaille, $pListe)
   {
     $this->champLibelle ($pLibelle, $pNom, $pVal, "SELECT",
                           array("SIZE"=>$pTaille), $pListe);       
   }

   function champFenetre ($pLibelle, $pNom, $pVal, $pLig, $pCol)
   {
     $this->champLibelle ($pLibelle, $pNom, $pVal, "TEXTAREA",
                           array ("ROWS"=>$pLig,"COLS"=>$pCol));       
   }

   function champValider ($pLibelle, $pNom)
   {
     $this->champLibelle (" ", $pNom, $pLibelle, "SUBMIT");
   }

  function champAnnuler ($pLibelle, $pNom)
   {
     $this->champLibelle (" ", $pNom, $pLibelle, "RESET");
   }

   function champFichier ($pLibelle, $pNom, $pTaille)
   {
     $this->champLibelle ($pLibelle, $pNom, "", "FILE",
                            array ("SIZE"=>$pTaille));
   }

   function champCache ($pNom, $pValeur)
   {
      $this->contenuForm .=
            "<INPUT TYPE=HIDDEN NAME=\"$pNom\" VALUE=\"$pValeur\">\n";
   }

   function champPlain ($pLibelle, $pValeur)
   {
     $this->champLibelle ($pLibelle, "", $pValeur, "PLAIN");
   }

   // Ajout d'un texte quelconque 
   function ajoutTexte ($texte)
   {
     $this->contenuForm .= $texte;
   }

   // Début d'une table, mode horizontal ou vertical
   function debutTable ($orientation=VERTICAL, $nbLignes=1, $border=0)
   {
      // Pas de bordure
      if ($orientation == VERTICAL) 
           $this->contenuForm .= "<TABLE BORDER='$border'>";
      $this->modeTable = TRUE;
      $this->orientation = $orientation; 
      $this->nbLignes = $nbLignes;
      $this->nbChamps = 0;
   }

    // Fin d'une table
    function finTable ()
    {
      if ($this->modeTable == TRUE) 
      {
       if ($this->orientation == HORIZONTAL)
       {
        // Affichage des libelles
        $this->contenuForm .= "<TABLE><TR>\n";
        // Les entêtes du tableau
        for ($i=0; $i < $this->nbChamps; $i++) 
           $this->contenuForm .= "<TD>".$this->entetes[$i]."</TD>\n";
        $this->contenuForm .= "</TR>\n";

        // Affichage des lignes et colonnes
        for ($j=0; $j < $this->nbLignes; $j++)
        {
          $this->contenuForm .= "<TR>\n";
          for ($i=0; $i < $this->nbChamps; $i++) 
           $this->contenuForm .= "<TD>".$this->champs[$i]."</TD>\n";
          $this->contenuForm .= "</TR>\n";
        }
       }
       $this->contenuForm .= "</TABLE>\n";
      }
      $this->modeTable = FALSE;
    }

    // Fin du formulaire, avec affichage éventuel
    function fin ($affiche = TRUE)
    {
      // Fin de la table, au cas où on aurait oublié ...
      $this->finTable();

      // On crée le formulaire final en assemblant (1)
      // la balise d'ouverture, (2) le contenu (3) la balise fermante

      // Balise ouvrante: penser à mettre un attribut ENCTYPE 
      // si on transfère un fichier
      if ($this->transfertFichier)
        $encType = "ENCTYPE='multipart/form-data'";
      else
	$encType="";

      // Ouverture de la balise
      $baliseO = "\n<FORM  METHOD='$this->methode' " . $encType
           . "ACTION='$this->script' NAME='$this->nom'>\n";

      $baliseF =  "</FORM>\n";

      $formulaire = $baliseO . $this->contenuForm . $baliseF;

      // Il faut éventuellement centrer le formulaire
      if ($this->centre) 
        $formulaire = "<CENTER>\n" . $formulaire . "</CENTER>\n";;

      // Eventuellement on affiche
      if ($affiche) echo $formulaire;

      // Dans tous les cas on retourne 
      return $formulaire;
    }

    // Fin de la classe
  }
?>
