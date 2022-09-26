<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="styles.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h2>

<table border=2>
<tr class='header'>
  <th colspan=2>Information on paper {PAPER_ID}</th>
</tr>
<tr class='odd'> <td><b>Title</b>:<td> {PAPER_TITLE}
<tr class='even'> <td><b>Authors:</b><td> {PAPER_AUTHORS}
<tr class='odd'> <td><b>Abstract:</b><td> {PAPER_ABSTRACT}
 <tr class='even'> <td><b>Main topic:</b><td> {PAPER_TOPIC}</tr>
 <tr class='even'> <td><b>Other topics:</b><td> {PAPER_OTHER_TOPICS}</tr>

 <!-- BEGIN BLOCK_QUESTIONS -->
<tr class='odd'>
  <td><b>{QUESTION}</b><td> {ANSWER}
</tr>
<!-- END BLOCK_QUESTIONS -->

 <!-- BEGIN BLOCK_REVIEWERS -->
<tr class='even'>
 <td><b>Reviewers:</b><td> {LIST_REVIEWERS}
</tr>
<!-- END BLOCK_REVIEWERS -->

 <!-- BEGIN BLOCK_REVIEWS -->
<tr class='odd'>  <td><b>Reviews:</b><td> {REVIEWS}</tr>
<!-- END BLOCK_REVIEWS -->
</table>

<p>

{FORUM}

<div valign="bottom">
<hr>
<center><input type='button' onClick="window.close()" value="Close"></center>
</div>
</body>