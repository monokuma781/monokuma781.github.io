<?php
require_once ("../DBInfo.php");
require_once ("../BD.class.php");
require_once ("./jpgraph/jpgraph.php");
require_once ("./jpgraph/jpgraph_pie.php");
require_once ("./jpgraph/jpgraph_pie3d.php");

//connexion a la base de Mysql
$db = new BD (NAME, PASS, BASE, SERVER);
NbPapersByTopics($db);

function NbPapersByTopics($db){
  $nbPapers= array();
  $labels=array();
  $qTopics="select * from ResearchTopic;";
  $rTopics = $db->execRequete($qTopics);
  while ($topic=$db->objetSuivant($rTopics)) {
    $nbPapers[$topic->id]=0;
    $labels[$topic->id]=$topic->label;
  }
  
  $qTopics = "select t.id as id, count(topic) as Nb ".
    "from ResearchTopic t, Paper p where t.id=topic group by topic;";
  $rTopics = $db->execRequete($qTopics);
  while ($topic=$db->objetSuivant($rTopics)) {
    $nbPapers[$topic->id]+=$topic->Nb; 
  }
  
  $nbP=array();
  $labelP=array();
  foreach ($nbPapers as $id => $nb){
    $nbP[]=$nb;
    $labelP[]=$labels[$id];
  }  
  
  // Create the Pie Graph.
  $graph = new PieGraph(400,200,"auto");
  $graph->SetShadow();
  
  // Set A title for the plot
  $graph->title->Set("Topics of papers");
  //$graph->title->SetFont(FF_VERDANA,FS_BOLD,18); 
  $graph->title->SetColor("darkblue");
  $graph->legend->Pos(0.04,0.2);
  
  // Create pie plot
  $p1 = new PiePlot3d($nbP);
  $p1->SetTheme("sand");
  $p1->SetCenter(0.4);
  $p1->SetAngle(30);
  //$p1->value->SetFont(FF_ARIAL,FS_NORMAL,12);
  $p1->SetLegends($labelP);
  
  $graph->Add($p1);
  $graph->Stroke();
}
?>