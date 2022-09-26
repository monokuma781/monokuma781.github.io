<!-- This is the template for the home page -->

<p>
Welcome to the <b>MyReview</b> web-based
conference management system! 
This is a demo version 
that will help you to evaluate (and, hopefully, appreciate)
the features of MyReview.

<h2>Customization</h2>

MyReview is a paper review system written with PHP and MySQL. It
proposes the traditional functionalities of such systems (see <a
href='http://myreview.lri.fr'>the main site</a> for a
comparison), namely paper submission, reviewer assignment, discussion
on conflicting reviews, selection of papers, mails sent to all actors,
etc.  One of its most salient features, though, is the ability to
change the look-and-feel of the HTML pages, as well as all the static,
textual information, independently from the PHP code.  To do so, it
relies on the <it>templates</it> mechanism of PHP which separates
(almost) completely the PHP code from the HTML/images/JavaScript/Flash
presentation.<p>

Just try it: choose a presentation,
which will be then applied to ALL the pages of the site.
Select a template with the form below, GO!, and see the result.
<center>
<form action='index.php' method='POST'>
 Basic template: <input type='radio' name='template' value='SimplePage.tpl'>
 Standard template: <input type='radio' name='template' value='standard.tpl'>
 <input type='submit' value='Go'>
</form>
</center>

Convinced? Using this feature allows you to integrate smoothly the
functionalities of MyReview in your own site: give your own template,
and that's all. You can take a look at the 
<a href='templates/SimplePage.tpl'>simple template</a> if you wish.
Note the <tt>TITLE</tt> and <tt>BODY</tt> special elements. MyReview
replaces these elements with dynamic content at run-time, depending
on the context, content o the DB, user actions, etc. Templates
quite be arbitrarily complex, as long as they contain the two 
elements at the appropriate location.

<h2>Try it!</h2>

 You can play with all the 
functionalities, taking alternatively the role
of the three main types of actors

<ol>
 <li><b>Authors</b>
   As an author, you can submit an abstract for a paper
    that you intend to submit, unsing the <a href="SubmitAbstract.php">
   abstract submission interface</a>. . The system will provide you with
   an <it>id</it> and a password.  You can  then access
    the <a href="SubmitPaper.php">paper submission interface</a>
      to upload the file of your complete paper. </li>

 <li><b>Reviewers</b>
   As a reviewer, you can access the <a href="Review.php">reviewing
   interface</a>, download your papers, submit and update  your reviews.
   You need a login and a password: use 
     <b>myreview@lri.fr</b> and the password
     <b>08b271</b> for this demo.</li>

 <li><b>Administrator</b>
   This is the most important role. Using the <a href="Admin.php">administrator
   interface</a>  You can configure the system,
   create new reviewers, enter research topics, evaluation criterias,
   papers' status codes
   for your conference, ask reviewers to select their preferred topics,
   assign manually or automatically
   papers to reviewers, consult the marks of papers,
   send mails to everybody, etc. The user
     <b>myreview@lri.fr</b> (with password
     <b>08b271</b>) is an administrator  for this demo site.</li>
</ol>

You can enter an abstract, then a paper, then, in the administrator's
interface, create one or several reviewers, assign the papers, enter reviews, 
look at the status of papers, etc. Please remember that ALL the
text can be changed without having to look at a single line of PHP code.


