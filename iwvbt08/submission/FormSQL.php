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
 
 
  // Formulaire de saisie d'une requête SQL

  require_once ("Formulaire.class.php");

  function FormSQL ($requeteDefaut)
  {
    $form = new Formulaire ("POST", "ExecSQL.php");
    $form->champFenetre ("", "requete", $requeteDefaut, 5, 50);
    $form->ajoutTexte ("<P>");
    $form->champValider ("Exécuter la requête", "exesql");

    $form->fin();
  }
?>