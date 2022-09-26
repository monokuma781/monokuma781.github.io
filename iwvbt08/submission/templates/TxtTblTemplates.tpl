<!-- Template for the list of accepted papers -->
<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<h3>Templates</h3>

<table border=3>
<tr class='header'>
  <th>Name</th>
  <th>Comments</th>
  <th colSpan=2>Actions</th>
</tr>

<!-- BEGIN TPL_DETAIL -->
<tr class='{CSS_CLASS}'>
<td>{TPL_NAME}</td>
<td>{TPL_COMMENTS}</td>
<td> <a href="#"
onClick="ShowWindow('ShowTemplate.php?TPLFILE={TPL_FILE}');">View</A>
</td>
<td>{TPL_MODIF}</td>
</tr>

<!-- END TPL_DETAIL -->

</table>

<a href='Admin.php'>Back to the admin menu</a>