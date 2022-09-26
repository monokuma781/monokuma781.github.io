<?php
require_once ("../DBInfo.php");
require_once ("../BD.class.php");
require_once ("../functions.php");
require_once ("./jpgraph/jpgraph.php");
require_once ("./jpgraph/jpgraph_line.php");

//connexion a la base de Mysql
$db = new BD (NAME, PASS, BASE, SERVER);
NbSubmittedReviews($db);

function NbSubRev($tabDates, $step, $db){
  $compteur=0;
  foreach($tabDates as $id => $date) {
    if (strtotime($date)<=$step) $compteur+=1;  
  }
  return $compteur;
}

function NbSubmittedReviews($db){

  $qDates="select submissionDeadline as depart, reviewDeadline as fin from Config;";
  $rDates = $db->execRequete($qDates);
  $dates=$db->objetSuivant($rDates);
  
  $qNbReview="select count(*) as nbR from Review;";
  $rNbReview = $db->execRequete($qNbReview);
  $nbR = $db->objetSuivant($rNbReview);

  $qSubDates = "select submissionDate as date from Review ".
    "where submissionDate<>\"\" order by submissionDate;";
  $rSubDates=$db->execRequete($qSubDates);
  
  $tabDates=array();
  while($subdate=$db->objetSuivant($rSubDates)) {
    $tabDates[]=$subdate->date;
  } 

  $l1datay = array();
  $l2datay = array();
  $datax = array();

  $l1datay[]=$nbR->nbR;
  $l2datay[]=NbSubRev($tabDates, strtotime($dates->depart), $db);
  $datax[]=DBtoDisplay($dates->depart);
 
  $nbPas=30;
  $pas=(strtotime($dates->fin)-strtotime($dates->depart))/$nbPas;

  for( $i=1; $i < $nbPas; $i += 1 ) {
    $l1datay[]=$nbR->nbR;
    $l2datay[]=NbSubRev($tabDates, strtotime($dates->depart)+$i*$pas, $db);
    $datax[]="";
  }
  $l1datay[]=$nbR->nbR;
  $l2datay[]=NbSubRev($tabDates, strtotime($dates->fin), $db);
  $datax[]=DBtoDisplay($dates->fin);

  // Create the graph. 
  $graph = new Graph(400,200,"auto");	
  $graph->SetScale("textlin");
  $graph->yaxis->scale->SetGrace(2,2);
  $graph->img->SetMargin(40,130,20,40);
  $graph->SetShadow();
  
  // Create the linear error plot
  $l1plot=new LinePlot($l1datay);
  $l1plot->SetColor("red");
  $l1plot->SetWeight(2);
  $l1plot->SetLegend("Total\nassignement");
  
  // Create the bar plot
  $l2plot = new LinePlot($l2datay);
  $l2plot->SetFillColor("orange");  
  $l2plot->SetLegend("Nb reviews");
  
  // Add the plots to the graph
  $graph->Add($l2plot);
  $graph->Add($l1plot);
  
  $graph->title->Set("Nb of submitted Reviews");
  //$graph->xaxis->title->Set("");
  //$graph->yaxis->title->Set("Submitted Reviews");  
  
  $graph->xaxis->SetTickLabels($datax);
  
  // Display the graph
  $graph->Stroke();
}

?>