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

require_once ("fpdf.php");
require_once ("bookmark.php");
require_once ("Util.php");


function reviewerComments($db,$pdf,$param,$idPaper,$reviewerEmail)
{
  //Commentaire des reviewers   
    $qComments="SELECT summary,details,comments ".
      " from Review ".
      " WHERE idPaper=$idPaper ".  
      " AND email='$reviewerEmail';";
    
    $rComments=$db->execRequete($qComments);    
    $comments = $db->objetSuivant($rComments);

    if ($comments->summary!="") {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'Summary : ');
      $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
      $pdf->SetFont('',$param["pdfItalTXT"]);
      $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]); 
      $pdf->MultiCell(100,6,$comments->summary);
    } else {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'No Summary');
    }
    $pdf->Ln();
    
    if ($comments->details!="") {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'Details : ');
      $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
      $pdf->SetFont('',$param["pdfItalTXT"]);
      $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]); 
      $pdf->MultiCell(100,6,$comments->details);
    } else {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'No Details');
    }
    $pdf->Ln();

    if ($comments->comments!="") {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(60,6,'Comments for PC : ');
      $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
      $pdf->SetFont('',$param["pdfItalTXT"]);
      $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]); 
      $pdf->MultiCell(100,6,$comments->comments);
    } else {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'No Comments for PC');
    }
    $pdf->Ln();

    return $pdf;
}


function afficheSummary($db,$pdf,$param,$listCriteria, $idPaper){  
  $pdf->Ln();  
  $pdf->Line($pdf->GetX(),$pdf->GetY()-4,$pdf->GetX()+150,$pdf->GetY()-4); 
  $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+150,$pdf->GetY()-2); 
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
  $pdf->Cell(40,10,"Paper ".$idPaper." Summary :"); 
  $pdf->Ln();  

  $qOverall="select avg(overall) as overall from Review where idPaper='$idPaper' and overall<>\"\";";
  $rOverall=$db->execRequete($qOverall);
  $overall=$db->objetSuivant($rOverall);
  $header=array();  
  $header[]=" Overall ";     
  $header[]=" $overall->overall";   
  $pdf->SetWidths(array(50,50));
  $pdf->Row($header);

  $qMark="select idCriteria,avg(mark) as mark from ReviewMark where idPaper='$idPaper' group by idCriteria;";
  $rMark=$db->execRequete($qMark);
  $isReview=0;
  while($mark=$db->objetSuivant($rMark)) {
    $isReview=1;
    $marks[$mark->idCriteria]=$mark->mark;
  }
  
  foreach ($listCriteria as $id => $crVals)
    {	
      $tab=array();
      $label = $crVals["label"];
      if (strlen($label)>45) $label = substr($label,0,44).".";    
      $tab[]=" $label ";     
      if ($isReview) $mark=$marks[$id];
      else $mark="?";
      $tab[]=" ".substr($mark,0,5);
      $pdf->Row($tab);
    }
 
  $pdf->Ln();
  return $pdf;
}


function afficheReview($db,$pdf,$param,$listCriteria, $idPaper,$reviewer){
  global $EXPERTISE; 
  global $SCALE;
  $pdf->Ln();  
  $pdf->Line($pdf->GetX(),$pdf->GetY()-4,$pdf->GetX()+150,$pdf->GetY()-4); 
  $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+150,$pdf->GetY()-2); 
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
  $pdf->Cell(40,5,'Reviewer :');
  $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
  $pdf->SetFont('',$param["pdfItalTXT"]);
  $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);     
  $pdf->Cell(40,5,$reviewer->firstName." ".$reviewer->lastName);
  $pdf->Ln();
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
  $pdf->Cell(40,10,'Expertise :');
  $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
  $pdf->SetFont('',$param["pdfItalTXT"]);
  $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);     
  $pdf->Cell(40,10,$EXPERTISE[$reviewer->reviewerExpertise]);
  $pdf->Ln(); 

  $header=array();  
  $header[]=" Overall ";     
  $header[]=" $reviewer->overall";   
  $pdf->SetWidths(array(50,50));
  $pdf->Row($header);

  $qMark="select idCriteria, mark from ReviewMark where idPaper=$idPaper and email='$reviewer->email';";
  $rMark=$db->execRequete($qMark);
  while($mark=$db->objetSuivant($rMark)) {
    $marks[$mark->idCriteria]=$mark->mark;	
  }
  
  foreach ($listCriteria as $id => $crVals)
    {
      $header=array();
      $label = $crVals["label"];
      if (strlen($label)>45) $label = substr($label,0,44).".";    
      $header[]=" $label ";     
      $mark=$marks[$id];
      $header[]=" $SCALE[$mark] ";
      $pdf->Row($header);
    }

  $pdf = reviewerComments($db, $pdf,$param, $idPaper, $reviewer->email);
  $pdf->Ln();
  return $pdf;
}



