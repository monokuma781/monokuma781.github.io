<?
//******************************************************************************************************
//   Name: uber_upload_finished.php
//   Revision: 2.0
//   Date: 2005/12/18  
//   Author: Peter Schmandra  http://www.webdice.org
//   Description: Show successful file uploads.
//   Note: Pop ups may need to be enabled in the browser in order for this file to close the progress bar
//
//   Licence:
//   The contents of this file are subject to the Mozilla Public
//   License Version 1.1 (the "License"); you may not use this file
//   except in compliance with the License. You may obtain a copy of
//   the License at http://www.mozilla.org/MPL/
// 
//   Software distributed under the License is distributed on an "AS
//   IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
//   implied. See the License for the specific language governing
//   rights and limitations under the License.
//
//
//   NOTE: THIS FILE IS ONLY NECESSARY IF YOU ARE USING RE-DIRECT AFTER UPLOAD.
//
//***************************************************************************************************************


/////////////////////////////////////////////////////////
//  Get the post data from the param file (tmp_sid.param)
/////////////////////////////////////////////////////////
function getPostData($up_dir, $tmp_sid){
	$param_array = array();
	$paramFileName = $up_dir . $tmp_sid . ".params";
	$fh = fopen($paramFileName, 'r') or die("<br><center>Failed to open parameter file $paramFileName</center>.\n");
	
	while(!feof($fh)){
		$buffer = fgets($fh, 4096);
		parse_str(trim($buffer),$param_array);
	}

	fclose($fh);
	unlink($paramFileName);

	return $param_array;
}

