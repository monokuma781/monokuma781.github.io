<!-- Template for the list of accepted papers -->
<!-- JavaScript function to display paper infos  -->

<h3>This is the list of papers with status "{STATUS_LABEL}".</h3>

<!-- BEGIN OPEN_FORM_ASSIGN -->
<FORM action="Admin.php?action={ACTION}" method="GET">
<!-- END OPEN_FORM_ASSIGN -->

<ol>
<!-- BEGIN PAPER_DETAIL -->
<li><b>{PAPER_TITLE}</b><br>
  <i>{PAPER_AUTHORS}</i>

  <!-- Present the list of sessions when an assignment 
             must be done -->
  <!-- BEGIN SELECT_ASSIGN -->
   <!-- END SELECT_ASSIGN -->

</li>
<!-- END PAPER_DETAIL -->
</ol>

<!-- BEGIN CLOSE_FORM_ASSIGN -->
</FORM>
<!-- END CLOSE_FORM_ASSIGN -->

<a href='Admin.php'>Back to the admin menu</a> | {LINK}