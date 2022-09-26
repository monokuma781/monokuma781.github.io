<!-- Template for the list of submitted papers -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>


<!-- BEGIN SHOW_SELECTION_FORM -->
You can choose to <a href="Admin.php?action=2&show_selection_form=1">show
 the selection form</a>. It will allow you to select a subset of papers.
<!-- END SHOW_SELECTION_FORM -->

<!-- BEGIN SELECTION_FORM -->
<h3>Set the current selection</h2>

Use the following form to get a selection of submitted
papers.
<p>

{FORM_SELECT_PAPERS}

You can choose to <a href="Admin.php?action=2&hide_selection_form=1">hide the above
selection form</a>. You will be able to display it again at any moment.

<!-- END SELECTION_FORM -->

<h3>Papers in the current selection <a href="#"
onClick="ShowWindow('Admin.php?action={PDF_SELECT_PAPERS_WITHOUT_REVIEWS}');">
             Print the selected papers  </A></h3>


<table border=3>
<tr class='header'>
  <th><font color=white>Title, authors, password</font></th>
  <th><font color=white>Reviewers</font></th>
  <th colSpan=4><font color=white>Actions</font></th>
</tr>

<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
<a name="{PAPER_ID}"></a>
<td><b>{PAPER_ID}</b> - {PAPER_TITLE}, {PAPER_AUTHORS},
         {PAPER_PASSWORD}
	   <a href="#{PAPER_ID}"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=1&noForum=1');">
                 (infos)</A>
</td>

<td>
  <!-- BEGIN REVIEWER -->
    {MEMBER_FIRST_NAME} {MEMBER_LAST_NAME} 
    <a href='#{PAPER_ID}' 
 onClick="ConfirmAction('Remove assignment of reviewer {MEMBER_EMAIL}\n from paper {PAPER_ID}?', 
           'Admin.php?action=2&remove={MEMBER_EMAIL}&idPaper={PAPER_ID}')">
      (Remove)
    </a>
    <br>
  <!-- END REVIEWER -->
</td>

<td>{DOWNLOAD}</td>
 <td><a href='#{PAPER_ID}' 
           onClick="ConfirmAction('Remove paper {PAPER_ID}\n and its reviews', 
           'Admin.php?action=2&idPaper={PAPER_ID}&instr=remove')">
      Remove
    </a>
  </td>
<td>

<a href="AssignReviewers.php?idPaper={PAPER_ID}">Assign</a></td>
<td><a href="mailto:{PAPER_EMAIL_CONTACT}">Mail authors</a></td>
</tr>

<!-- END PAPER_DETAIL -->

</table>

<a href='Admin.php'>Back to the admin menu</a>
