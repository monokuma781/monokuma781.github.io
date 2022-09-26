<!-- JavaScript function to toggle the status of papers  -->
<script language="JavaScript1.2" src="TogglePaperStatus.js"></script>

<!-- JavaScript function to display paper infos  -->
<script language="JavaScript1.2" src="ShowWindow.js"></script>

<a href="Admin.php?logout=1">Logout</a>


<!-- BEGIN ADMIN_CHOICES -->

<h1>Administrator  menu</h1>

<TABLE BORDER =1>
<TR class='header'><TH colspan=3>Administrative tasks</TH></TR>
<TR class='even'>
  <TD><a href="Admin.php?action={CONFIGURE}">Configure the system</a>
  <TD><a href="Admin.php?action={LIST_PC_MEMBERS}">Program committee</a>
  <TD><a href="Admin.php?action={TOPICS}">Research topics</a></TD> 
</TR>
<TR class='odd'>
  <TD><a href="Admin.php?action={CRITERIAS}">Criteria</a></TD>
  <TD><a href="Admin.php?action={PAPER_QUESTIONS}">Paper questions</a></TD>
  <TD><a href="Admin.php?action={REVIEW_QUESTIONS}">Review questions</a></TD>
</TR>
<TR class='odd'>
  <TD><a href="Admin.php?action={PAPER_STATUS_CODES}">Status codes</a></TD> 
  <TD><a href="Admin.php?action={CLOSE_SUBMISSION}">Close the submission 
                      phase</a></TD> 
  <TD><a href="Admin.php?action={PDF_CONFIG_PARAMS}">PDF Style</a></TD>
</TR>
<TR class='even'>
  <TD><a href="EditTemplate.php">Templates edition</a></TD> 
  <TD colspan=2>{GRAPHS}</TD>	
</TR>
</TABLE>

<!-- END ADMIN_CHOICES -->

<!-- BEGIN CHAIR_CHOICES -->

<h1>PC chair menu</h1>

<TABLE BORDER =1>
<TR class='header'><TH colspan=3>Submission phase</TH></TR>
<TR class='even'>
  <TD><a href="Admin.php?action={QUERY}">SQL queries</a></TD> 
 <TD><a href="Admin.php?action={LIST_PAPERS}">List of submitted papers</a></TD>
 <TD><a href="Admin.php?action={LIST_AUTHORS}">List of authors</a></TD>
</TR>
<TR class='odd'>
 <TD><a href="Admin.php?action={CREATE_VOTE}">Compute preferences and 
         conflicts</a></TD> 
 <TD><a href="SendMail.php?all_reviewers=1&idMessage=6">Send a mail 
          asking for reviewers' preferences
           </a></TD> 

 <TD><a href="Admin.php?action={COMPUTE_ASSIGNMENT}">Compute the 
             automatic assignment
               of papers</a></TD>
</TR>
<TR class='even'>
 <TD colspan=3><a href="Admin.php?action={SUMMARY_ASSIGNMENT}">
          Manual assignment - check and modify</a></TD>
</TR>

<TR class='header'><TH colspan=3>Selection phase</TH></TR>

<TR class='even'>
  <TD colspan=3><a href="Admin.php?action={STATUS_OF_PAPERS}">Status of papers
    (see reviews and assign a status to papers)</a></TD> 
</TR>
<!-- BEGIN PAPER_CLASSIFICATION -->
<TR class='odd'>
 <TD colspan=3><a href="Admin.php?action={LIST_ACCEPTED_PAPERS}&status={PAPER_STATUS}">
      List of papers with status "{STATUS_LABEL}"</a></TD> 
</TR>
<!-- END PAPER_CLASSIFICATION -->
<TR class='even'>
  <TD colspan=3><a href="Admin.php?action={CLOSE_SELECTION}">Close the selection phase</a></TD> 
</TR>

<TR class='header'><TH colspan=3>Camera-ready phase</TH></TR>
<TR class='even'>
 <TD><a href="Admin.php?action={CONF_SLOTS}">Define the slots
  of the conference
 <TD><a href="Admin.php?action={CONF_SESSIONS}">Define the sessions
  of the conference
 <TD><a href="Admin.php?action={ASSIGN_CR_PAPERS}&simple=0">Assign accepted papers to sessions</a>
</TR>
<TR class='odd'>
 <TD><a href="Admin.php?action={CONF_PROGRAM}">Conference program</a>
 <TD><a href="#" onClick="ConfirmAction 
       ('This will replace the existing latex documents',
	'Admin.php?action={LATEX_OUTPUT}');">
       Produce the Latex documents (proceedings, booklets, etc.)</a>
  <TD>&nbsp;</TD>
</TR>

<TR class='header'><TH colspan=3>Registration</TH></TR>
<TR class='odd'>
 <TD><a href="Admin.php?action={PAYMENT_MODES}">Payment modes</a>
  <TD><a href="Admin.php?action={REGISTRATION_QUESTIONS}">Registration
         choices</a></TD>
  <TD><a href="Admin.php?action={REGISTRATION_LIST}">List of attendees</a></TD>
</TR>

<TR class='header'><TH colspan=3>Mails</TH></TR>
<TR class='even'>
  <TD colspan=3>
    <form method="POST" action="SendMail.php?all_reviewers=1">
      Send <select name='idMessage'>
           <option value='{FREE_MAIL}'>Free mail
           <option value='{MAIL_SELECT_TOPICS}'>Select topics
           <option value='{MAIL_RATE_PAPERS}'>Collect preferences
           <option value='{PWD_REVIEWERS}'>Review instructions
         <!-- BEGIN PARTICIPATE_FORUM -->
           <option value='{MAIL_PARTICIPATE_FORUM}'>Participate to the 
                      general discussion
         <!-- END PARTICIPATE_FORUM -->
        </select>to each reviewer. <input type='submit' 
                value='Check template'>
    </form></TD> 
</TR>
<TR class='odd'>
  <TD colspan=3>
    <form method="POST" action="SendMail.php?all_authors=1">
      Send  <select name='idMessage'>
           <option value='{FREE_MAIL}'>Free mail
           <option value='{STATUS_TO_AUTHORS}'>Paper status
        </select>to each author.  <input type='submit' value='Check template'>
    </form>
  </TD> 
</TR>
<TR class='odd'>
  <TD colspan=3>
    <a href="SendMail.php?idMessage={FREE_MAIL}&all_authors_accepted=1">
      Send a free mail to all the authors of accepted papers </a><br>
          Note: a
      paper is considered "accepted" if a camera-ready version is required for
        its status.
    </a>
  </TD> 
</TR>
</TABLE>
<!-- END CHAIR_CHOICES -->

<a href="Admin.php?logout=1">Logout</a>