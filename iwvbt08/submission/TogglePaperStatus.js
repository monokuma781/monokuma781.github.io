/* Toggle he status of selected papers */

function TogglePaperStatus (status)
{
  form = document.forms.PaperList;

  /* Scan all the radio buttons of the form */
  for (var j=0; j < form.elements.length; j++)
  {
   el = form.elements[j];  
   if (el.type=='radio')
   {
    // alert ("Value = " + el.value + " Status = " + status);
    
    if (el.value == status) el.checked = true;
   }
 }
}