function afficheReviews($db,$pdf,$param,$idPaper){
  $listC = GetListCriterias($db);   
  $qReviewers="select firstName,lastName, r.email, r.reviewerExpertise, r.overall ".
    "from Review r, PCMember p where p.email=r.email and r.idPaper=$idPaper and overall<>\"\";";
  $rReviewers=$db->execRequete($qReviewers);
  while($reviewer=$db->objetSuivant($rReviewers)) {    
    $pdf=afficheReview($db,$pdf,$param,$listC,$idPaper,$reviewer);
  }
  $pdf=afficheSummary($db,$pdf,$param,$listC,$idPaper);
  return $pdf;  
}

function afficheDiscussion($db,$pdf,$param,$idPaper){
    $qDiscussion=" SELECT * " 
      ." from Message m,PCMember p"
      . " Where m.idPaper =$idPaper"
      . " AND m.emailReviewer=p.email "
      . " ORDER BY date";
    
    $rDiscussion=$db->execRequete($qDiscussion); 
    
    $cpt=0;  
    while ($rowDisc=$db->objetSuivant($rDiscussion)){      
      if ($cpt==0) {
	$pdf->SetFont('Arial','B', $param["pdfFontST"]); 
	$pdf->SetFont('',$param["pdfUndItalST"]);
	$pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
	$pdf->Cell(40,10,'Discussion :');
	$pdf->Ln();
	$pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
	$pdf->SetFont('',$param["pdfItalTXT"]);
	$pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);  
	
	$cpt++;
	$head=array();
	$head[]=" Message id ";
	$head[]=" Reviewer ";
	$head[]=" Date  ";
	$head[]=" Text ";
	$pdf->SetFont('Arial','B',10 ); 
	$pdf->SetWidths(array(25,20,25,100));
	$pdf->Row($head);	
      }      
      $tabMessage[]=" ".$rowDisc->idMessage;
      $firstName=$rowDisc->firstName;
      $LastName=$rowDisc->lastName;
      $tabMessage[]=" ".$firstName." ".$LastName;
      $tabMessage[]=" ".$rowDisc->date;
      $tabMessage[]=" ".$rowDisc->message;      
      $pdf->Row($tabMessage);
      $tabMessage=array();
    }  
    return $pdf;
}

function paperHeader($db,$pdf,$param, $idPaper, $id_session)
{
  $qPaperInfo = "SELECT * FROM Paper WHERE id='$idPaper';";
  $rPaperInfo = $db->execRequete($qPaperInfo);
  $paperInfo = $db->objetSuivant($rPaperInfo);
  
  // Paper id
  $pdf->Ln(10);
  $pdf->SetFont('Arial','B', $param["pdfFontT"]);       
  $pdf->SetFont('', $param["pdfUndItalT"]);
  $pdf->SetTextColor($param["pdfColT1"],$param["pdfColT2"],$param["pdfColT3"]);
  $pdf->Cell(40,6,'Paper id : '); 
  $pdf->SetFont('',$param["pdfItalT"]);
  $pdf->Cell(40,6,$idPaper);
  $pdf->Ln();
  $pdf->Ln();   
  
  // Title 
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
  $pdf->Cell(40,6,'Title : ');
  $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
  $pdf->SetFont('',$param["pdfItalTXT"]);
  $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);
  $pdf->Cell(40,6,$paperInfo->title);
  $pdf->Ln();
  
  // Authors 
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
  $pdf->Cell(40,6,'Authors : ');
  $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
  $pdf->SetFont('',$param["pdfItalTXT"]);
  $pdf->SetTextColor($param["pdfColTXT1"],
		     $param["pdfColTXT2"],$param["pdfColTXT3"]);

  $config = GetConfig($db);
  $session = GetSession ($id_session, $db); 
  if ($config["blind_review"] == "Y" and !strstr($session->roles,"C"))
    $blind_review = true;
  else
    $blind_review = false;
  $pdf->Cell(40,6, GetAuthors($paperInfo->id, $db, $blind_review, "string", 
			      $paperInfo->authors));
  $pdf->Ln();    
  
  // Abstract 
  $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
  $pdf->SetFont('',$param["pdfUndItalST"]);
  $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
    $pdf->Cell(40,6,'Abstract : ');  
    $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
    $pdf->SetFont('',$param["pdfItalTXT"]);
    $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);
    $pdf->MultiCell(0,5,$paperInfo->abstract); 
    $pdf->Ln();
    

    // Topic
    if ($paperInfo->topic!=0) {
      $qTopic="select * from ResearchTopic where id='$paperInfo->topic';";
      $rTopic = $db->execRequete($qTopic);
      $topic = $db->objetSuivant($rTopic);

      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'Topic : ');
      $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
      $pdf->SetFont('',$param["pdfItalTXT"]);
      $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);
      $pdf->Cell(40,6,$topic->label);
      $pdf->Ln();
    }  
    return $pdf;
}


