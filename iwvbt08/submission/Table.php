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
 
 
 // Module de production de tableaux HTML

 function TblDebut ($bordure = '1', // La bordure
                    $largeur = -1, 
                    $espCell = '2', // CELLSPACING
                    $remplCell = '4', // CELLPADDING 
                    $classe=-1)
 {
  $optionClasse = ""; $optionLargeur="";
  if ($classe != -1) $optionClasse = " CLASS='$classe' ";
  if ($largeur != -1) $optionLargeur = " WIDTH='$largeur' ";

  return "<TABLE BORDER='$bordure' "
      . " CELLSPACING='$espCell' CELLPADDING='$remplCell' " 
      . $optionLargeur .  $optionClasse . ">\n";
 }

 function TblFin ()
 {
  return "</TABLE>\n";
 }

 function TblDebutLigne ($classe=-1)
 {
  $optionClasse = "";
  if ($classe != -1) $optionClasse = " CLASS='$classe'";
  return "<TR" . $optionClasse . ">\n";
 } 

 function TblFinLigne ()
 {
  return "</TR>\n";
 }

 function TblEntete ($contenu, $nbLig=1, $nbCol=1)
 {
  return "<TH ROWSPAN='$nbLig' COLSPAN='$nbCol'>$contenu</TH>\n";
 }

 function TblDebutCellule ($classe=-1)
 {
  $optionClasse = "";
  if ($classe != -1) $optionClasse = " CLASS='$classe'";
  return "<TD" . $optionClasse . ">\n";
 }

 function TblFinCellule ()
 {
  return "</TD>\n";
 }

 function TblCellule ($contenu, $nbLig=-1, $nbCol=-1, $classe=-1)
 {
   $options = "";

   if ($classe != -1) $options = " CLASS='$classe'";
   if ($nbLig != -1) $options .= " ROWSPAN=$nbLig";
   if ($nbCol != -1) $options .= " COLSPAN=$nbCol";
  
  return "<TD$options>$contenu</TD>\n";
 }
?>
