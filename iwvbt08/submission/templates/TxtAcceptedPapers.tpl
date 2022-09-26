<!-- Template for the list of accepted papers -->
<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>
<h3>This is the list of papers with status "{STATUS_LABEL}".</h3>


<!-- BEGIN OPEN_FORM_ASSIGN -->
<FORM action="Admin.php?action=21" method="POST">
<INPUT TYPE='SUBMIT' VALUE='Validate the conf. session assignment'>
<INPUT TYPE='HIDDEN' NAME='form_assign_session' VALUE='1'>
<!-- END OPEN_FORM_ASSIGN -->

<table border=3>
<tr class='header'>
  <th>Title, authors, password</th>
  <th>Info</th>
  <th>Conference session, position</th>
  <th colSpan=2>Actions</th>
</tr>

<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
<td><b>{PAPER_ID}</b> - {PAPER_TITLE}, {PAPER_AUTHORS},
         {PAPER_PASSWORD}
	   <a href="#"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=1&noForum=1');">
                 (infos)</A>
</td>

<td>
{CR_PAPER}
</td>

  <!-- BEGIN SELECT_ASSIGN -->
  <!-- Present the list of sessions when an assignment 
             must be done -->
    <td nowrap>{SESSION_LIST}
           <INPUT TYPE='TEXT' SIZE='3' MAXSIZE='3' 
		NAME='position_in_session[{PAPER_ID}]' 
	               VALUE='{PAPER_POSITION_IN_SESSION}'>	
    </td>
   <!-- END SELECT_ASSIGN -->

<td>{DOWNLOAD}</td>
<td><a href="mailto:{PAPER_EMAIL_CONTACT}">Mail authors</a></td>


</tr>

<!-- END PAPER_DETAIL -->

</table>

<!-- BEGIN CLOSE_FORM_ASSIGN -->
</FORM>
<!-- END CLOSE_FORM_ASSIGN -->


<a href='Admin.php'>Back to the admin menu</a> | {LINK}