<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2006
 */

  require_once('Rule.php');
  require_once('QTreeNode.php');
  //require_once('Table_.php');
  require_once('table.php');
  require_once('BD.php');
  require_once('Reference.php');
  require_once('xmlfile.php');


Class Docql
  {
  	var $Base;
  	var $Q;
  	var $requete;


function Docql($bd)
{
	$this->Base=$bd;
	$this->Q=new QTreeNode();
	$this->requete="";

}
function ParseFile($path,$filename)
{
  $myFile = $path.$filename;
  $fh=fopen($myFile, "r");
  if($fh==null)

       // Impossible !! on lève une exception.
       throw new Exception ('Impossible to open the file ' . $myFile);

  else
  	{
		  $req= fread($fh, filesize($myFile));
		  $this->requete=$req;
		  // echo "la requete est:\n $req  \n";
	  	return true;
	 }
}

function message ($message)
{
     // Just output an HTML message
     echo "<B>Error:</B> $message<BR>\n";
}

function Execute()
{
	try{

	//Phase of parsing
  $this->Q->parse_QTreeNode($this->requete,$this->Q);

  //Set the "Eval.i" in the body of each QtreeNode
  $this->Q->do_eval_body();
  $this->Q->SetExtra($this->requete);

  //Rewriting the graph in order to eliminate redudant sibling
 // $this->Q->rewriting_redudant_sibling($this->Q);

  //connect the Qtreenode and his sons to the base
  $this->Q->connect($this->Base);

  //Set the contexte of the initial QtreeBNode=> execute the query select * from path....
  $this->Q->SetContexte();

  //Set the schema of the QtreeNode
  $schema=$this->ProduceSchema();

  $this->SetSchema($schema);

  //Phase of Evaluation
	$this->Q->Evaluation();

  //returning the results
  if($this->Q->GetRule()!=null){
  	$begin=$this->Q->GetRule()->GetParent();
  	$end=$this->Q->GetExtra();
    $resultat=$begin.$this->Q->GetRule()->GetBody().$end;
    }
  else
	throw new Exception ('  ERROR OCCURED IMPOSSIBLE TO BROWSE A RESULT' );

  return $resultat;
}
catch(Exception $e){
	 echo $e ->getMessage();
}
}
function WriteFile($path,$filen,$type,$result){
  $filename=".//"."result_".$filen.$type;
//  echo $filename;
  $myFile = $path.$filename;
 if (file_exists($filename))
 	$fh=fopen($myFile, "r+");
 else
  	$fh=fopen($myFile, "x+");

 if($fh==null)

       // Impossible !! on lève une exception.
       throw new Exception ('Impossible to open the file ' . $myFile. '!!  ');

  else
  	{
		if (fwrite($fh, $result) === FALSE) {
    	   echo "Impossible to write in file ($filename)";
    	   }

   }

}
function SetSchema($schema)
{

  	$this->Q->set_schema($schema);
	$this->Q->set_sons_schema($schema);
}

 function ProduceSchema()
 {

 $schema =& new SchemaProcessor();
 $success = $schema->ProcessSchema("myreview.xml");
 $tables=$schema->tables;

 $schemaB=new Schema_Base();
 for($k=0;$k<count($tables);$k++)
		$schemaB->add_table($tables[$k]);

return $schemaB;
 }

 function SendtoMail($destinataire,$objet,$result){
 // Envoie du résultat sous forme de mail
	$headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\n";
  //  $headers .= "From:  <sonia.guehis@lamsade.dauphine.fr>\n";


    $message = "this is the result of the query.\n" ;
    $message = $result;

  // On envoi l’email
   if ( mail($destinataire, $objet, $message) )
   		 echo "Envoi du mail réussi.";
   else
   		throw new Exception ('Cannot send the mail ' );


 }
 function parselatexresult($result){
 	$r="";
	for($i=0;$i<strlen($result);$i++)
	{
		if($result[$i]=="+")
			$r=$r."{";
		else
		{
			if($result[$i]=="-")
				$r=$r."}";
			else
				$r=$r.$result[$i];
		}

	}
	return $r;
 }
 function GenerateLatex($disk,$path,$filename,$res){

$result=$this->parselatexresult($res);
echo $result;
 // $filename="result";
  $chemin=".\\"; // Le chemin est pris à partir de l'emplacement d'où est appelé le script.

 // $tex = "\\documentclass{}";
  $tex=$result;
 // $tex.="\\end{document};";

  $f=fopen ("{$chemin}{$filename}.tex", "w+"); //ouverture du fichier tex en écriture
  if($f==null)
    	echo "Error while opening the file!!";
  else
    fwrite ($f, $tex);
  	fclose ($f);

// A partir de ce point, le fichier tex est pile poil comme il faut... Faisons-en un pdf !
	$res=system("disk && cd {$path} ");
	$res=system("latex {$filename}.tex");
	system("dvipdfm result.dvi");
//	system("result.pdf");


//    system ("cd {$chemin} && texi2pdf --pdf -c -q {$filename}.tex");
    //Si la commande texi2pdf s'est bien déroulée, nous avons à présent un beau pdf !
   if (file_exists ("{$chemin}{$filename}.pdf"))
         printf ("window.open ('%s%s.pdf');", $chemin, $filename);

	return $res;
}

}
?>
