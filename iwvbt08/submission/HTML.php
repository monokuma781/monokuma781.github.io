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
 
 
   // Fonctions produisant des conteneurs HTML

   function Ancre ($url, $libelle, $classe=-1, $onClickAction="")
   {
    $optionClasse = "";
    if ($classe != -1) $optionClasse = " CLASS='$classe'";
    return "<A HREF='$url' $optionClasse $onClickAction>$libelle</A>\n";   
   }

   function Image ($url, $largeur=-1, $hauteur=-1, $bordure=0)
   {
    $attrLargeur = "";
    $attrHauteur = "";
    if ($largeur != -1) $attrLargeur = " WIDTH  = '$largeur' ";
    if ($hauteur != -1) $attrHauteur = " HEIGHT = '$hauteur' ";

    return "<IMG SRC='$url'" .  $attrLargeur 
            . $attrHauteur . " BORDER='$bordure'>\n";   
   }
?>