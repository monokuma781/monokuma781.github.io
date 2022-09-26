<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2006
 */
Class Reference
{
	var $tab_name;
	var $links=array();

	  function Reference($tab)
	{
		$this->tab_name=$tab;
	}
	  function add_link($id1,$id2)
	{
		$this->links[$id1]=$id2;
	}
	  function GetTabName()
	{
		return $this->tab_name;
	}
	  function GetLinks()
	{
		return $this->links;
	}

}

?>