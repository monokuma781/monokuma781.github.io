<?php
/**********************************************
   The MyReview system for web-based conference management
 
   Copyright (C) 2003-2006 Philippe Rigaux
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation;
 
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
 
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
************************************************/
 
// Extracts small texts from an XML file and put
// them in an internal array

class CText
{
  // The SAX parser
  var $parser;
  // The array that stores the texts
  var $texts;
  // Global SAX variables
  var $current_element, $current_pcdata;

  // Constructor
  function CText ($file_name="SmallTexts.xml")
  {
    // Instanciate the SAX parser
    $this->parser = xml_parser_create();

    // Triggers = methods
    xml_set_object($this->parser, $this);

    // Put all tags and attributes in uppercase
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);

    // Assign triggers
    xml_set_element_handler ($this->parser, "startElement", "endElement");
    xml_set_character_data_handler ($this->parser, "pcdataHandling");

    // Initialize the array
    $this->texts = array();
    
    // Parse
    $this->parse($file_name);
  } 

  // Triggers
   function startElement ($parser, $name, $attrs)
  {
    $this->current_element = $name;
    $this->texts[$this->current_element] = "";
  }

   function endElement ($parser, $name)
  {
    // Nothing to do!
    $this->current_element = "TEXTS";
  }

   function pcdataHandling ($parser, $pcdata)
  {
    // if (!empty($chaine))  $this->donnees .= $chaine;
    if ($this->current_element != "TEXTS") // Do not take the root element
      {
	$this->texts[$this->current_element] .= $pcdata;
      }
  }

  // Parse method
   function parse($file) 
  {
    // Open the XML file
    if ( !($f = fopen($file, "r"))) 
      {
	$this->error ("ERROR: Unable to open file: $file");
	return;
      } 
  
    // Scan the document
    while ($data = fread($f, 4096)) 
      {
	if (!xml_parse($this->parser, $data, feof($f)))
	  {
	    $this->error (" line " . xml_get_current_line_number($this->parser)
                         . " of $file:"
			 . xml_error_string(xml_get_error_code($this->parser)));
	    return;
	  }
      }
    fclose ($f);
  }

  // Destructor PHP 5 only
  /*  function __destruct() 
  {
    xml_parser_free($this->parser);
  }*/

  function error ($message)
  {
    echo "<font color=red>SAX ERROR: $message</font><p>";
  }

  /*********** Public part  *********************/

   function get($text_key)
  {
   if (isSet($this->texts[$text_key]))
      return $this->texts[$text_key];
    else
      return $text_key;
  }
}
