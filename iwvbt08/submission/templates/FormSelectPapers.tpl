<FORM  METHOD='POST' ACTION='Admin.php?action={ACTION}' NAME='Form'>

<table border=1>
  <tr><td><B>Title contains:</B><td>
     <INPUT TYPE='TEXT' NAME="spTitle" VALUE="{PAPERS_WITH_TITLE}" 
          SIZE='20' MAXLENGTH='30'>
     <td><B>Authors contains:</B><td>
      <INPUT TYPE='TEXT' NAME="spAuthor" 
          VALUE="{PAPERS_WITH_AUTHOR}" SIZE='20' MAXLENGTH='30'>
     <td><B>Uploaded files?</B><td> {SP_UPLOADED}
  </tr>
<tr>
  <td><B>Status</B><td> {SP_STATUS}

  <td><B>Filter</B><td> {SP_FILTER}
       <INPUT TYPE='TEXT' NAME="spRate" VALUE="{SP_RATE}" 
               SIZE='4' MAXLENGTH='4'>
  <td><B>Reviewer</B><td> {SP_REVIEWERS}

<tr>
   <td><B>Topic</B><td> {SP_TOPICS}

   <td><B>Conflicting papers</B><td> {SP_CONFLICTS}

  <td><B>Missing reviews</B><td>{SP_MISSING}
</tr>
<!-- Paper questions -->
<!-- BEGIN ALL_QUESTIONS -->
<TR>
  <TD colspan=5><B>Submission questions</B></TD><TD><b>Answer</b></TD>
 </TR>
      <!-- BEGIN PAPER_QUESTION -->  
     <TR>
	<TD COLSPAN=5>{QUESTION}</TD><TD>{CHOICES}</TD>
      </TR>
    <!-- END PAPER_QUESTION -->  
    <TD colspan=5><B>Review questions</B></TD><TD><b>Answer</b></TD>
      <!-- BEGIN REVIEW_QUESTION -->  
     <TR>
	<TD COLSPAN=5>{QUESTION}</TD><TD>{CHOICES}</TD>
      </TR>
    <!-- END REVIEW_QUESTION -->  
<!-- END ALL_QUESTIONS -->

</table>

   <INPUT TYPE='SUBMIT' NAME="Submit" VALUE="Go" SIZE='0' MAXLENGTH='0'>
</FORM>
