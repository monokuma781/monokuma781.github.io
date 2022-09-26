<!-- Template for the forum -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

Here is the list of papers. You should <i>not</i>
see the papers for which you declared a conflict.

<p>
<b>Pages:</b>:
<!-- BEGIN GROUPS_LINKS -->
<a href="Forum.php?iMin={IMIN_VALUE}&iMax={IMAX_VALUE}">{LINK}</a>
<!-- END GROUPS_LINKS -->

<p>

<table border="1">
<tr class='header'>
  <th>Paper info</th>
  <th>Actions</th>
</tr>

<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
<td rowspan='2'>
 <a name="{PAPER_ID}"></a>
 <b>ID: {PAPER_ID}</b> - <b>Title</b>: {PAPER_TITLE}<br> 
    {PAPER_AUTHORS}
</td>
<td>
    | <a href="Download.php?idPaper={PAPER_ID}">Download</a>  | 
  <a href="Forum.php?newMessage=1&idPaper={PAPER_ID}&emailReviewer={EMAIL_REVIEWER}">Add a message</a>

   | <a href="#{PAPER_ID}"
       onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&allReviews=1&in_forum=1');">
                 See the reviews</A>   
</td>
</tr>
<tr>
 <td>
   <b>Discussion</b>
     {MESSAGES}
 </td>
</tr>
<!-- END PAPER_DETAIL -->
</table>

