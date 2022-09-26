This is reserved to the PC chair(s).

<h4>Administrative tasks</h4>
<ol>
  <li><a href="Admin.php?action={CONFIGURE}">Configure the system</a></li> 
  <li><a href="Admin.php?action={QUERY}">SQL queries</a></li> 
  <li><a href="Admin.php?action={LIST_PC_MEMBERS}">Program committee</a></li> 
  <li><a href="Admin.php?action={TOPICS}">Research topics</a></li> 
  <li><a href="Admin.php?action={CRITERIAS}">Criterias</a></li> 
  <li><a href="Admin.php?action={CLOSE_SUBMISSION}">Close the submission phase</a></li> 
</ol>


<h4>Reviewer assignement</h4>
<ol>
 <li><a href="Admin.php?action={LIST_PAPERS}">List of submitted papers</a></li>
 <li><a href="Admin.php?action={CREATE_VOTE}">Take a new ballot</a></li> 
  <li><a href="Admin.php?action={COMPUTE_PREDICTION}">Compute 
                  new preferences</a></li> 
 <li><a href="Admin.php?action={COMPUTE_ASSIGNMENT}">Compute the 
             automatic assignment
               of papers</a></li>
 <li><a href="Admin.php?action={SUMMARY_ASSIGNMENT}">
          Manual assignment - check and modify</a></li>
</ol>

<h4>Selection phase</h4>
<ol>
  <li><a href="Admin.php?action={STATUS_OF_PAPERS}">Status of papers</a></li> 

  <li><a href="Admin.php?action={LIST_ACCEPTED_PAPERS}">
            List of accepted papers</a>
   </li> 
</ol>

<h4>Mails</h4>
<ol>
  <li>
    <form method="POST" action="SendMail.php?all_reviewers=1">
      Send <select name='idMessage'>
           <option value='{FREE_MAIL}'>Free mail
           <option value='{MAIL_SELECT_TOPICS}'>Select topics
           <option value='{MAIL_RATE_PAPERS}'>Rate papers
           <option value='{PWD_REVIEWERS}'>Review instr.
        </select>to each reviewer. <input type='submit' 
                value='Check template'>
    </form></li> 
  <li>
    <form action="SendMail.php?all_authors=1">
      Send  <select name='idMessage'>
           <option value='{STATUS_TO_AUTHORS}'>Paper status
        </select>to each author.  <input type='submit' value='Check template'>
    </form></li> 
</ol>
