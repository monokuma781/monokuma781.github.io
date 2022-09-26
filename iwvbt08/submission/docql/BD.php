<?php
/**********************************************
   The MyReview system for web-based conference management

   Copyright (C) 2003-2004 Philippe Rigaux
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

  // This class encapsulates the MySQL PHP API
  class BD
  {
    // ----   Private part: properties

    var $connexion, $erreurRencontree=0, $base;

    // Object constructor

    function BD ($login, $motDePasse, $base, $serveur)
    {
      // Connect to the server
      $this->connexion = @mysql_pconnect ($serveur, $login, $motDePasse);

      if (!$this->connexion)
       $this->message("Sorry, unable to connect to $serveur\n");

      // Connnect to the DB
      if (!@mysql_select_db ($base, $this->connexion))
      {
        $this->message ("Sorry, unable to access to the DB $base\n");
        $this->message ("<B>MySQL says: </B>" .
                             mysql_error($this->connexion));
        $this->erreurRencontree = 1;
      }

      $this->base = $base;
      // End of constructor
    }

    // ---- Private part: methods

    // Shows a message
    function message ($message)
    {
     // Just output an HTML message
     echo "<B>Error:</B> $message<BR>\n";
    }

    // ---- Public part

    // Execute a query
    function execRequete ($requete)
    {
      $resultat = mysql_query ($requete, $this->connexion);

      if (!$resultat)
      {
       $this->message ("Problem when executing query: $requete");
       $this->message ("<B>MySQL says: </B>" .
                             mysql_error($this->connexion));
       $this->erreurRencontree = 1;
      }
      return $resultat;
    }
    // Get the next object
    function objetSuivant ($resultat)
    {      return  mysql_fetch_object ($resultat);    }

    // Get the next assoc. array
    function ligneSuivante ($resultat)
    {   return  mysql_fetch_assoc ($resultat);  }

    // Get the next array
    function tableauSuivant ($resultat)
    {   return  mysql_fetch_row ($resultat);  }

    // Check whether an error has been met
    function enErreur ()
    {  return  $this->erreurRencontree;   }

    // Get the id of the last inserted row
    function idDerniereLigne ()
    {  return  mysql_insert_id(); }

    // How many attributes in the result
    function nbrAttributs ($res)
      {  return  mysql_num_fields ($res); }

    // Get the name of an attribute
    function nomAttribut ($res, $position)
    {
      // Check position
      if ($position < 0 or $position >= $this->nbrAttributs($res))
      {
        $this->message ("No attribute at pos $position");
	return "Unknown";
      }
      else return  mysql_field_name ($res, $position);
    }

    // Get the schema of a table
    function schemaTable ($tableName)
    {
      return mysql_list_fields ($this->base, $tableName);
    }

    // Free
    function freeResult ($result)
    {      @mysql_free_result ($result);    }

   // Disconnect
    function quitter ()
    {      @mysql_close ($this->connexion);
	}
    //get the columns of a table
    function GetColumns($tableName){

    	 $result = mysql_query("SHOW COLUMNS FROM ".$tableName);
		if (!$result) {
  			 echo 'Impossible to execute the query : ' . mysql_error();
   			exit;
		}
		if (mysql_num_rows($result) > 0) {
  		 		while ($row = mysql_fetch_assoc($result)) {
    	 			$fields[]=$row->name;//[Field];
   				}
   		}
   		return $fields;
    }

    function GetTables(){
    	$sql = "SHOW TABLES FROM ".$this->base;
		$result = mysql_query($sql);

		if (!$result) {
 			  echo "Erreur DB, impossible de lister les tables\n";
 			  echo 'Erreur MySQL : ' . mysql_error();
 			  exit;
		}

		while ($row = mysql_fetch_row($result)) {
  			// echo "Table : {$row[0]}\n";
  			 $tables[]=$row[0];
		}

		mysql_free_result($result);
		return $tables;

    }


    // End of the class
 }
?>
