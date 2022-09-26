<?php
require_once ("../DBInfo.php");
require_once ("../BD.class.php");
require_once ("../functions.php");
require_once ("./jpgraph/jpgraph.php");
require_once ("./jpgraph/jpgraph_line.php");


//connexion a la base de Mysql
$db = new BD (NAME, PASS, BASE, SERVER);
NbPapers($db);

function NbSubRev($tabDates, $step, $db){
  $compteur=0;
  foreach($tabDates as $id => $date) {
    if (strtotime($date)<=$step) $compteur+=1;  
  }
  return $compteur;
}

function NbPapers($db){
  $qFirstPaper = "select min(submissionDate) as depart from Paper;";
  $rFirstPaper = $db->execRequete($qFirstPaper);
  $firstPaper = $db->objetSuivant($rFirstPaper);

  $qSubDeadline = "select submissionDeadline as fin from Config;";
  $rSubDeadline = $db->execRequete($qSubDeadline);
  $subDeadline = $db->objetSuivant($rSubDeadline);

  $qSubDates = "select submissionDate as date from Paper ".
    "where submissionDate<>\"\" order by submissionDate;";
  $rSubDates=$db->execRequete($qSubDates);
  
  $tabDates=array();
  while($subdate=$db->objetSuivant($rSubDates)) {
    $tabDates[]=$subdate->date;
  }  

  $datay = array();
  $datax = array();

  $datay[]=NbSubRev($tabDates, strtotime($firstPaper->depart), $db);
  $datax[]=DBtoDisplay($firstPaper->depart);
 
  $nbPas=30;
  $pas=(strtotime($subDeadline->fin)-strtotime($firstPaper->depart))/$nbPas; 

  for( $i=1; $i < $nbPas; $i += 1 ) {
    $datay[]=NbSubRev($tabDates, strtotime($firstPaper->depart)+$i*$pas, $db);
    $datax[]="";
  }
    
  $datay[]=NbSubRev($tabDates, strtotime($subDeadline->fin), $db);
  $datax[]=DBtoDisplay($subDeadline->fin);

  // Create the graph. 
  $graph = new Graph(400,200,"auto");	
  $graph->SetScale("textlin");
  $graph->yaxis->scale->SetGrace(2,2);
  $graph->img->SetMargin(40,40,20,40);
  $graph->SetShadow();
  
  // Create the linear error plot
  $plot=new LinePlot($datay);
  $plot->SetColor("red");
  $plot->SetWeight(2);
  //$plot->SetLegend("Submitted Papers");  

  // Add the plots to the graph 
  $graph->Add($plot);
  
  $graph->title->Set("Nb of submitted papers");
  //$graph->xaxis->title->Set("");
  //$graph->yaxis->title->Set("Submitted Papers");  
  
  $graph->xaxis->SetTickLabels($datax);
  
  // Display the graph
  $graph->Stroke();
}
?>