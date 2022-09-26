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
 

//Librairies

require_once ("fpdf.php");
require_once ("bookmark.php");
require_once ("Util.php");


  // Suppression des fichiers temporaires.
  function CleanFiles()
  { 
    $currentDir=getcwd()."/tmpPdf/";    
    $h=opendir($currentDir);
    
    while($file=readdir($h)) {
     if ( substr($file,0,3)=='tmp' )     
        unlink($currentDir."".$file);
	          
    }
    closedir($h);
  }



  //Fonction permettant la generation d'un fichier PDF.

  function OutputPDf($pdfobj){
      
       CleanFiles();     
       $currentDir=getcwd()."/tmpPdf";
       $file=tempnam( $currentDir,'tmp');
       $file.='.pdf';
       $pdfobj->Output($file); 
       $file=basename($file);
      
       echo "<HTML><SCRIPT>
                 document.location='./tmpPdf/$file'
             </SCRIPT></HTML>";
  }

  //Cette fonction renvoie un tableau de trois entiers correspondant 
  //à une couleur. 
  function RetColor($colorTitre){ 
   $tab=array();   
    
    switch($colorTitre)
      {
      case "R" :
	$tab=array(255,20,85); 
        break;
       
   
      case "N" :
        $tab=array(10,10,10);
        break;
      
      case "B" :
	$tab=array(100,100,250);
	break;

      case "V" :
	$tab=array(25,200,25);
        break;

      case "P" :
	$tab=array(50,0,75);
        break;
	
      }
        
      return $tab;


  }

//Cette fonction détermine les parametres de mis en page d'un fichier pdf.
 function ParamMefPdf($requete){
   $param=array(); 
  
     /*---1. Format des titres.-----*/ 
  
         //Couleur
         $colT=RetColor($requete['colorT']);
               
         $param["pdfColT1"]=$colT[0];
         $param["pdfColT2"]=$colT[1];
	 $param["pdfColT3"]=$colT[2];
       
                       
         // Souligner
	 if ($requete['underT']=="Y")
         $undItalT='UB';
	 else  $undItalT='B';
         
	 //Italique
	 if($requete['italicT']=="Y"){
	   $undItalT=$undItalT.'I';
           $param["pdfItalT"]='I';
	 }
         else $param["pdfItalT"]='';
              
         $param["pdfUndItalT"]=$undItalT; 
           
        
        //Taille de la police. 
        $fontT=$requete['policeT'];
        $param["pdfFontT"]=$fontT; 

    /*---2. Format des sous titres.-----*/ 
        //Couleur
	$colST=RetColor($requete['colorST']);
	$param["pdfColST1"]=$colST[0];
	$param["pdfColST2"]=$colST[1];
	$param["pdfColST3"]=$colST[2];

	//Souligner
	if ($requete['underST']=="Y")
        $undItalST='UB';
	 else  $undItalST='B';
 
	//Italique
	if($requete['italicST']=="Y"){
	  $undItalST=$undItalST.'I';
          $param["pdfUndST"]='I'; 
        }
	$param["pdfUndItalST"]=$undItalST; 

	//Taille de la police. 
        $fontST=$requete['policeST'];
        $param["pdfFontST"]=$fontST; 


   /*---3. Format du texte.-----*/ 
	//Couleur
	$colTxt=RetColor($requete['colorTxt']);
	$param["pdfColTXT1"]=$colTxt[0];
	$param["pdfColTXT2"]=$colTxt[1];
	$param["pdfColTXT3"]=$colTxt[2];

        //Italique
	if($requete['italicTxt']=="Y")
	  $italTxt=$italTxt.'I';
	$param["pdfItalTXT"]=$italTxt; 

	//Taille de la police. 
        $fontTxt=$requete['policeTxt'];
	$param["pdfFontTXT"]=$fontTxt; 

 
   /*----4. Parametres globals------*/     
        //Saut de page.
        $pgBreak=$requete['pageBreak'];
        $param["pdfPgBREAK"]=$pgBreak;

        //Marge gauche.
        $mgLeft=$requete['leftMargin'];
        $param["pdfLeftMG"]=$mgLeft;

        //Marge droite.
        $mgRight=$requete['rightMargin'];
        $param["pdfRightMG"]=$mgRight;

    
   return $param;

}

// Cette fonction met a jour les parametres necessaires à
// la mis en page d'un fichier pdf. 
function UpdatePapersPDF($_POST){
  $updatePdfFormat=array();
  if (isSet($_POST['colT'] )) $updatePdfFormat["colorT"]=$_POST['colT'];
  if (isSet($_POST['colST'] )) $updatePdfFormat["colorST"]=$_POST['colST'];
  if (isSet($_POST['colTxt'])) $updatePdfFormat["colorTxt"]=$_POST['colTxt'];
  if (isSet($_POST['undT'] )) $updatePdfFormat["underT"]=$_POST['undT'];
  if (isSet($_POST['undST'] )) $updatePdfFormat["underST"]=$_POST['undST'];
  if (isSet($_POST['italT'] )) $updatePdfFormat["italicT"]=$_POST['italT'];
  if (isSet($_POST['italST'] )) $updatePdfFormat["italicST"]=$_POST['italST'];
  if (isSet($_POST['italTxt'] )) $updatePdfFormat["italicTxt"]=$_POST['italTxt'];
  if (isSet($_POST['fontT']  )) $updatePdfFormat["policeT"]=$_POST['fontT'];
  if (isSet($_POST['fontST'] )) $updatePdfFormat["policeST"]=$_POST['fontST'];
  if (isSet($_POST['fontTxt'] )) $updatePdfFormat["policeTxt"]=$_POST['fontTxt'];
  if (isSet($_POST['pgBrk'] )) $updatePdfFormat["pageBreak"]=$_POST['pgBrk'];
  if (isSet($_POST['margLeft'])) $updatePdfFormat["leftMargin"]=$_POST['margLeft'];
  if (isSet($_POST['margRight'])) $updatePdfFormat["rightMargin"]=$_POST['margRight']; 

  //Mise a jour de la mise en page du fichier pdf.  
  $req="UPDATE PDFStyle ";
  list($col,$val) = each( $updatePdfFormat);
  $req=$req."SET $col=\"$val\""; 
 
  while (list($col,$val) = each( $updatePdfFormat))
  $req=$req.", $col=\"$val\" ";
 
  $res=mysql_query($req);   
}
?>