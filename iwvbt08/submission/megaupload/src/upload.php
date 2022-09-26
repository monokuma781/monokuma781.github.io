<?

# PHP File Uploader with progress bar Version 1.12
# Copyright (C) Raditha Dissanyake 2003
# http://www.raditha.com

# Licence:
# The contents of this file are subject to the Mozilla Public
# License Version 1.1 (the "License"); you may not use this file
# except in compliance with the License. You may obtain a copy of
# the License at http://www.mozilla.org/MPL/
# 
# Software distributed under the License is distributed on an "AS
# IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or
# implied. See the License for the specific language governing
# rights and limitations under the License.
# 
# The Initial Developer of the Original Code is Raditha Dissanayake.
# Portions created by Raditha are Copyright (C) 2003
# Raditha Dissanayake. All Rights Reserved.
# 

# CHANGES:
# 1.00 cookies were abolished!
# 1.20 changed the form submit mechanism to filter for certain types
#      of files



	$sid = md5(uniqid(rand()));
	/*
	 * if your php installation cannot produce md5 hashes delete the above line and
	 * uncomment the line below.
	 *
	 * $sid = urlencode(uniqid(rand()));
	 */
	 
	//include("../inc/head.php");
//	create_header();
?>
<html>
<script language="javascript" type="text/javascript" src="script.js">
</SCRIPT>

<body>
<h2>File uploader with progress bar demo.</h2>
<p align="right"><a href="http://www.raditha.com/megaupload/">Project Home</A></p>

<form  enctype="multipart/form-data" action="/cgi-bin/upload.cgi?sid=<? echo $sid; ?>" method="post">


<table border="0" cellpadding="10" align="center">
<tr><td width="35%" valign="top"><p>
Use this form to upload some files and check out the functionality of the uploader.
The total file size should be less than 5MB or it will be rejected. 
</p>
<p>If you use a
file that is too small the progress bar may not have enough time to display itself,
specially if you are on a high speed connection.
</p>
<table class="newboxTable">
	<tr><td class="newboxTitle"  align="center">Note</td></tr>
	<tr><td>
		The speed of upload has been throttled for the health of the server.
	</td></tr>
	</table>
</td>
<td  valign="top">
	<table border=0 align="left" cellpadding=3>
	<tr><td><input type="file" name="file[0]"></td></tr>
	<tr><td><input type="file" name="file[1]"></td></tr>
	<tr><td><input type="file" name="file[2]"></td></tr>
	<tr><td><input type="file" name="file[3]"></td></tr>
	<tr><td><input type="file" name="file[4]"></td></tr>
	<tr><td><input type="file" name="file[5]"></td></tr>
	<tr><td><input type="file" name="file[6]"></td></tr>
	<tr><td><input type="file" name="file[7]"></td></tr>
	<tr><td><input type="file" name="file[8]"></td></tr>
	<tr><td><input type="file" name="file[9]"></td></tr>
	<tr><td colspan=2 align="center">
		<input type="hidden" name="sessionid" value="<?= $sid ?>">
		<input type="button" value="Send" onClick="postIt();">
		<!-- uncomment the following to test with out the progress bar -->
		<!input type="submit" value="Send">
	</td></tr></table>
</td>
</tr></table>

</form>
</body>
</html>


<?// create_footer(); ?>
