
The following input of the weighted matching algorithm
have been found.

<center>
<table border=2>
 <tr>  <td><b>Number of papers</b></td><td>{NUMBER_PAPERS}</tr>
 <tr> <td><b>Number of reviewers</b></td><td>{NUMBER_REVIEWERS}</tr>
 <tr> <td><b>Rev/papers</b></td><td>{REVIEWERS_PAPERS}</tr>
 </table>
</center>
<p>

It turns out that these values exceeds
the expected limit of the PHP implementation
({MAX_PAPERS_IN_ROUND} papers max.)). You can adopt
one of the following solutions:
<ol>
   <li>Raise the value of the <tt>MAX_PAPERS_IN_ROUND</tt>
        parameter in the file <i>Constant.php</i> to check whether
          your environment supports a larger setting.
   <li>Run the C implementation (see the documentation). 
   <li>Run the PHP code on subgroups of papers. We computed
          a partition, shown below. Each group
           in this partition contains less
         than {MAX_PAPERS_IN_ROUND} papers, so we expect
            that the PHP algorithm will execute.
</ol>  

<h4>Groups of papers</h4>

<table border="2">

<!-- BEGIN GROUP_DETAIL -->
<tr>
  <td>Group {GROUP_ID}</td>
  <td><a href="Admin.php?idMin={ID_MIN}&idMax={ID_MAX}&action=11">
              Compute the assignment from paper id({ID_MIN}) to id({ID_MAX})</a>
  </td>
</tr>
<!-- END GROUP_DETAIL -->

</table>
<br><br><a href='Admin.php'>Back to the admin menu</a>