//Ecriture dans un fichier PDF de la liste des papiers 
//obtenu suivant les criteres selectionnes.

function WriteResult($pdf,$papersList,$param,$db,$withReviews, $id_session)
{  
  while (list ($idPaper, $overall) = each ($papersList)) {
    $indexIdPaper="Paper ".$idPaper;
    $pdf->Bookmark($indexIdPaper);
    
    // Paper id, Title, Authors, Abstract and Topic
    $pdf=paperHeader($db,$pdf,$param,$idPaper, $id_session);    
    
    if ($withReviews) {
      // Reviews
      $pdf=afficheReviews($db,$pdf,$param,$idPaper);    
      
      // Separation
      $pdf->Line($pdf->GetX(),$pdf->GetY()-4,$pdf->GetX()+150,$pdf->GetY()-4); 
      $pdf->Line($pdf->GetX(),$pdf->GetY()-2,$pdf->GetX()+150,$pdf->GetY()-2); 
      
      // Discussion sur l'article.       
      $pdf=afficheDiscussion($db,$pdf,$param,$idPaper);    
      
      $pdf->AddPage();    
    }
    else {
      $pdf->SetFont('Arial','B', $param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfUndItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);
      $pdf->Cell(40,6,'Reviewers : ');
      $pdf->Ln();

      $pdf->SetFont('Arial','B',$param["pdfFontST"]); 
      $pdf->SetFont('',$param["pdfItalST"]);
      $pdf->SetTextColor($param["pdfColST1"],$param["pdfColST2"],$param["pdfColST3"]);

      $tab=array();
      $tab[]=" Reviewer";
      $tab[]=" Email";
      $tab[]=" Overall";
      $pdf->SetFont('Arial','B',10 ); 
      $pdf->SetWidths(array(50,75,15));
      $pdf->Row($tab);	     

      $pdf->SetFont('Arial','B',$param["pdfFontTXT"]); 
      $pdf->SetFont('',$param["pdfItalTXT"]);
      $pdf->SetTextColor($param["pdfColTXT1"],$param["pdfColTXT2"],$param["pdfColTXT3"]);

      $qReviewers="select firstName,lastName, r.email, overall ".
	"from Review r, PCMember p where p.email=r.email ".
	"and r.idPaper=$idPaper;";    
      $rReviewers = $db->execRequete($qReviewers);
      while ($reviewer = $db->objetSuivant($rReviewers)){

	$tab=array();
	$tab[]=" ".$reviewer->firstName." ".$reviewer->lastName;
	$tab[]=" ".$reviewer->email;
	if ($reviewer->overall!="") {$tab[]=" ".$reviewer->overall;}
	else {$tab[]=" ?";}
	$pdf->SetFont('Arial','B',10 ); 
	$pdf->SetWidths(array(50,75,15));
	$pdf->Row($tab);
      }
      $pdf->Ln();
    }
    
  }   

  if (!$withReviews) $pdf->AddPage();
  // Index de pdf.
  $pdf->Bookmark('Index');      
  $pdf->CreateIndex();    
  
  return $pdf; 
}


