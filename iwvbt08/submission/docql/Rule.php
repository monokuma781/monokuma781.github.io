<?php
/**
 *
 *
 * @version $Id$
 * @copyright 2005
 */
Class Rule
{
	var $parent ;
	var $path ;
	var $Body ;
	var $resultat ;
	var $default ;
	var $sep1;//separator
	var $sep2;
//********************************************       GETTERS         ******************************/
  function GetResult()
{
	print_r(get_object_vars($this));
}
  function GetBody()
{
	return $this->Body;
}
  function GetPath()
{
	return $this->path;
}
function GetLenghtBody()
{
	return $this->lenght_body;
}

function GetParent(){
return $this->parent;

}


public function GetFirstNode(){
	$pos=strpos($this->GetPath(),".");
	if($pos!=false)
		$FirstNode=substr($this->path,0,$pos);
	else
		$FirstNode=$this->path;
	return $FirstNode;

}
public function DeleteFirstNode(){
	$pos=strpos($this->GetPath(),".");
	if($pos!=false)
		$this->SetPath(substr($this->GetPath(),$pos+1,strlen($this->GetPath())-$pos));

}
//********************************************       SETTERS         ******************************/
  function SetBody($b)
{
	$this->Body=$b;
}
function SetPath($p)
{
	$this->path=$p;
}
 function SetParent($p){
 	$this->parent=$p;
 }
  function initiate()
{
	$this->path="";
	$this->Body="";
	$resultat="";
	$default="";
}
    function parse($requete)
{
	//$requete=$this->delete_blank($requete);

	$pos_arobas=strpos($requete,"@"); //renvoie la 1ere position de @
	$this->parent=substr($requete,0,$pos_arobas);
	$pos_acc_ouv=strpos($requete,"{");
	$pos_acc_fer=strrpos($requete,"}");  // renvoie la position de la derniere occurance de }
	$this->path=substr($requete,$pos_arobas+1,$pos_acc_ouv-$pos_arobas-1);
	//-1
	$def=strrpos($requete,"} {"); //position de }{; renvoie faux si inexistant
	$chaine_tronquee=substr($requete,$pos_acc_fer+1,strlen($requete)-$pos_acc_fer-1);
	$sous_chaine=substr($requete,$def+2,$pos_acc_fer-$def-2);
	$acc=strpos($sous_chaine,"}'");
	if ($def!=FALSE and $acc==FALSE)
	{
		$this->Body=substr($requete,$pos_acc_ouv+1,$def-$pos_acc_ouv-1);
		$chaine=substr($requete,$def+2,strlen($requete)-$def-1);
		//echo"la chaine est:<b>$chaine\n";
		$pos_acc_fer=strpos($chaine,"}");
		//echo"la position de }est:<b>$pos_acc_fer\n";
		$this->default=substr($chaine,1,$pos_acc_fer-1);
		$chaine_tronquee=substr($chaine,$pos_acc_fer+1,strlen($chaine)-$pos_acc_fer-1);
	}
	else
		$this->Body=substr($requete,$pos_acc_ouv+1,$pos_acc_fer-$pos_acc_ouv-1);
	if(trim($this->Body)==".")
			$this->Body="#Evalself";


	$this->lenght_body=strlen($this->Body);
	return $chaine_tronquee;
}


}

?>
