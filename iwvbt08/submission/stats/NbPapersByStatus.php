<?php
require_once ("../DBInfo.php");
require_once ("../BD.class.php");
require_once ("./jpgraph/jpgraph.php");
require_once ("./jpgraph/jpgraph_bar.php");
//connexion a la base de Mysql
$db = new BD (NAME, PASS, BASE, SERVER);
NbPapersByStatus($db);

function NbPapersByStatus($db){
  $nbPapers= array();
  $status=array();
  $qStatus="select * from PaperStatus;";
  $rStatus = $db->execRequete($qStatus);
  while ($status=$db->objetSuivant($rStatus)) {
    $nbPapers[$status->id]=0;
    $labels[$status->id]=$status->label;
  }
  
  $qNbPapers="select status, count(*) as nbPaper from Paper group by status;";
  $rNbPapers=$db->execRequete($qNbPapers);
  while ($nbP=$db->objetSuivant($rNbPapers)) {
    $nbPapers[$nbP->status]+=$nbP->nbPaper; 
  }

  $nbPap=array();
  $status=array();
  foreach ($nbPapers as $idStatus => $nb){
    $nbPap[]=$nb;
    $status[]=$labels[$idStatus];
  }  

  // Setup the graph. 
  $graph = new Graph(400,200,"auto");	
  $graph->img->SetMargin(60,20,30,50);
  $graph->SetScale("textlin");
  $graph->SetMarginColor("lightblue");
  $graph->SetShadow();
  
  // Set up the title for the graph
  $graph->title->Set("Nb of Papers by status");
  //$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
  $graph->title->SetColor("darkred");
  
  // Setup font for axis
  //$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
  //$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
  
  // Show 0 label on Y-axis (default is not to show)
  $graph->yscale->ticks->SupressZeroLabel(false);
  
  // Setup X-axis labels
  $graph->xaxis->SetTickLabels($status);
  //$graph->xaxis->SetLabelAngle(50);
  
  // Create the bar pot
  $bplot = new BarPlot($nbPap);
  $bplot->SetWidth(0.6);
  $bplot->SetValuePos('center');
  $bplot->value->SetFormat("%d");
  $bplot->value->Show();
  // Setup color for gradient fill style 
  $bplot->SetFillGradient("navy","#EEEEEE",GRAD_LEFT_REFLECTION);
  
  // Set color for the frame of each bar
  $bplot->SetColor("white");
  $graph->Add($bplot);
  
  // Finally send the graph to the browser
  $graph->Stroke();
}
?>