function  PrintReview($idPaper, $reviewerEmail, $id_session)
{
 //connexion a la base de Mysql
  $db = new BD (NAME, PASS, BASE, SERVER);

  //Requete pour recuperer les paramatres de mis en page  du fichier pdf.
  $qUpdate="SELECT * ".
    "From PDFStyle"; 
  $rUpdate=$db->execRequete($qUpdate);
  $rUpdate=$db->ligneSuivante($rUpdate);
  $param=ParamMefPdf($rUpdate);

  //initialisation d'un fichier pdf
  $pdf=new PDF_Index();
  $pdf->AddPage();
  $pdf=paperHeader($db, $pdf, $param, $idPaper, $id_session);
  $listC = GetListCriterias($db); 
  $qReviewer="select firstName,lastName, r.email, r.reviewerExpertise, r.overall ".
    "from Review r, PCMember p where p.email=r.email and r.idPaper=$idPaper and p.email='$reviewerEmail';";
  $rReviewer=$db->execRequete($qReviewer);
  $reviewer=$db->objetSuivant($rReviewer);
  $pdf=afficheReview($db,$pdf,$param,$listC, $idPaper,$reviewer);
  //generation du fichier pdf.
  OutputPDF($pdf); 
}

function PrintAllReviews($idPaper, $id_session)
{
  //connexion a la base de Mysql
  $db = new BD (NAME, PASS, BASE, SERVER);

  //Requete pour recuperer les paramatres de mis en page  du fichier pdf.
  $qUpdate="SELECT * ".
    "From PDFStyle"; 
  $rUpdate=$db->execRequete($qUpdate);
  $rUpdate=$db->ligneSuivante($rUpdate);
  $param=ParamMefPdf($rUpdate);

  //initialisation d'un fichier pdf
  $pdf=new PDF_Index();
  $pdf->AddPage();
  $pdf=paperHeader($db, $pdf, $param, $idPaper, $id_session);
  $pdf=afficheReviews($db,$pdf,$param,$idPaper);
  $pdf=afficheDiscussion($db,$pdf,$param,$idPaper);
  //generation du fichier pdf.
  OutputPDF($pdf); 
}



//Fonction generant un fichier PDF suivant
//des critieres de selection.
function PrintPapersList($withReviews, $id_session)
{
  //connexion a la base de Mysql
  $db = new BD (NAME, PASS, BASE, SERVER);
  
  //Requete pour recuperer les paramatres de
  //selection des papiers soumis.
  $qConfig="SELECT * ".
    "From Config"; 
  $rConfig=$db->execRequete($qConfig);
  $rConfig=$db->objetSuivant($rConfig); 

  //Requete pour recuperer les paramatres de mis en page  du fichier pdf.
  $qUpdate="SELECT * ".
    "From PDFStyle"; 
  $rUpdate=$db->execRequete($qUpdate);
  $rUpdate=$db->ligneSuivante($rUpdate);
  $param=ParamMefPdf($rUpdate);
  
  
  //Calcul du nombre de papiers sélectionnés.
  $qNbPapers="select count(*) as nbPapers from Paper where inCurrentSelection='Y';";
  $rNbPapers=$db->execRequete($qNbPapers);
  $result=$db->objetSuivant($rNbPapers);
  $nbPapers=$result->nbPapers;  
  
  
  //Informatons de base sur la conférence.  
  $qTitlePg="SELECT confName,confURL from Config";
  $rTitlePg=$db->execRequete($qTitlePg); 
  
  //Creation de la page de garde.
  $pdf=ConsHeadPage($db,$param,$rTitlePg,$nbPapers,$rConfig);		   
  
  // Sort the papers on the average 'overall' field
/*  $query = "SELECT p.id, AVG(overall) AS overall FROM Paper p, Review r "
    .  " WHERE p.id=r.idPaper AND inCurrentSelection='Y' "
    . "  GROUP BY p.id";
 */
  $query = "SELECT id FROM Paper WHERE inCurrentSelection='Y'";
  $result = $db->execRequete($query);
  
  $arrPaper = array();
  while ($paper = $db->objetSuivant($result)) {
    $arrPaper[$paper->id] = 0;//$paper->overall; 
  }
  // Sort in descending order
  arsort($arrPaper);
  reset ($arrPaper);
  
  // Informations contenues dans le fichier
  $pdf=WriteResult($pdf,$arrPaper,$param,$db,$withReviews, $id_session); 
  
  //generation du fichier pdf.
  OutputPDF($pdf); 
}



