<!-- Template for the summary assignment -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

Here is a summary of the current assignments. If neccessary,
restrict the papers and/or the reviewers to the topic
selected with the following lists:

<form action="Admin.php?action=10" method="POST">
<input type='hidden' name='selectedTopic' value='1'>
Choose a paper topic: {LIST_PAPER_TOPICS} 
Choose a reviewer topic: {LIST_REVIEWER_TOPICS} 
<input type='submit' value='Go'>
</form>

<hr>
  <!-- BEGIN NAVIGATION_TABLE -->
    We do not display the entire ouput of the HTML table, 
    because of its size ({NB_PAPERS} papers *
        {NB_REVIEWERS} reviewers) .<br>
        The table has been therefore divided in sub-tables,
          limited to {MAX_ITEMS_IN_ASSIGNMENT} lines/columns<br>
         (you can change the value of the <tt>MAX_ITEMS_IN_ASSIGNMENT</tt>
            parameter in <it>Constant.php</i> if you wish).<br>
        Use the following navigation table to 
       switch from one sub-group to another. The <font color="lightblue">blue
        cell</font>
        indicates the currently displayed sub-group.<p>
          <table border=1 cellspacing=2 cellpadding=2>
            {NAV_TABLE}
         </table>
  <!-- END NAVIGATION_TABLE -->

<hr>

 You can add or remove a link 	    
(paper, reviewer) with the button of each cell.
Use the following button to validate your modifications.

<form action="Admin.php?action=10" method="POST">
<input type='hidden' name='changeAssignment' value='1'>
<input type='hidden' name='i_paper_min' value='{I_PAPER_MIN}'>
<input type='hidden' name='i_paper_max' value='{I_PAPER_MAX}'>
<input type='hidden' name='i_member_min' value='{I_MEMBER_MIN}'>
<input type='hidden' name='i_member_max' value='{I_MEMBER_MAX}'>

<input type='submit' value='Commit'>

<table border="2">
<tr class='header'><th>&nbsp;</th>
  <!-- BEGIN MEMBER_DETAIL -->
   <th>{MEMBER_NAME}<br>{MEMBER_NB_PAPERS} papers</th>
  <!-- END MEMBER_DETAIL -->
</tr>


<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
  <td><a name="{PAPER_ID}"></a>
    Paper {PAPER_ID}, {PAPER_NB_REVIEWERS} reviewers
	   <a href="#{PAPER_ID}"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=1&noForum=1');">
                 (infos)</A>
</td>

	<!-- BEGIN ASSIGNMENT_DETAIL -->
         <td bgcolor='{BG_COLOR}' NOWRAP>
           Y <input type='RADIO' 
			name='assignments[{PAPER_ID}][{MEMBER_EMAIL}]'
		   	 value=1 {CHECKED_YES}>
            N <input type='RADIO' 
			name='assignments[{PAPER_ID}][{MEMBER_EMAIL}]'
		   	 value=0 {CHECKED_NO}>
               <br>Rating={PAPER_RATING}
         </td>
	<!-- END ASSIGNMENT_DETAIL -->
</tr>
<!-- END PAPER_DETAIL -->

</table>
</form>

<br><a href='Admin.php'>Back to the admin menu</a>
