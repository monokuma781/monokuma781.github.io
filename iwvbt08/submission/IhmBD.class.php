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
 
//require_once("Tableau.class.php");
//require_once("Formulaire.class.php");

// Classe générique pour accéder à une table.  
// Fonctionne quelle que soit la table, quel que soit le SGBD
// Peut être spécialisée pour surcharger certaines méthodes (voir IhmCarte).

define ("SELECT_FIELD", "SELECT");
define ("HIDDEN_FIELD", "HIDDEN");
define ("CHECKBOX_FIELD", "CHECKBOX");
define ("BOOLEAN_FIELD", "BOOLEAN");


define("INS_BD", 1);
define ("MAJ_BD", 2);
define ("DEL_BD", 3);
define ("EDITER", 4);

class IhmBD
{
  // ----   Partie privée : les constantes et les variables
  var $bd, $nomScript, $nomTable, $schemaTable, $title,
    $entetes, $form_fields, $form_fields_type, $hidden_fields,
    $order_by_clause, $where_clause, $auto_increment_key;

  var $slave_table, $schema_slave, $size_slave_table, 
    $foreign_keys;

  // Le constructeur
  function IhmBD ($nomTable, $bd, $script="moi")
  {
    // Initialisation des variables privées
    $this->bd = $bd;
    $this->nomTable = $nomTable;
    if ($script == "moi")
      $this->nomScript = $_SERVER['PHP_SELF'];
    else
      $this->nomScript = $script;
    $this->title = $nomTable;

    // The id should always be auto-incremented ?!
    $this->auto_increment_key["id"]=true;
    $this->order_by_clause = $this->where_clause = "";
    $this->hidden_fields = array();
    $this->slave_table = "";

    // Lecture du schéma de la table, et lancé d'exception si pb
    $this->schemaTable = $this->bd->schemaTable($nomTable);

    // Par défaut, les textes des attributs sont leurs noms
    foreach ($this->schemaTable as $nom => $options)
      $this->entetes[$nom] = $nom;
  }

  // Fonction renvoyant la clé d'accès à un tuple, sous
  // format URL ou SQL
  function accesCle($tuple, $format="url")
  {
    $separateur = $chaine = "";
    // Parcours des attributs
    foreach ($this->schemaTable as $nom => $options) 
      {
	// C'est un attribut de la clé primaire
	if ($options['cle_primaire'])  
	  {
	    if ($format=="url")
	      {
		$chaine .= "&$nom=" .  urlEncode($tuple[$nom]);
		$separateur = "&";
	      }
	    else
	      {
		$chaine .= "$separateur$nom='" . 
		  addSlashes($tuple[$nom]) . "'";
		$separateur = " AND ";
	      }
	  }
      }
    return $chaine;
  }

  // Fonction renvoyant la clé d'accès à la table esclave
  function slaveAccess($tuple)
  {
    $separateur = $chaine = "";
    // Parcours des attributs
    foreach ($this->schema_slave as $nom => $options)
      {
	if (isSet($this->foreign_keys[$nom])) {
	  $chaine .= "$separateur$nom='" . 
	    addSlashes($tuple[$this->foreign_keys[$nom]]) . "'";
	  $separateur = " AND ";
	}
      }
    return $chaine;
  }

  // Méthode effectuant des contrôles avant mise à jour 
  function controle(&$ligne, &$messages)
  {
    // On commence par traiter toutes les chaînes des attributs
    foreach ($this->schemaTable as $nom => $options) 
      {
	if (!isSet($this->auto_increment_key[$nom])) {
	  // Transformation des ' en \'
	  $ligne[$nom] = $this->bd->prepareString($ligne[$nom]);
	}
      }
    // On peut, de plus, contrôler le type ou la longueur des données
    // d'après le schéma de la table... A faire!
    return true; // false si un pb est rencontré
  }

  /*****************   Partie publique ********************/

