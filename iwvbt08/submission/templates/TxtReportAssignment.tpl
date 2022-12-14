<!-- Template for the report of automatic assigment  -->
<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>



The following assignment proposal has been computed. Please
check the result and validate
 if they suit you.
<br>
<b>IMPORTANT</b>: validating the result will remove all
the previous assignment,  automatic or manual. You can always
modify manually the assignment, though.

<form action="Admin.php?action=11" method="POST">
  <!-- BEGIN GROUP_DETAIL -->
      <b>This assignment has been computed for the group    
          of papers ranging from id {ID_MIN} to id {ID_MAX}</b><p>
   <input type="hidden" name='idMin' value='{ID_MIN}'>	
   <input type="hidden" name='idMax' value='{ID_MAX}'>	
  <!-- END GROUP_DETAIL -->
<input type='hidden' name='commitAssignment' value='1'>
<input type="submit" value="Validate">
</form>

<h4>Statistics on the automatic assignment results</h4>

The table shows the proposed assignment (colored cells).<br> The 
number inside each cell gives the PC member preference
on the corresponding paper.
<p>
The <i>W</i> value for each paper gives the "weight" of
an assignment, i.e., the sum of the ratings for the reviewers
of the paper. <br>Note that the maximal weight is 
{CONF_NB_REV_PER_PAPER} * 4 = {MAXIMAL_WEIGHT},
i.e., the number of reviewers per paper, multiplied
by the maximal rating (4).

<table border=2>
 <tr>  <td><b>Number of papers</b></td><td>{NUMBER_PAPERS}</tr>
 <tr> <td><b>Number of reviewers</b></td><td>{NUMBER_REVIEWERS}</tr>
 <tr> <td><b>Rev/papers</b></td><td>{REVIEWERS_PAPERS}</tr>
 <tr> <td><b>Max. papers per reviewer</b></td><td>{MAX_PAPERS_REVIEWER}</tr>
 </table>

<h4>Summary of results</h4>

<table border="2">
<tr><th></th>
  <!-- BEGIN MEMBER_DETAIL -->
   <th>{MEMBER_NAME}</th>
  <!-- END MEMBER_DETAIL -->
</tr>

<!-- BEGIN PAPER_DETAIL -->
<tr>
  <td>{PAPER_ID}
	   <a href="#"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=1&noForum=1');">
                 (infos)</A>
         W={WEIGHT}
</td>
	<!-- BEGIN ASSIGNMENT_DETAIL -->
         <td bgcolor='{BG_COLOR}' align='right'>({PAPER_RATING})</td>
	<!-- END ASSIGNMENT_DETAIL -->
</tr>
<!-- END PAPER_DETAIL -->

</table>
<br><br><a href='Admin.php'>Back to the admin menu</a>
