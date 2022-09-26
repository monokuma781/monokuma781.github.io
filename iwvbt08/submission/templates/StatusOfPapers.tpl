<!-- JavaScript function to toggle the status of papers  -->
<script language="JavaScript1.2" src="TogglePaperStatus.js"></script>

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<!-- A form to select a subset of papers   -->

<!-- BEGIN SHOW_SELECTION_FORM -->
You can choose to <a href="Admin.php?action=3&show_selection_form=1">show
 the selection form</a>. It will allow you to select a subset of papers.
<!-- END SHOW_SELECTION_FORM -->

<!-- BEGIN SELECTION_FORM -->
<h3>Set the current selection</h2>

Use the following form to get a selection of submitted
papers.
<p>

{FORM_SELECT_PAPERS}

You can choose to <a href="Admin.php?action=3&hide_selection_form=1">hide the above
selection form</a>. You will be able to display it again at any moment.

<!-- END SELECTION_FORM -->

<h3>Actions on the current selection</h3>

<!-- A form to toggle the status of selected papers -->
<ol>
 <li>Mark all the selected papers as {TOGGLE_LIST}<br>
  <li><a href="#"
onClick="ShowWindow('Admin.php?action={PDF_SELECT_PAPERS_WITH_REVIEWS}');">Print all the selected papers</A>
</ol>

<h3>List of papers in the current selection</h3>

<!-- A button to commit changes  -->
<FORM NAME="PaperList" ACTION='Admin.php?action=3' METHOD=POST>
Commit status changes: <INPUT TYPE=SUBMIT VALUE='Commit'>

<!-- A table that shows the selected papers   -->

<a name="#"></a>

<table border="1" cellspacing="0" cellpadding="2">

<tr class='header'>
    <th colspan=2>Paper info.</th>
    <th>Reviewers</th>
    <th>AVG mark</th>
    <th>Expertise</th>
    <!-- BEGIN REVIEW_CRITERIA -->
         <th align="right">{CRITERIA}</th>
    <!-- END REVIEW_CRITERIA -->
</tr>

<!-- BEGIN PAPER_DETAIL -->

<tr class='{CSS_CLASS}'>
  <a name="{PAPER_ID}"

  <!-- The following is used to provides infos on a paper,
     with misc. actions                             -->

 <!-- BEGIN PAPER_INFO -->
  <td rowspan={NB_REVIEWERS}>{PAPER_RANK}<br> 
  <td rowspan={NB_REVIEWERS}><table border=1 cellpadding="0" cellspacing="0">
    <tr><td colspan=3>{PAPER_TITLE} (#{PAPER_ID})<br>
       {PAPER_AUTHORS}</td></tr>
      <tr>
       <td><a href="#{PAPER_ID}"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}');">
                 Show infos</A>
       </td>
      <td>
       <a href="SendMail.php?idMessage={ID_MESSAGE}&idPaper={PAPER_ID}&target={TARGET}">
          Mail reviewers</a>
       </td>
       <td> 
       <a   href="SendMail.php?idMessage={ID_MESSAGE_STATUS}&idPaper={PAPER_ID}&target={TARGET}">
         Notify authors</a>
       </td>
      </tr>
      <tr>
      <!-- Radio buttons to mark the paper as accepted or rejected -->
       <td colspan=3><center>{FORM_STATUS}</center></td>
   </tr>
   </table>
  </td>
 <!-- END PAPER_INFO -->

    <td>
     <!-- BEGIN REVIEWER -->
      <a href="mailto:{MEMBER_EMAIL}">{MEMBER_NAME}
</a>
    <a href='#{PAPER_ID}' 
 onClick="ConfirmAction('Remove assignment of reviewer {MEMBER_EMAIL}\n from paper {PAPER_ID}?', 
           'Admin.php?action=3&remove={MEMBER_EMAIL}&idPaper={PAPER_ID}')">
      (Remove)
    </a>

     <!-- END REVIEWER -->
    </td>

    <td align="right"><b><font color='#000099'>{REVIEWER_MARK}</font></b></td>
     <td>{EXPERTISE}</td>

    <!-- BEGIN REVIEW_MARK -->
       <td align="right">{MARK}</td>
    <!-- END REVIEW_MARK -->
  
</tr>

<!-- END PAPER_DETAIL -->

</table>

</form>

<a href='Admin.php'>Back to the admin menu</a>