  // Création d'un formulaire générique
  function formulaire ($action, $ligne)
  {
    // print_r ($ligne); echo "<p>";

    // Création de l'objet formulaire
    $form = new Formulaire ("POST", $this->nomScript, false);
    $form->setTitle ($this->title);

    foreach ($this->hidden_fields as $nom => $value)
      $form->champCache ($nom, $value);
      
    $form->champCache ("ihm_action", $action);
      
    $form->debutTable (VERTICAL,array(),$nbLignes=1, $this->title);

    // Pour chaque attribut, création d'un champ de saisie
    foreach ($this->schemaTable as $nom => $options)
      {
	// D'abord vérifier que la valeur par défaut existe
	if (!isSet($ligne[$nom])) $ligne[$nom] = "";
	$this->addFormField ($form, $action, $nom, $ligne[$nom], $options);
      }
    
    // OK. Now add lines for the slave table, if any
    if (!empty($this->slave_table)) {
      $form->debutTable (HORIZONTAL, array(), 
			 $this->size_slave_table);

      // Pour chaque attribut, création d'un champ de saisie
      foreach ($this->schema_slave as $nom => $options)
	{
	  // D'abord vérifier que la valeur par défaut existe
	  if (!isSet($ligne[$this->slave_table][$nom]))
	    $valeur = "";
	  else
	    $valeur = $ligne[$this->slave_table][$nom];
 
	  // Attention à gérer les clés étrangères
	  if (isSet($this->foreign_keys[$nom])) {
	    $options['cle_etrangere'] = 1;
	    if ($action == MAJ_BD)
	      $valeur = $ligne[$this->foreign_keys[$nom]];
	  }

	  $html_field_name = "$this->slave_table[$nom][]";
	  $this->addFormField ($form, $action, $nom, 
			       $valeur, $options, $html_field_name);
	}
      $form->finTable ();
    }
    $form->finTable();
	
    if ($action == MAJ_BD)
      $form->champValider ("Modify", "submit");
    else
      $form->champValider ("Insert", "submit");

    return $form->formulaireHTML();
  }
  
  // Add a field to the form
  function addFormField (&$form, $action, $nom, $valeur, $options,
			 $html_field_name="")
  {
    if (empty($html_field_name))
      $html_field_name = $nom;

    // Attention: traitement des balises HTML avant affichage
    if (!is_array($valeur))
      $valeur = htmlSpecialChars($valeur);

    // On met la clé primaire en champ caché  
    if ($options['cle_primaire'] and $action == MAJ_BD) {
      $form->champCache ($html_field_name, $valeur);
    }
    if (isSet($options['cle_etrangere'])) {
      $form->champCache ($html_field_name, $valeur);
    }
    else {
      if (!isSet($this->auto_increment_key[$nom])) {
	// Affichage du champ
	if ($options['type'] == "blob")
	  $form->champfenetre ($this->entetes[$nom], 
			       $html_field_name, $valeur, 5, 60);
	else
	  {
	    if ($options['type'] == "time" and $action==MAJ_BD) {
	      // Show only hour and minutes 
	      $time = explode (":", $valeur);
	      $valeur = $time[0] . ":" . $time[1];
	    }
	    
	    if (!isSet($this->form_fields_type[$nom])) {
	      $lg_visible = min (60, $options['longueur']);
	      $form->champTexte ($this->entetes[$nom], 
				 $html_field_name, $valeur, $lg_visible,
				 $options['longueur']);
	    }
	    else if ($this->form_fields_type[$nom] == SELECT_FIELD) {
	      $form->champListe ($this->entetes[$nom], 
				 $html_field_name, $valeur, 1, 
				 $this->form_fields[$nom]);
	    }
	    else if ($this->form_fields_type[$nom] == CHECKBOX_FIELD) {
	      $form->champRadio ($this->entetes[$nom], 
				 $html_field_name, $valeur, 
				 $this->form_fields[$nom]);
	    }
	    else if ($this->form_fields_type[$nom] == BOOLEAN_FIELD) {
	      $form->champRadio ($this->entetes[$nom], 
				 $html_field_name, $valeur, 
				 array("Y" => "Yes", "N" => "No"));
	    }
	  }
      }
    }
  }

