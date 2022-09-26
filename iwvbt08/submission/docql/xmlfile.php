<?php
//require_once("table.php");
require_once("Reference.php");
class SchemaProcessor {

   var $nbtables = 0;
   var $tables = array();
   var $last_attribute =array();

   var $attributes=array();
   var $nb_attributes;

   var $quantity = 0;
   var $unitprice = 0;
   var $currentElement = "";
   var $lastElement= "";
   var $thisText = "";
   var $ref;
   var $fkey;

   function SchemaProcessor(){

	   }

   function ProcessSchema($url) {

      $parser = xml_parser_create();
      xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
      xml_set_object($parser, $this);

      xml_set_element_handler($parser, "_startElement", "_endElement");
      xml_set_character_data_handler($parser, "_charHandler");

      $this->_startDocument($parser);

      $fp = fopen($url, "r");
      while(!feof($fp)) {
         $line = fgets($fp, 4096);
         xml_parse($parser, $line);
      }
      fclose($fp);

      $this->_endDocument($parser);

      xml_parser_free($parser);

   }

   function _startDocument($parser){
      $this->nbtables = 0;
   }

   function _endDocument($parser){
  //    echo("<br />Nb of Tables: ".$this->nbtables."<br />");
   }

	function insertTable($table){
		$nb=$this->nbtables;
		$this->tables[$nb]=$table;
		$this->nbtables++;

	}

   function _startElement($parser, $name, $attrs) {


      if ($name == "table"){
         $table=new Table();
      	 $table->SetName($this->deleteBlank($attrs['name']));
      	 $this->insertTable($table);
       //  $customerid = $attrs["customerNumber"];
       //  echo("Table :<br /><br />");

      } else if ($name == "primarykey" ){
      		$a=$this->deleteBlank($attrs['name']);
         $this->tables[$this->nbtables-1]->SetPrimaryKey($a) ;
      }
      else if ($name=="reference") {

      	  $this->ref=new reference($attrs['table']);
      	 // $this->tables[$this->nbtables-1]->add_ref($this->ref);
      }

//	  $this->lastElement= $this->currentElement;
      $this->currentElement = $name;


   }

   function _endElement($parser, $name) {

      if (strlen($this->thisText) > 0) {
         if ($name == "name"){
            $this->tables[$this->nbtables-1]->add_attribut($this->deleteBlank($this->thisText)) ;
         } else if ($name == "key"){
            $this->tables[$this->nbtables-1]->addPKey($this->deleteBlank($this->thisText));
         } else if ($name == "fkey"){
            $this->fkey = $this->deleteBlank($this->thisText);
         }else if ($name == "fattribute"){
           if($this->fkey!=null)
		   	 $this->ref->add_link($this->fkey, $this->thisText);
		}
         $this->thisText = "";

      }
      if ($name == "association"){
      	$this->fkey="";
		 $this->tables[$this->nbtables-1]->add_ref($this->ref);
         $this->ref=null;;
      }

   }

   function _charHandler($parser, $data) {
//   echo"texte:".$this->thisText;
   $x=$data;
	if(ord($data)!=10)
      $this->thisText= $this->thisText.$data;
     else{
          $code=ord($data);
	}
   }

 function deleteBlank($chaine)
{

	$i=0;
	$chaine_res="";
	$x=false;
	while($i<strlen($chaine))
	{
		if($chaine[$i]!=" " )
			{
			$chaine_res=$chaine_res.$chaine[$i];}
			$i++;

	}
	return $chaine_res;
}
}





?>