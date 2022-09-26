<ol>
  {MESSAGES}
</ol>

<FORM  METHOD='POST' ACTION='Admin.php?action=4' NAME='Form'>

<TABLE  BORDER='1' >

<TR class='header'>
<TH COLSPAN=2>Configuration form</TH></TR>

<TR class='odd'><TD><B>Your login/password</B></TD>
<TD>{CONF_ID_ADMIN}</TD></TR>

<TR class='even'><TD><B>Conference acronym</B></TD>
<TD><INPUT TYPE='TEXT' NAME="confAcronym" VALUE="{CONF_ACRONYM}" SIZE='20' 
        MAXLENGTH='20'>
</TD>
</TR>

<TR class='odd'><TD><B>Conference name</B></TD>
<TD><INPUT TYPE='TEXT' NAME="confName" VALUE="{CONF_NAME}" SIZE='50' MAXLENGTH='100'>
</TD>
</TR>

<TR class='even'><TD><B>URL of the submission site</B></TD>
<TD><INPUT TYPE='TEXT' NAME="confURL" VALUE="{CONF_URL}" SIZE='50' MAXLENGTH='100'>
</TD>

</TR>
<TR class='odd'><TD><B>Conference mail</B></TD>
<TD><INPUT TYPE='TEXT' NAME="confMail" VALUE="{CONF_MAIL}" SIZE='60' MAXLENGTH='60'>
</TD>
</TR>
<TR class='even'><TD><B>Chair mail</B></TD>
<TD><INPUT TYPE='TEXT' NAME="chairMail" VALUE="{CONF_CHAIR_MAIL}" 
     SIZE='60' MAXLENGTH='60'>
</TD>
</TR>
<TR class='odd'><TD><B>Password generator</B></TD>
<TD><INPUT TYPE='TEXT' NAME="passwordGenerator" 
    VALUE="{CONF_PASSWORD_GENERATOR}" SIZE='10' MAXLENGTH='10'>
</TD>

</TR>

<TR class='even'><TD><B>Uploaded papers directory</B></TD>
<TD>
<INPUT TYPE='TEXT' NAME="uploadDir" VALUE="{CONF_UPLOAD_DIR}"
               SIZE='30' MAXLENGTH='30'>
</TD>
</TR>

<TR class='even'><TD><B>Submission options</B></TD>
<TD>
<TABLE BORDER=1>
<TR class='header'>
  <TH>Files format</TD>
  <TH>Extended submission form?</TD>
  <TH>Two phases submission?</TD>
  <TH>Multi-topics?</TD>
</TR>
<TR>
  <TD>{LIST_FILE_TYPES}</TD>
<TD>{LIST_EXTENDED_SUBMISSION_FORM}</TD>
  <TD>{LIST_TWO_PHASES_SUBMISSION}</TD>
  <TD>{LIST_MULTI_TOPICS}</TD>
</TR>
</TABLE>

</TD>
</TR>

<TR class='odd'><TD><B>Is submission open?</B></TD>

<TD><TABLE WIDTH="100%" BORDER=1 CELLSPACING=5 CELLPADDING=2>
<TR class='header'>
<TH>For abstracts?</TH>
<TH>For papers?</TH>
<TH>For camera-ready files?</TH>
</TR>
<TR>
<TD align=center>{LIST_ABSTRACT_SUBMISSION_OPEN}</TD>
<TD align=center>{LIST_PAPER_SUBMISSION_OPEN}</TD>
<TD align=center>{LIST_CAMERA_READY_SUBMISSION_OPEN}</TD>
</TR>
</TABLE>
</TR>

<TR class='even'><TD><B>Blind review?</B></TD>
<TD>{LIST_BLIND_REVIEW}</TD>
</TR>


<TR class='odd'><TD><B>Discussion mode</B></TD>
<TD>{LIST_DISCUSSION_MODE}</TD>
</TR>

<TR class='even'><TD><B>Ballot mode</B></TD>
<TD>{LIST_BALLOT_MODE}</TD>
</TR>

<TR class='even'><TD><B>Nb reviewers per paper</B></TD>
<TD><INPUT TYPE='TEXT' NAME="nbReviewersPerItem" 
	VALUE="{CONF_NB_REV_PER_PAPER}" SIZE='2' MAXLENGTH='2'>

</TD>
</TR>

<TR class='odd'><TD><B>Mail sending</B></TD>
<TD>
<TABLE WIDTH="100%" BORDER=1 CELLPADDING=2>
<TR class='header'>
 <TH>On abstract?</TH>
 <TH>On upload?</TH>
 <TH>On review submission?</TH>
</TR>
<TR>
 <TH>{SEND_ON_ABSTRACT}</TH>
 <TH>{SEND_ON_UPLOAD}</TH>
 <TH>{SEND_ON_REVIEW}</TH>
</TR>

</TABLE>
</TD>
</TR>

<TR class='even'>
<TD><B>Deadlines</B></TD>

<TD>
<TABLE WIDTH="100%" BORDER=1>
<TR class='header'>
  <TH>Paper submission</TH>
  <TH>Review submission</TH>
  <TH>Camera-ready submission</TH>
</TR>
<TR>
<TD nowrap>{CONF_SUBMISSION_DEADLINE}</TD>
<TD nowrap>{CONF_REVIEW_DEADLINE}</TD>
<TD nowrap>{CONF_CAMERA_READY_DEADLINE}</TD>
</TR>
</TABLE>

</TD>
</TR>

<TR class='even'><TD><B>Date presentation</B></TD>
<TD><INPUT TYPE='TEXT' NAME="date_format" VALUE="{CONF_DATE_FORMAT}" 
     SIZE='10' MAXLENGTH='20'> (Y=year; m=month; d=days; F=month's name; D=day's name)
</TD>
</TR>

<TR class='odd'><TD><B>Currency (registration)</B></TD>
<TD><INPUT TYPE='TEXT' NAME="currency" VALUE="{CONF_CURRENCY}" 
     SIZE='10' MAXLENGTH='20'>
</TD>
</TR>
<TR class='even'><TD><B>Paypal business account (registration)</B></TD>
<TD><INPUT TYPE='TEXT' NAME="paypal_account" VALUE="{CONF_PAYPAL_ACCOUNT}" 
     SIZE='40' MAXLENGTH='90'>
</TD>
</TR>
</TABLE>
<B> </B><INPUT TYPE='SUBMIT' NAME="submit" VALUE="Submit" SIZE='0' MAXLENGTH='0'>
</FORM></CENTER>

<a href='Admin.php'>Back to the admin menu</a></p>
