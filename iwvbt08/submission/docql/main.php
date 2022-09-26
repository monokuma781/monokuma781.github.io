<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2005
 */

require_once('Docql.php');

$bd=new BD("adminReview","mdpAdmin","Review","localhost");

$dql=new Docql($bd);

try {
  $dql -> parseFile("",".//pc.dql");
}
catch (Exception $e) {
// Une exception a été levée, on affiche le message d'erreur via la méthode Exception :: getMessage()
  echo $e ->getMessage();
}
try{
  $result = $dql->Execute();
  if($result!=null)
    echo "le resultat est:\n$result\n";
}
catch(Exception $e){
  echo $e->getMessage();
}
try {
  $dql->writeFile("","exemple1",".htm",$result);
}

// Une exception a été levée, on affiche le message d'erreur via la méthode Exception :: getMessage()
catch (Exception $e) {
  echo $e ->getMessage();
}
try {
  //il faut configurer dans le php.ini le sendfrom ????@???.com
  //	$dql -> SendtoMail($result);
}

// Une exception a été levée, on affiche le message d'erreur via la méthode Exception :: getMessage()
catch (Exception $e) {
  echo $e ->getMessage();
}

//send the result by mail...
//$destinataire = "sonia.guehis@dauphine.fr";
//   $objet = "Mail by Docql !" ;
//  $dql->SendtoMail($destinataire,$objet,$result);

//Generate the latex version of the file
//	$res=$dql->GenerateLatex("e","\code projet versions\code 26 avec reference",fichier_resultat,$result);

?>