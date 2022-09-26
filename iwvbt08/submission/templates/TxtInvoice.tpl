<!-- Template showing the invoice -->

Thanks for registering to {CONF_ACRONYM}.  Please
check that your data are correct.

<ul>
  <li><b>Your name</b>: {PERSON_FIRST_NAME} {PERSON_LAST_NAME}
  <li><b>Email</b>: {PERSON_EMAIL}
  <li><b>Affiliation</b>: {PERSON_AFFILIATION}
</ul>

<h3>Your registration choices</h3>

Here is the summary of your registration choice. <p>

<CENTER>
<TABLE border="2" cellpadding=4 cellspacing=4>
<TR class='header'>
  <TH>Option</TH>
  <TH>Choice</TH>
  <TH align='right'>Cost</TH>
</TR>
<!-- BEGIN ROW_CHOICE -->
<TR>
 <TD>{REG_QUESTION}</TD>
 <TD>{REG_CHOICE}</TD>
 <TD align='right'>{REG_COST}</TD>
</TR>
<!-- END ROW_CHOICE -->

<TR>
 <TD colspan=2><b>Total registration cost</b></TD>
 <TD align='right'>{TOTAL_COST}</TD>
</TR>

</TABLE>
<P>

<!-- BEGIN PAYPAL_PAYMENT -->

<!-- The paypal form -->
<FORM  TARGET="paypal"  METHOD='POST' 
       ACTION='https://www.paypal.com/cgi-bin/webscr'>

<INPUT type="HIDDEN" NAME="cmd" VALUE="_cart">
<INPUT type="HIDDEN" NAME="upload" VALUE="1">
<INPUT type="HIDDEN" NAME="no_shipping" VALUE="1">
<INPUT type="HIDDEN" NAME="business" VALUE="{PAYPAL_BUSINESS}">
<INPUT type="HIDDEN" NAME="charset" VALUE="utf-8">
<!--
  <INPUT type="HIDDEN" NAME="invoice" VALUE="{REGISTRATION_ID}">
-->
<INPUT type="HIDDEN" NAME="custom" VALUE="{REGISTRATION_ID}">
<INPUT type="HIDDEN" NAME="currency_code" VALUE="{PAYPAL_CURRENCY}">

<INPUT type="HIDDEN" NAME="cancel_return" 
 VALUE="{CONF_URL}/Register.php?registration={REGISTRATION_ID}&ihm_action=paypal_cancel">

<INPUT type="HIDDEN" NAME="return" 
 VALUE="{CONF_URL}/Register.php?registration={REGISTRATION_ID}&ihm_action=paypal_paid">
<INPUT type="HIDDEN" NAME="rm" VALUE="2">

<INPUT type="HIDDEN" NAME="email" VALUE="{PERSON_EMAIL}">
<INPUT type="HIDDEN" NAME="first_name" VALUE="{PERSON_FIRST_NAME}">
<INPUT type="HIDDEN" NAME="last_name" VALUE="{PERSON_LAST_NAME}">
<INPUT type="HIDDEN" NAME="address1" VALUE="{PERSON_ADDRESS}">
<INPUT type="HIDDEN" NAME="city" VALUE="{PERSON_CITY}">
<INPUT type="HIDDEN" NAME="country" VALUE="{PERSON_COUNTRY}">
<INPUT type="HIDDEN" NAME="zip" VALUE="{PERSON_ZIP_CODE}">

<!-- BEGIN PAYPAL_ITEM -->
<!--
  <INPUT type="HIDDEN" NAME="item_number_{ITEM_ID}" VALUE="{ITEM_ID}">
-->
<INPUT type="HIDDEN" NAME="item_name_{ITEM_ID}" VALUE="{ITEM_NAME}">
<INPUT type="HIDDEN" NAME="amount_{ITEM_ID}" VALUE="{ITEM_AMOUNT}">
<!-- END PAYPAL_ITEM -->

<INPUT TYPE='submit' VALUE='Make your payment with PayPal'>
<!-- END PAYPAL_PAYMENT -->

<!-- BEGIN OTHER_PAYMENT -->
You chose the following payment mode: {PERSON_PAYMENT_MODE}.
We shall send you your registration confirmation
when we get your payment.
<!-- END OTHER_PAYMENT -->
</FORM>

</CENTER>
