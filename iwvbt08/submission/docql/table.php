<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2006
 */

 Class Table
 {
 	var $type;
	var $attributs=array();
	var $nb_attributs;
	var $associations=array();
	var $refs=array();
	var $PrimaryKey;
	var $Pkeys=array();
	var $nbPKeys;


	function Table(){
		//$this->type="";
		$this->nb_attributs=0;
		$this->nbPKeys=0;
		//$this->PrimaryKey="";
	}
/*	  function Table($name,$PK)
	{
		$this->type=$name;
		$this->nb_attributs=0;
		$this->PrimaryKey=$PK;
	}*/

	function SetPrimaryKey($PK)
	{
		$this->PrimaryKey=$PK;
	}
	function AddPKey($pkey){
		$this->Pkeys[$this->nbPKeys]=$pkey;
		$this->nbPKeys++;
	}
	function SetName($n)
	{
		$this->type=$n;
	}
 	  function GetName()
	{
		return $this->type;
	}

	  function GetRefs()
	{
		return $this->refs;
	}
 	  function add_attribut($attr)
	{
		$this->attributs[]=$attr;
		$this->nb_attributs++;
	}

	  function add_ref($ref)
	{
		$this->refs[]=$ref;
	}
	  function GetResult()
{
	print_r(get_object_vars($this));
}
		  function GetAssociation()
	{
		return $this->associations;
	}
	  function GetPrimarykey()
	{
		return $this->Pkeys;
	}
	function GetNbPKeys(){
		return $this->nbPKeys;

	}
	  function GetSeondaryKey($n_tab)
	{
		return $this->associations[$n_tab];
	}

	  function isset_table($n_t)
	{

		//$x=var_dump(array_key_exists( $n_t,$this->associations));
		$x=isset($this->associations[$n_t]);
		return $x;
		/*if (isset($this->asociations[$n_t]))
			return true;
		else
		return false;	*/
	}
 }
?>
