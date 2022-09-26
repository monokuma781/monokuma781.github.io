<!-- Template for the list of members -->

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

Here is the list of your papers. Use the 
<i>download</i> link to get the file,
and the <i>submit review</i> link to enter or
modify your review.
<p>
   <!-- BEGIN FORUM_LINK -->
<b>Important</b>: You are invited to participate to the global
discussion on all the submitted papers. Please
<a href="Forum.php">follow this link</a>
  <!-- END FORUM_LINK -->

<table border=1>
<tr class='header'>
  <th>Id</th>
  <th>Title</th>
   <th>Authors</th>
   <th align=center>Actions</th>
</tr> 
<!-- BEGIN PAPER_DETAIL -->
<tr class='{CSS_CLASS}'>
  <a name="{PAPER_ID}"></a>
  <td>{PAPER_ID}</td>
  <td>{PAPER_TITLE}</td>
  <td>{PAPER_AUTHORS}</td>
  <td nowrap>
    <a href="Download.php?idPaper={PAPER_ID}">Download</a>
    | <a href="Review.php?idPaper={PAPER_ID}">{TXT_SUBMIT_REVIEW}</a> 

   <!-- Beforediscussion phase, make it possible to consult MY review -->
   <!-- BEGIN MY_REVIEW -->
    |<a href="#"
      onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&allReviews=0');">See my review</A>   
   |<a href="#{PAPER_ID}"
      onClick="ShowWindow('PdfShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&allReviews=0');">
                 Print my review</A>   |
  <!-- END MY_REVIEW -->

   <!-- During discussion phase, make it possible to consult other reviews -->
   <!-- BEGIN ALL_REVIEWS -->
   <br> <a href="#{PAPER_ID}"
       onClick="ShowWindow('ShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&allReviews=1');">
                 See all reviews and discussion</A>   | <a href="#"
       onClick="ShowWindow('PdfShowInfos.php?idPaper={PAPER_ID}&idSession={SESSION_ID}&allReviews=1');">
                 Print all reviews and discussion</A>   |
    </td>

   <!-- During discussion phase, propose a forum  -->
   <tr class='{CSS_CLASS}'>
    <td colspan=3>Discussion thread
        (<a 
     href="Review.php?newMessage=1&idPaper={PAPER_ID}&emailReviewer={EMAIL_REVIEWER}">Add a message</a>)
    </td>
     <td>
     {MESSAGES}
     </td>

  <!-- END ALL_REVIEWS -->
</tr>
<!-- END PAPER_DETAIL -->
</table>

<a href="Review.php?logout=1">Logout</a> 