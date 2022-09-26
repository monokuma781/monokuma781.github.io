<!-- Template for generic table access -->

<h3>{FEEDBACK}</h3>

{FORM_ROW}

<h3>List of existing rows</h3>

<table border='1'>
<tr class='header'>
{HEADERS}
<th colspan=2>Actions</th>
</tr>

<!-- BEGIN DETAIL_ROW -->
<tr class='{CSS_CLASS}'>
{ROWDATA}
<td>{MODIFY}</td><td>{DELETE}</td>
</tr>
<!-- END DETAIL_ROW -->

</table>

<p>

{ADD_ROW}
<br><br><a href='Admin.php'>Back to the admin menu</a>