  // Fonction d'insertion d'une ligne. A faire: vérifier
  // que la ligne n'existe pas déjà!
  function insertion ($ligne)
  {
    // Initisalisations
    $noms = $valeurs = $virgule = "";
    $messages = array();

    // Contrôle avant toute mise à jour
    if (!$this->controle ($ligne, $messages))
      {
	$mess = "";
	foreach ($messages as $m) $mess.="<li>$m</li>";
	echo "<ol>$mess</ol>\n";
	return false;
      }

    // Parcours des attributs pour créer la requête
    foreach ($this->schemaTable as $nom => $options)
      {
	if (!isSet($this->auto_increment_key[$nom])) {
	  // Liste des noms d'attributs + liste des valeurs
	  $noms .= $virgule . $nom;
	  $valeurs .= $virgule . "'" . $ligne[$nom] . "'";
	  // A partir de la seconde fois, on sépare par des virgules
	  $virgule= ",";
	}
      }
    $requete = "INSERT INTO $this->nomTable ($noms) VALUES ($valeurs) ";
    $this->bd->execRequete ($requete);
    $id_master = mysql_insert_id();

    $this->insertInSlave ($id_master, $ligne);

    return true;
  }

  function deleteFromSlave ($id_master, $ligne)
  {
    // Take account of the slave table if any
    if (!empty($this->slave_table)) {
      $slave_values = $ligne[$this->slave_table];
      // On détruit les lignes existantes
      $clause = $this->slaveAccess ($ligne);
      $query = "DELETE FROM $this->slave_table WHERE $clause";
      $res = $this->bd->execRequete($query);
    }
  }

  function insertInSlave ($id_master, $ligne)
  {
    // Take account of the slave table if any
    if (!empty($this->slave_table)) {
      $slave_values = $ligne[$this->slave_table];
      
      // On fait une boucle sur le nombre de lignes possibles
      for ($i=0; $i < $this->size_slave_table; $i++) {
	$virgule=""; $noms=""; $valeurs=""; $insertion = true;
	// Parcours des attributs pour créer la requête
	foreach ($this->schema_slave as $nom => $options)
	  {
	    if (!isSet($this->auto_increment_key[$nom])) {
	      // Liste des noms d'attributs + liste des valeurs
	      $noms .= $virgule . $nom;

	      // Attention à gérer les clés étrangères
	      if (isSet($this->foreign_keys[$nom])) 
		$valeur = $id_master;
	      else
		$valeur = $slave_values[$nom][$i];

	      // Never insert if one value is missing (do better some day..)
	      if ($valeur == "") $insertion = false;
	      $valeurs .= "$virgule '$valeur'";
	      // A partir de la seconde fois, on sépare par des virgules
	      $virgule= ",";
	    }
	  }
	if ($insertion) {
	  $requete = "INSERT INTO $this->slave_table($noms) VALUES ($valeurs)";
	  // echo "Exécution de $requete<br>";
	  $this->bd->execRequete ($requete);
	}
      }
    }
  }

  // Fonction de mise à jour  d'une ligne
  function maj ($ligne)
  {
    // Initisalisations
    $listeAffectations = $virgule = "";

    // Contrôle avant toute mise à jour
    if (!$this->controle ($ligne, $messages))
      {
	$mess = "";
	foreach ($messages as $m) $mess.="<li>$m</li>";
	echo "<ol>$mess</ol>\n";
	return false;
      }

    // Parcours des attributs pour créer la requête
    foreach ($this->schemaTable as $nom => $options)
      {
	// Création de la clause WHERE
	$clauseWhere = $this->accesCle($ligne, "SQL");
	// Création des affectations nom='valeur'
	if (!$options['cle_primaire'])
	  {
	    $listeAffectations .= $virgule . "$nom='" . $ligne[$nom] . "'";   
	    // A partir du second, on sépare par des virgules 
	    $virgule= ",";
	  }
	else
	  $id_master = $ligne[$nom];
      }

    $requete = "UPDATE $this->nomTable SET $listeAffectations "
      . "WHERE $clauseWhere";
    $this->bd->execRequete ($requete);

    // Take account of the slave table
    $this->deleteFromSlave ($id_master, $ligne);
    $this->insertInSlave ($id_master, $ligne);
    return true;
  }

  // Fonction de destruction  d'une ligne
  function del ($ligne)
  {
    // On supprime le contenu de la table esclave
    if (!empty($this->slave_table)) {
      $clause = $this->slaveAccess ($ligne);
      $query = "DELETE FROM $this->slave_table WHERE $clause";
      $res = $this->bd->execRequete($query);
    } 

    // Création de la clause WHERE
    $clauseWhere = $this->accesCle($ligne, "SQL");
    $requete = "DELETE FROM $this->nomTable WHERE $clauseWhere";
    $this->bd->execRequete ($requete);
  }

