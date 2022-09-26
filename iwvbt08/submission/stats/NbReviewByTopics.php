<?php
require_once ("../DBInfo.php");
require_once ("../BD.class.php");
require_once ("./jpgraph/jpgraph.php");
require_once ("./jpgraph/jpgraph_bar.php");
//connexion a la base de Mysql
$db = new BD (NAME, PASS, BASE, SERVER);
NbReviewByTopics($db);

function NbReviewByTopics($db){
  $nbReviews= array();
  $labels=array();
  $qTopics="select * from ResearchTopic;";
  $rTopics = $db->execRequete($qTopics);
  while ($topic=$db->objetSuivant($rTopics)) {
    $nbReviews[$topic->id]=0;
    $labels[$topic->id]=$topic->label;
  }

  $qNbReviews="select topic, count(idPaper) as NbReview ".
    "from Review, Paper p where p.id=idPaper group by idPaper;";
  $rNbReviews=$db->execRequete($qNbReviews);
  while ($nbR=$db->objetSuivant($rNbReviews)) {
    $nbReviews[$nbR->topic]+=$nbR->NbReview; 
  }

  $nbRev=array();
  $topic=array();
  foreach ($nbReviews as $idTopic => $nb){
    $nbRev[]=$nb;
    $topic[]=$labels[$idTopic];
  }  

  // Setup the graph. 
  $graph = new Graph(400,200,"auto");	
  $graph->img->SetMargin(60,20,30,50);
  $graph->SetScale("textlin");
  $graph->SetMarginColor("lightblue");
  $graph->SetShadow();
  
  // Set up the title for the graph
  $graph->title->Set("Nb of reviewers by topic");
  //$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
  $graph->title->SetColor("darkred");
  
  // Setup font for axis
  //$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
  //$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
  
  // Show 0 label on Y-axis (default is not to show)
  $graph->yscale->ticks->SupressZeroLabel(false);
  
  // Setup X-axis labels
  $graph->xaxis->SetTickLabels($topic);
  //$graph->xaxis->SetLabelAngle(50);
  
  // Create the bar pot
  $bplot = new BarPlot($nbRev);
  $bplot->SetWidth(0.6);
  
  // Setup color for gradient fill style 
  $bplot->SetFillGradient("navy","#EEEEEE",GRAD_LEFT_REFLECTION);
  
  // Set color for the frame of each bar
  $bplot->SetColor("white");
  $graph->Add($bplot);
  
  // Finally send the graph to the browser
  $graph->Stroke();
}
?>