<?php
/////////////////////////////////////////////////////////
//  Get the post data from the param file (tmp_sid.param)
/////////////////////////////////////////////////////////
function getPostData($up_dir, $tmp_sid){
	$param_array = array();
	$paramFileName = $up_dir . $tmp_sid . "_postdata";
	$fh = fopen($paramFileName, 'r') or die("<br><center>Failed to open parameter file $paramFileName</center>.\n");
	
	while(!feof($fh)){
		$buffer = fgets($fh, 4096);
		list($key, $value) = explode('=', trim($buffer));
		
		if(($key != "" || $key != null) && ($value != "" || $value != null)){ $param_array[$key] = $value; echo $param . ' ' . $key . ' ' . $value;}
	}

	fclose($fh);
	unlink($paramFileName);
	return $param_array;
}
?>