//Construction de la page de garde.
function ConsHeadPage($db,$param,$title,$nbPaper,$config) {  

    //initialisation d'un fichier pdf
    $pdf=new PDF_Index();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->SetFont('Arial','B', $param["pdfFontT"]);    
    $pdf->SetTextColor($param["pdfColT1"],$param["pdfColT2"],$param["pdfColT3"]);       
     
    $rowTitlePg=$db->ligneSuivante($title);
    
    $date=date("d.m.Y");
    $pdf->Cell(40,10,$date );
    $pdf->Ln();
  
     
    $pdf->cMargin=0;
    $pdf->SetY(65);
    $taille=(strlen($rowTitlePg['confName']))/2;
    $pdf->cMargin=40-$taille;

    $pdf->Ln();
    $pdf->Cell(190,8,$rowTitlePg['confName']);
    $pdf->Ln();
    $pdf->Cell(40,10, $rowTitlePg['confURL']);
    $pdf->Ln();
    $pdf->Cell(40,10,'Number of papers : '.$nbPaper);
    $pdf->Ln();
    
    

    //Critere de selection des papiers.
    $pdf->Ln();$pdf->Ln();$pdf->Ln();$pdf->Ln();$pdf->Ln();
   
    $pdf->SetFont('Arial','B', $param["pdfFontT"]);       
    $pdf->SetFont('', $param["pdfUndItalT"]);
    $pdf->SetTextColor($param["pdfColT1"],$param["pdfColT2"],$param["pdfColT3"]);
    $pdf->Cell(40,6,'Selected criteria');
    $pdf->Ln();$pdf->Ln();
  
  
    
    // topic 
    $pdf->Ln(); 
    $pdf->Cell(40,6,'Topic :');
    switch($config->papersWithTopic){
    case 0  : $pdf->Cell(40,6,'All ');break;
    default : 
     $qtopic="SELECT label from ResearchTopic ".
       " WHERE id=$config->papersWithTopic";
    $rtopic=$db->execRequete($qtopic);
    $rowTopic=$db->ligneSuivante($rtopic);  
    $pdf->Cell(40,6,$rowTopic['label']);
   }
  
   
    // reviewer 
   $pdf->Ln();
   $pdf->Cell(40,6,'Reviewer : ');
   switch($config->papersWithReviewer) {
   case "All" : $pdf->Cell(40,6,'All');break;
   default : 
     $qReviewer="SELECT firstName,lastName ".
                " from PCMember pc".
                " WHERE pc.email=\"$config->papersWithReviewer\" ";
    $rReviewer=$db->execRequete($qReviewer);
    $rowReview=$db->ligneSuivante($rReviewer);  
    $fName=$rowReview['firstName'];
    $lName=$rowReview['lastName'];
    $pdf->Cell(40,6,$fName.' '.$lName);

   } 

   // filter 
    $pdf->Ln();
    $pdf->Cell(40,6,'Filter');
    switch($config->papersWithFilter){
    case "1" : $pdf->Cell(40,6,'above');break; 
    case "2" : $pdf->Cell(40,6,'below'); 

    }
    $pdf->Cell(40,6,$config->papersWithRate);
    
 
   
    // conflicting paper 
    $pdf->Ln();
    switch($config->papersWithConflict){
    case "A" : $pdf->Cell(40,6,'Conflicting paper : Any');break; 
    case "Y" : $pdf->Cell(40,6,'Conflicting paper : Yes');break; 
    case "N" : $pdf->Cell(40,6,'Conflicting paper : No');
   }   


    // Status     
    $pdf->Ln();
    $pdf->Cell(40,6,'Status : ');
    if ($config->papersWithStatus==SP_ANY_STATUS) {$pdf->Cell(40,6,'Any');}
    elseif ($config->papersWithStatus==SP_NOT_YET_ASSIGNED) {$pdf->Cell(40,6,'Not yet assigned');}
    else {
      $qStatus="SELECT label from PaperStatus where id='$config->papersWithStatus';";
      $rStatus=$db->execRequete($qStatus);
      $status=$db->objetSuivant($rStatus);
      $pdf->Cell(40,6,$status->label);
    }
    

    // Papers with missing review
    $pdf->Ln();
    
    switch($config->papersWithMissingReview){
    case "A" : $pdf->Cell(40,6,'Missing reviews : Any');break;
    case "Y" : $pdf->Cell(40,6,'Papers with missing reviews');break;
    case "N" : $pdf->Cell(40,6,'Papers without missing reviews');
    }

    $pdf->setY(270);
    $pdf->Cell(20,6,'The MyReview system, http://myreview.lri.fr');

    $pdf->cMargin=0;
    $pdf->SetLeftMargin($param["pdfLeftMG"]);
    $pdf->SetRightMargin($param["pdfRightMG"]);
    
    if ( $nbPaper != 0 )
    $pdf->AddPage();
  

    return $pdf;
}

?>