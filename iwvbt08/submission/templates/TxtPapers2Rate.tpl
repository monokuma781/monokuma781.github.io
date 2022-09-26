<!-- Template for the list of papers to rate -->


<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<form action="RatePapers.php" method="POST">
<input type=hidden name=iMin value={IMIN_CUR}>
<input type=hidden name=iMax value={IMAX_CUR}>

<!-- BEGIN RATING_MESSAGE -->
You can browse the list of papers and express your
preferences. Note that <i>No</i> means that 
you will never be assigned as reviewer to the paper.
Use it to express a conflict, if necessary.
<p>
At most {SIZE_RATING} paper are displayed simultaneously. 
You can switch from a group to another by using
the list of numbered links below. 
<p>
<b>Important</b>: you
must validate your preferences <i>before</i> accessing
another group, otherwise they will be lost.
<!-- END RATING_MESSAGE -->

<!-- BEGIN ACK_RATING_MESSAGE -->
Your preferences have been stored. Thanks You can modify
them at any moment, or access to another group.
<!-- END ACK_RATING_MESSAGE -->

<p>
<hr>
<b>Groups of papers</b>:
<!-- BEGIN GROUPS_LINKS -->
<a href="RatePapers.php?iMin={IMIN_VALUE}&iMax={IMAX_VALUE}">{LINK}</a>
<!-- END GROUPS_LINKS -->

<hr>
<p>
<INPUT TYPE="SUBMIT" VALUE="Validate your preferences">
<p>

<table border=1 cellspacing=2 cellpadding=2>
<tr class='header'>
  <th>Paper title</th><th>Authors</th><th>Your preference</th>
</tr>

<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
 <td><a name='{PAPER_ID}'>
     {PAPER_TITLE}
	   <a href="#{PAPER_ID}"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=1&noForum=1');">
                 (infos)</A>
  </td>
 <td>{PAPER_AUTHORS}</td>
 <td>{PAPER_RATE}</td>
</tr>
<!-- END PAPER_DETAIL -->

</table>

</form>
<p>

<a href="RatePapers.php?logout=1">Logout</a> 
