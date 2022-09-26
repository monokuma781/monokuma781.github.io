<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2006
 */

  Class Schema_Base
  {
  	var $Tables=array();


	  function add_table($t)
	{
		$this->Tables[]=$t;
	}

	  function Get_table($name)
	{
		$t=null;
		for($i=0;$i<count($this->Tables);$i++)
		{
			$n=$this->Tables[$i]->GetName();
			if($n==$name)
				$t=$this->Tables[$i];

		}
		return $t;
	}
	  function Merge_Ids($tab_id1,$tab_id2,$tab1,$tab2)
	{
		$req="";
		for($i=0;$i<count($tab_id1);$i++)
		{
			$req=$req.$tab1.".".$tab_id1[$i]."=".$tab2.".".$tab_id2[$i];
			if ($i<count($tab_id1)-1)
			 {
			  	$req=$req." and ";
			 }
		}
		return $req;
	}

	public function tab_in_reference($n_tab,$refs)
	{
		for($k=0;$k<count($refs);$k++)
		{
			if($refs[$k]->GetTabName()==$n_tab)
				return true;
			else
				return false;
		}
	}
//this function return a sequence of tables wich can contribute to the join of tab1 and tab2
//example, (tab1,tab3) and (tab2;tab3) so it return tab3.

	function Get_reference($t1,$t2)
	{
		$tab1=$this->Get_table($t1);
		$tab2=$tab=$this->Get_table($t2);
		if($tab1!=null and $tab2!=null)
		{
			$refs1=$tab1->GetRefs();
			if($refs1!=null)
			{
				$res=$this->tab_in_reference($t2,$refs1);
				if($res)
					$tab_ref[]=$t1;
				else
				{
					for($i=0;$i<count($refs1);$i++)
					{
						$n_t=$refs1[$i]->GetTabName();
						$tab_ref_ref=$this->Get_reference($n_t,$t2);
						if($tab_ref_ref!=null)
						{
							for($j=0;$j<count($tab_ref_ref);$j++)
							$tab_ref[]=$tab_ref_ref[$j];
						}
						else
							$tab_ref=null;
					}
				}
			}
			else
			{
				return null;
			}

 	return $tab_ref;
		}
		else
		{
		//	throw new Exception (' Impossible to found the object associated to the table ' . $t1." and ". $t2.' please verify the syntax ');
			return null;
		}

	}


	  function associations($tabs)
	{
		$req="";
		$s=0;
		for($i=0;$i<count($tabs);$i++)
		{
			$n_t=$tabs[$i];
			$tab=$this->Get_table($n_t);
			if($tab!=null)
			{
				$refs=$tab->GetRefs();

				for($j=0;$j<count($tabs);$j++)
				{
					for($k=0;$k<count($refs);$k++)
					{
						if($refs!=null)
						 {
							if($refs[$k]->GetTabName()==$tabs[$j])
							{
							$links=$refs[$k]->GetLinks();
							$ids_tab1=array_keys($links);
							$ids_tab2=array_values($links);
							if($s!=0)
								$req=$req." and ".$this->Merge_Ids($ids_tab1,$ids_tab2,$n_t,$tabs[$j]);
							else
								$req=$req.$this->Merge_Ids($ids_tab1,$ids_tab2,$n_t,$tabs[$j]);
							$s++;
							}
						}
						else
							{
							throw new Exception (' Impossible to found the references of the table ' . $n_t);
							$req="";
							}
					}
				}


			}
			else
			{
			throw new Exception (' Impossible to found the object associated to the table ' . $n_t.' please verify the syntax ');
			$req="";
			}
			}
		return $req;
	}




  }
?>
