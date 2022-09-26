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

// Load the libraries

require_once ("Util.php");
require_once ("Formulaire.class.php");


function FormAllPaper($target){
  // Connection à la base de donnée.
  $db = new BD (NAME, PASS, BASE, SERVER);
  
  //Requète pour récupérer les valeurs par défaut
  //pour la mis en forme des fichiers PDF.
  $qValDefaults="SELECT * FROM PDFStyle";
  $rValDefaults=mysql_query($qValDefaults);  
  $rowVal=mysql_fetch_array($rValDefaults);  
  
  //Creation d'un formulaire pour la mis en page
  // d'un fichier pdf.
  $form= new Formulaire("POST",$target,false);
  
  $tabColor=array("N"=>"Black","B"=>"Blue","V"=>"Green",
		  "P"=>"Purple","R"=>"Red");
  $tabColT=$rowVal['colorT'];
  $tabColST=$rowVal['colorST'];
  $tabColTxt=$rowVal['colorTxt'];
  
  $binChoice=array("Y"=>"Yes","N"=>"No");
  $binChoiceUndT=$rowVal['underT'];
  $binChoiceUndST=$rowVal['underST'];
  
  $binChoiceItalT=$rowVal['italicT'];
  $binChoiceItalST=$rowVal['italicST'];
  $binChoiceItalTxt=$rowVal['italicTxt'];
  
  $pgBreak=$rowVal['pageBreak'];
  
  $tabFont=array("10"=>"10","11"=>"11","12"=>"12","13"=>"13","14"=>"14",
		 "15"=>"15","16"=>"16","17"=>"17","18"=>"18","19"=>"19",
		 "20"=>"20","21"=>"21","22"=>"22");
  $valFontT=$rowVal['policeT'];
  $valFontST=$rowVal['policeST'];
  $valFontTxt=$rowVal['policeTxt'];
  
  
  $margin=array("10"=>"10","20"=>"20","30"=>"30");
  $marginLeft=$rowVal['leftMargin'];
  $marginRight=$rowVal['rightMargin'];
  
  
  $form->ajoutTexte("<a href=Admin.php?action=".PDF_SELECT_PAPERS_WITH_REVIEWS.">See the pdf file</a><BR><BR>");   
  
  $form->ajoutTexte("1. Format of the titles");
  
  $form->debutTable(HORIZONTAL);
  $form->champListe("Color  ","colT", $tabColT,1, $tabColor); 
  $form->champRadio("Underline ","undT", $binChoiceUndT,$binChoice);
  $form->champRadio("Italic ","italT", $binChoiceItalT,$binChoice);
  $form->champListe("Fonts ", "fontT", $valFontT,1, $tabFont);
  $form->finTable();
  
  $form->ajoutTexte("2. Format of the subtitles");
  
  $form->debutTable(HORIZONTAL);
  $form->champListe("Color","colST", $tabColST,1, $tabColor); 
  $form->champRadio("Underline ","undST", $binChoiceUndST,$binChoice); 
  $form->champRadio("Italic ","italST",$binChoiceItalST,$binChoice);
  $form->champListe("Fonts ", "fontST",$valFontST,1, $tabFont);
  $form->finTable();
  
  $form->ajoutTexte("3. Format of the text");
  
  $form->debutTable(HORIZONTAL);
  $form->champListe("Color","colTxt", $tabColTxt,1, $tabColor); 
  $form->champRadio("Italic ","italTxt",$binChoiceItalTxt,$binChoice);
  $form->champListe("Fonts ", "fontTxt",$valFontTxt,1, $tabFont);
  $form->finTable();
  
  $form->ajoutTexte("4. Global parameters"); 
  $form->debutTable(HORIZONTAL);
  $form->champRadio("Page Break after each paper  ","pgBrk",$pgBreak,$binChoice);
  $form->champListe(" Left margin  ", "margLeft",$marginLeft,1,$margin );
  $form->champListe(" Right margin ", "margRight",$marginRight,1,$margin);  
  $form->finTable();  
  
  $form->champValider ("Submit", "submit");
  $form->ajoutTexte("<BR><BR><a href=Admin.php>Back to the admin menu</a><BR>");

  return $form->fin(false);
}

