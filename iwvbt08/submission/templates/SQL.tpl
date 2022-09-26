<!-- JavaScript function to display schema infos  -->
<script language="JavaScript1.2" src="Popup.js"></script>

<P>

Enter your query in the form below, and execute.
Note that only SELECT queries are allowed.
You can pop-up
a window with  <a href="#"
	onClick="Popup('templates/Schema.html');">the DB schema</a>.
<P>
<center>
<form action='Admin.php?action=13' method="POST">

<textarea name='sqlQuery' cols=50 rows=10>{SQL_QUERY}</textarea>
<br>
<input type='submit' value='Execute'>

</form>
</center>

<!-- Display result, if any -->

<!-- BEGIN RESULT -->
<center>
<h2>Results</h2>
<table border=2 cellspacing=4 cellpadding=4>
  {LINES}
</table>
</center>
<!-- END RESULT -->

<br><a href='Admin.php'>Back to the admin menu</a>