  // Création d'un tableau générique
  function tableau($attributs=array())
  {
    // Création de l'objet tableau
    $tableau = new Tableau(2, $attributs);
    $tableau->setCouleurImpaire("silver");
    $tableau->setAfficheEntete(1, false);
    $tableau->setLegende("Lines in $this->nomTable");

    // Texte des entêtes
    foreach ($this->schemaTable as $nom => $options) 
      if (!isSet($this->auto_increment_key[$nom]))
	$tableau->ajoutEntete(2, $nom, $this->entetes[$nom]);

    //    $tableau->ajoutEntete(2, "action", "Action");

    // Parcours de la table
    $requete = "SELECT * FROM $this->nomTable $this->where_clause";
    if (!empty($this->order_by_clause))
      $requete .= "ORDER BY $this->order_by_clause";
    $resultat = $this->bd->execRequete ($requete);
    
    $i=0;
    while ($ligne = $this->bd->ligneSuivante ($resultat))
      {
	$i++; 
	// Création des cellules
	foreach ($this->schemaTable as $nom => $options) 
	  {
	    if (!isSet($this->auto_increment_key[$nom])) {
	      if ($options['type'] == "time") {
		// Show only hour and minutes 
		$time = explode (":", $ligne[$nom]);
		$ligne[$nom] = $time[0] . ":" . $time[1];
	      }

	      if (isSet ($this->form_fields_type[$nom])) {
		// La valeur est référencée par la clé externe $ligne[$nom]
		$libelle = $this->form_fields[$nom][$ligne[$nom]];
	      }
	      else
		// Attention: traitement des balises HTML avant affichage
		$libelle = htmlSpecialChars($ligne[$nom]);
	      $tableau->ajoutValeur($i, $nom, $libelle);
	    }

	  }
	
	// Création de l'URL de modification
	$urlMod = $this->accesCle($ligne) . "&ihm_action=" . EDITER;
	$modLink = "<a href='$this->nomScript$urlMod'>modify</a>";
	$tableau->ajoutValeur($i, "Modify", $modLink);

	$urlDel = $this->accesCle($ligne) . "&ihm_action=" . DEL_BD;
	$jscript= "onClick=\"ConfirmAction"
	  . "('This will remove this tuple!?', '$this->nomScript$urlDel')\"";
	$delLink = "<a $jscript href='#'>delete</a>";
	$tableau->ajoutValeur($i, "Delete", $delLink);
      }

    // Retour de la chaîne contenant le tableau
    return $tableau->tableauHTML();
  }

  // Méthode permettant d'affecter un entête à un attribut
  function setEntete($nomAttr, $texte)
  {
    $this->entetes[$nomAttr] = $texte;
  }

  // Méthode permettant d'affecter un titre
  function setTitle($title)
  {    
    $this->title = $title;
  }

  // Méthode permettant d'indiquer le type d'un champ du formulaire
  function setFormField($nomAttr, $type, $params, $whereClause="")
  {
    if ($type == SELECT_FIELD)
      {
	$this->form_fields_type[$nomAttr] = SELECT_FIELD;
	// Recherche de la liste
	$tb_name = $params["tb_name"];
	$id_name = $params["id_name"];
	$name = $params["name"];
	$res = array();
	$result = $this->bd->execRequete 
	  ("SELECT $id_name, $name FROM $tb_name $whereClause ORDER BY $name");
	while ($cursor = $this->bd->ligneSuivante ($result))
	  $res[$cursor[$id_name]] = $cursor[$name];
	$this->form_fields[$nomAttr] = $res;
      }
    else if ($type == CHECKBOX_FIELD)
      {
	$this->form_fields_type[$nomAttr] = CHECKBOX_FIELD;
	// Recherche de la liste
	$this->form_fields[$nomAttr] = $params;
      }
    else if ($type == BOOLEAN_FIELD)
      {
	$this->form_fields_type[$nomAttr] = BOOLEAN_FIELD;
      }
  }

