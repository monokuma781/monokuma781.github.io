<!-- Template for the list of papers to rate -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<p>
<hr>
<p>
<b>Groups of authors</b>:
<!-- BEGIN GROUPS_LINKS -->
<a href="Admin.php?action=19&iMin={IMIN_VALUE}&iMax={IMAX_VALUE}">{LINK}</a>
<!-- END GROUPS_LINKS -->
<p>
<hr>

<table border=1 cellspacing=2 cellpadding=2>
<tr class='header'>
  <th>Author's name</th><th>Affiliation</th><th>Submitted papers</th>
</tr>

<!-- BEGIN AUTHOR_DETAIL -->
<tr class='{CSS_CLASS}'>
 <td>{AUTHOR_FIRST_NAME} {AUTHOR_LAST_NAME}</td>
  <td>{AUTHOR_AFFILIATION}</td>
  <td><ol>

  <!-- BEGIN PAPER_DETAIL -->
  <li>{PAPER_TITLE}
	   <a href="#"
onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&noReview=0&noForum=1');">
                 (infos)</A></li>
    <!-- END PAPER_DETAIL -->
     </ol>
  </td>
</tr>
<!-- END AUTHOR_DETAIL -->

</table>
