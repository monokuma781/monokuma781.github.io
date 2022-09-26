<!-- Template for the list of members -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<h4>{MESSAGE}</h4>

<p>

<center>
{FORM_MEMBER}
</center>

<h4>Program committee</h4>

Note: role A=Admin, C=Chair, R=Reviewer.

<center>
<table border=2>
<tr class='header'><th>Email</th><th>Name</th><th>Password</th>
  <th>Topics</th>
  <th>Roles</th>
   <th>Instructions</th>
   <th colspan=2>Actions</th></tr>

<!-- BEGIN MEMBER_DETAIL -->
<tr class='{CSS_CLASS}'>
<td>{MEMBER_EMAIL}</td>
<td>{MEMBER_NAME}</td>
<td>{MEMBER_PASSWORD}</td>
<td>{MEMBER_TOPICS}</td>
<td>{MEMBER_ROLES}</td>
<td><a 
 href="SendMail.php?idMessage={ID_MESSAGE}&to={PC_CODED_EMAIL}&target=Admin.php?action=1">
Send</a></td>
<td><a href="Admin.php?action=1&email={PC_EMAIL}&instr=modify">Modify</a></td>
<td><a href='#' onClick="ConfirmAction('This will remove {PC_EMAIL} and ALL her/his reviews', 
           'Admin.php?action=1&email={PC_EMAIL}&instr=remove')">Remove</a></td>
</tr>
<!-- END MEMBER_DETAIL -->

</table>
</center>
<p>

<a href='Admin.php'>Back to the admin menu</a>