  // Fonction recherchant une ligne d'après sa clé
  function chercheLigne($params, $format="tableau")
  {
    // On constitue la clause WHERE
    $clauseWhere = $this->accesCle ($params, "SQL");

    // Création et exécution de la requête SQL
    $requete = "SELECT * FROM $this->nomTable WHERE $clauseWhere";
    $resultat = $this->bd->execRequete($requete);

    if ($format == "tableau") {
      $ligne = $this->bd->ligneSuivante($resultat);

      // On complète avec le contenu de la table esclave
      if (!empty($this->slave_table)) {
	$clause = $this->slaveAccess ($ligne);
	$query = "SELECT * FROM $this->slave_table WHERE $clause";
	$res = $this->bd->execRequete($query);
	while ($sl = $this->bd->ligneSuivante($res)) {
	  foreach ($sl as $nom_att => $val_att)
	    $ligne[$this->slave_table][$nom_att][] = $val_att;
	}
      }
      return $ligne;
    }
    else
      return $this->bd->objetSuivant($resultat);
  }

  // Fonction créant une interface avec saisie, mise à jour
  // et consultation
  function genererIHM ($paramsHTTP)
  {
    echo "<script language='JavaScript1.2' src='ShowWindow.js'></script>";
 
    // A-t-on demandé une action?
    if (isSet($paramsHTTP['ihm_action']))
      $action = $paramsHTTP['ihm_action'];
    else 
      $action = "";

    $affichage = "";
    switch ($action)
      {
      case INS_BD:
	// On a demandé une insertion
	if ($this->insertion($paramsHTTP))
	  {
	    $affichage .= "<I>Insertion: done</I>";
	    $affichage .= "<h2>Input form</h2>";
	    $affichage .= $this->formulaire(INS_BD, array());
	  }
	else{
	  $affichage .= $this->formulaire(INS_BD, $paramsHTTP);
	}
	break;
	
      case MAJ_BD:
	// On a demandé une modification
	if ($this->maj($paramsHTTP))
	  $affichage .= "<I>Update: done.</I>";
	$ligne  = $this->chercheLigne ($paramsHTTP);
	$affichage .= $this->formulaire(MAJ_BD,$ligne);
	break;

      case DEL_BD:
	// On a demandé une destruction
	$this->del($paramsHTTP);
	$affichage .= "<I>Delete: done.</I>";
	$affichage .= $this->formulaire(INS_BD, array());
	break;

      case EDITER:
	// On a demandé l'accès à une ligne en mise à jour
	$ligne  = $this->chercheLigne ($paramsHTTP);
	$affichage .= $this->formulaire(MAJ_BD,$ligne);
	break;
	
      default:
	$affichage .= $this->formulaire(INS_BD,array());

      }
   
    // On met toujours le tableau du contenu de la table
    $affichage .= "<h2>Lines in table <I>$this->nomTable</I></h2>"
      . $this->tableau(array("BORDER" => 2));
    $affichage .= "<a href='$this->nomScript'>Add a new line</a>";
    // Retour de la page HTML
    return $affichage;
  }

  // Pour ajouter un champ caché dans le formulaire
  function addHiddenField ($name, $value)
  {
    $this->hidden_fields[$name] = $value;
  }

  // Pour ajouter une clause de tri
  function setOrderBy ($fields_list)
  {
    // The list must be SQL-compliant: field[, field], ...
    $this->order_by_clause = $fields_list;
  }

  // Pour ajouter une clause WHERE
  function setWhereClause ($where_clause)
  {
    $this->where_clause = $where_clause;
  }

  // Pour ajouter une table-esclave
  function setSlaveTable ($tbname, $foreign_keys, $nb_lines=10) {  
    $this->slave_table = $tbname;  
    $this->schema_slave = $this->bd->schemaTable($tbname);
    $this->size_slave_table = $nb_lines;
    $this->foreign_keys = $foreign_keys;
    foreach ($this->schema_slave as $nom => $options)
      $this->entetes[$nom] = $nom;
  }

  // Pour indiquer le champ auto-incrémenté
  function setAutoIncrementedKey ($ai_name)
  {
    $this->auto_increment_key[$ai_name]=true;
  }
}
?>