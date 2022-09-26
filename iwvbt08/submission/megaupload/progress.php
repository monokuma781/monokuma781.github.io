<?
/*
 * The progress bar in PHP was contributed by 
 * Mike Hodgson
 */
	// Edit these to wherever your temporary files are stored.

	$info_file = "/tmp/$sessionid"."_flength";
	$data_file = "/tmp/$sessionid"."_postdata";


	function hms($sec) {
		$thetime = str_pad(intval(intval($sec) / 3600),2,"0",STR_PAD_LEFT).":". str_pad(intval(($sec / 60) % 60),2,"0",STR_PAD_LEFT).":". str_pad(intval($sec % 60),2,"0",STR_PAD_LEFT) ;	
		return $thetime;
	}
	
	$started = TRUE;
	$total_size = $_GET['total_size'];
	$start_time = $_GET['start_time'];
	$time_now = time();
	$sessionid = $_GET['sessionid'];
	
	if ($total_size == 0) {
		if ($fp = @fopen($info_file,"r")) {
			$fd = fread($fp,1000);
			fclose($fp);
			$total_size = $fd;
		} else {
			$started = FALSE;
		}
	}
	if ($started == TRUE) {
		if ($start_time == 0) {
			$start_time = $time_now;
		}
		$time_elapsed = $time_now - $start_time;
		if ($time_elapsed == 0) {
			$time_elapsed = 1;
		}
		$current_size = @filesize($data_file);
		$percent_done = sprintf("%.0f",($current_size / $total_size) * 100);
		$speed = ($current_size / $time_elapsed);
		if ($speed == 0) {
			$speed = 1024;
		}
		$time_remain_str = hms(($total_size-$current_size) / $speed);
		$time_elapsed_str = hms($time_elapsed);
	}
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past 
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified 
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
	header("Pragma: no-cache"); // HTTP/1.0
?>
<html><head>
<title>File Upload Progress</title>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<?
	if ($percent_done < 100) {
?>
<meta http-equiv="refresh" content="5;<? echo $_SERVER['PHP_SELF']; ?>?total_size=<? echo $total_size; ?>&start_time=<? echo $start_time; ?>&sessionid=<? echo $sessionid; ?>">
<?
	}
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body bgcolor="#eeeeee">
  <table border=0 width="100%">
    <tr><TD align="center"  bgcolor="#ecf8ff">File Upload In Progress</td></tr>
	  <tr><td>
	<? if ($started) { ?>
      	 <table border=0 width='100%'>
		  <tr>
		   <td>
			 <table width="<? echo $percent_done; ?>%"  height="100%"  bgcolor='red'>
 			   <tr><td width='100%' style="height: 20px"> </td></tr>
		     </table>
		   </td>
          </tr>
        </table>
      <? echo $current_size; ?>/<? echo $total_size; ?> (<? echo $percent_done; ?>%) <? echo printf("%.2f",$speed); ?> kbit/s<br>
      Time Elapsed: <? echo $time_elapsed_str; ?><br>
	  Time Remaining: <? echo $time_remain_str; ?></td>
	  <? } else { ?>
	  Waiting for file upload to begin...
	  <? } ?>
  </tr>
</table>
</body>
</html>
