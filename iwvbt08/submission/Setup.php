<?php 
/********************************************** 
   The MyReview system for web-based conference management 
  
   Copyright (C) 2003-2006 Philippe Rigaux
   This program is free software; you can redistribute it and/or modify 
   it under the terms of the GNU General Public License as published by 
   the Free Software Foundation; 
  
   This program is distributed in the hope that it will be useful, 
   but WITHOUT ANY WARRANTY; without even the implied warranty of 
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
   GNU General Public License for more details. 
  
   You should have received a copy of the GNU General Public License 
   along with this program; if not, write to the Free Software 
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
************************************************/ 
 
error_reporting(E_ALL); 
 
require_once ("functions.php"); 
require_once ("template.inc"); 
require_once ("Constant.php"); 
require_once ("Formulaire.class.php"); 
require_once ("BD.class.php"); 
require_once ("SetupTexts.php"); 
require_once ("InstallFunction.php"); 
 
 
if (isSet($_POST['post'])) 
  $post = $_POST['post']; 
else 
  $post =""; 
 
# if setup form has been submitted 
# -------------------------------- 
if (!empty($post))  
{ 
   
  $server = $_POST['server']; 
  $databaseName = $_POST['databaseName'];  
  $login = $_POST['login'];  
  $password = $_POST['password'];  
   
  $email = $_POST['email']; 
  $firstName = $_POST['firstName']; 
  $lastName = $_POST['lastName']; 
  $affiliation = $_POST['affiliation']; 
 
  $passwordGenerator = $_POST['passwordGenerator']; 
  $uploadDir = $_POST['uploadDir']; 
 
  $messagesErreur=array(); 
 
  if (IsAlreadyInstalled()){ 
    $msg=$messages['ALREADY_INSTALL']; 
    Affiche($msg); 
    exit;    
  }  
   
#	check all required variables 
#	---------------------------- 
  if (empty($server)) array_push($messagesErreur,$messages['ERROR_MISSING_SERVER']);        
  if (empty($databaseName)) array_push($messagesErreur,$messages['ERROR_MISSING_DBNAME']);        
  if (empty($login)) array_push($messagesErreur,$messages['ERROR_MISSING_LOGIN']);       
  if (empty($password)) array_push($messagesErreur,$messages['ERROR_MISSING_PASSWORD']);        
  if (empty($email)) array_push($messagesErreur,$messages['ERROR_MISSING_EMAIL']);       
  if (empty($firstName)) array_push($messagesErreur,$messages['ERROR_MISSING_FIRSTNAME']);        
  if (empty($lastName)) array_push($messagesErreur,$messages['ERROR_MISSING_LASTNAME']);        
  if (empty($affiliation)) array_push($messagesErreur,$messages['ERROR_MISSING_AFFILIATION']);        
  if (empty($passwordGenerator)) array_push($messagesErreur,$messages['ERROR_MISSING_PWDGEN']);        
  if (empty($uploadDir)) array_push($messagesErreur,$messages['ERROR_MISSING_UPDIR']); 
 
# check files permissions 
# ----------------------- 
  if (!is_file("DBInfo.php"))  
    array_push($messagesErreur,$messages['DBINFO_EXIST']);   
  if (!is_readable("DBInfo.php"))  
    array_push($messagesErreur,$messages['DBINFO_READ']);   
  if (!is_writable("DBInfo.php"))  
    array_push($messagesErreur,$messages['DBINFO_WRITE']); 
  if (!is_dir($uploadDir))  
    array_push($messagesErreur,$messages['DIR']." <b>$uploadDir</b> " 
	       .$messages['UPLOAD_DIR_EXIST']);   
  if (!is_readable($uploadDir))  
    array_push($messagesErreur,$messages['DIR']." <b>$uploadDir</b> " 
	       .$messages['UPLOAD_DIR_READ']); 
  if (!@file_exists("$uploadDir/."))  
    array_push($messagesErreur,$messages['DIR']." <b>$uploadDir</b> " 
	       .$messages['UPLOAD_DIR_EXE']); 
  if (!is_writable($uploadDir))  
    array_push($messagesErreur,$messages['DIR']." <b>$uploadDir</b> " 
	       .$messages['UPLOAD_DIR_WRITE']); 
  if (!is_dir("tmpPdf"))  
    array_push($messagesErreur,$messages['DIR']." <b>tmpPdf</b> " 
	       .$messages['UPLOAD_DIR_EXIST']);   
  if (!is_readable("tmpPdf"))  
    array_push($messagesErreur,$messages['DIR']." <b>tmpPdf</b> " 
	       .$messages['UPLOAD_DIR_READ']); 
  if (!@file_exists("tmpPdf/."))  
    array_push($messagesErreur,$messages['DIR']." <b>tmpPdf</b> " 
	       .$messages['UPLOAD_DIR_EXE']); 
  if (!is_writable("tmpPdf"))  
    array_push($messagesErreur,$messages['DIR']." <b>tmpPdf</b> " 
	       .$messages['UPLOAD_DIR_WRITE']); 
  if (!is_writable("templates"))  
    array_push($messagesErreur,$messages['DIR']." <b>templates</b> " 
	       .$messages['UPLOAD_DIR_WRITE']); 
   
# check email adress 
# ------------------ 
if (!empty($email)) { 
    if (!CheckEMail($email)) array_push($messagesErreur,"<b>$email</b> ".$messages['INVALID_EMAIL']); 
  } 
 
# display errors 
# -------------- 
  if (count($messagesErreur)>0) Erreur($messagesErreur); 
 
 
  // Connect to the database 
  $connexion = @mysql_pconnect ($server, $login, $password); 
  if (!$connexion){ 
    $msg=$messages['CONNECT_ERROR']." ".$server." ". 
      $messages['CHECK_DB_FIELDS']; 
    array_push($messagesErreur,$msg); 
    Erreur($messagesErreur);       
  }   
  if (!mysql_select_db ($databaseName, $connexion)) { 
    $msg=$messages['DB']." <b>$databaseName</b> ".$messages['CHECK_DB_NAME']; 
    array_push($messagesErreur,$msg); 
    Erreur($messagesErreur);    
  }   
 
  // No error => Installation 
  CreateTables(); 
  WriteFileDBInfo($server,$databaseName,$login,$password); 
  CreateAdmin($email,$firstName,$lastName,$affiliation); 
  $URL="http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"]; 
  $URL= ereg_replace("/Setup.php", "/", $URL); 
  WriteConfig($passwordGenerator,$uploadDir,$URL); 
   
  // Compute password 
  $pwd= PWDMember ($email, $passwordGenerator); 
   
  $tpl = new Template ("."); 
  $tpl->set_file (array("MailSetup" => TPLDIR . "MailSetup.tpl", "TxtSetup" => TPLDIR . "TxtSetup.tpl"));   
  $tpl->set_var("NAME_ADMIN", $firstName." ".$lastName); 
  $tpl->set_var("EMAIL_ADMIN", $email); 
  $tpl->set_var("PASSWORD_ADMIN", $pwd); 
  $tpl->parse ("TEXT_BODY", "TxtSetup"); 
  $tpl->parse ("MAIL_BODY", "MailSetup"); 
   
  Affiche($tpl->get_var("TEXT_BODY")); 
   
  SendMail ($email,"MyReview installation completed", 
	    $tpl->get_var("MAIL_BODY"));   
} 
else { 
# if setup form has NOT been submitted 
# -------------------------------- 
   
  if (IsAlreadyInstalled()){ 
    $msg=$messages['ALREADY_INSTALL']; 
    Affiche($msg); 
    exit; 
  } 
 
  TestPHPConfig(); 
 
# Initialize defaults then display form 
# ------------------------------------- 
  $server="localhost"; 
  $databaseName="Review"; 
  $login="adminReview"; 
  $password="mdpAdmin"; 
  $email="myreview@lri.fr"; 
  $firstName="Philippe"; 
  $lastName="Rigaux"; 
  $affiliation="LRI"; 
  $passwordGenerator="pwd"; 
  $uploadDir="FILES"; 
  Affiche(Formulaire($server,$databaseName, 
           $login,$password,$email,$firstName, 
           $lastName,$affiliation, 
           $passwordGenerator,$uploadDir)); 
} 
 
 
 
function Affiche($page){ 
  $template = new Template ("."); 
  $template->set_file ("Page", TPLDIR . "Page.tpl"); 
  $template->set_var("TITLE", "Installation"); 
  $template->set_var("BODY",$page); 
  $template->set_var("CONF_NAME","MyReview automatic installation"); 
  $template->set_var("CONF_MAIL","myreview@lri.fr"); 
  $template->parse("RESULT","Page"); 
  echo $template->get_var("RESULT"); 
} 
 
 
function Erreur($msgs){ 
  global $server,$databaseName,$login,$password,$email,$firstName,$lastName,$affiliation,$passwordGenerator,$uploadDir; 
  $message = ""; 
  while (count($msgs)>0){ 
    $message="<b>Error :</b> ".array_pop($msgs)."<br>".$message; 
  } 
  Affiche($message.Formulaire($server,$databaseName,$login,$password,$email,$firstName,$lastName,$affiliation,$passwordGenerator,$uploadDir));  
  exit; 
} 
 
function WriteConfig($passwordGenerator,$directory, $URL){ 
  global $messages; 
  $error = ""; 
  $query="UPDATE Config SET passwordGenerator='$passwordGenerator', uploadDir='$directory', confURL='$URL', installInfo='Setup.php install';"; 
  $result = mysql_query($query) or $error=1;$erreur=mysql_error(); 
  if ($error) { 
    $msg=$messages['DB_PERMISSIONS']." ".$erreur; 
    $messagesErreur=array(); 
    array_push($messagesErreur,$msg); 
    Erreur($messagesErreur);       
  }   
} 
 
 
function CreateAdmin($email,$firstName,$lastName,$affiliation) { 
  global $messages; 
  $error=false; 
  $result = mysql_query("INSERT INTO PCMember (email, lastName, firstName, affiliation, roles) 
           VALUES ('$email', '$lastName', '$firstName', '$affiliation', 'A;C');") or  $error=true;$erreur=mysql_error(); 
  if ($error) { 
    $msg=$messages['DB_PERMISSIONS']." ".$erreur; 
    $messagesErreur=array(); 
    array_push($messagesErreur,$msg); 
    Erreur($messagesErreur);   
  }   
} 
 
 
function TestPHPConfig() { 
  global $messages; 
  $messagesErreur=array(); 
  set_magic_quotes_runtime(0); 
  if (!ini_get("file_uploads"))  
    array_push($messagesErreur,$messages['FILE_UPLOADS']);  
  if (count($messagesErreur)>0) Erreur($messagesErreur); 
} 
 
 
 
function WriteFileDBInfo($server,$databaseName,$login,$pwd){ 
  $file = fopen("DBInfo.php","w"); 
  fseek($file,0); 
   
  fputs($file, "<?php\n"); 
  fputs($file, "// File written by Setup.php\n"); 
  fputs($file,"// Constants that define how to connect the DB\n\n"); 
   
  fputs($file,"// The standard user: can do anything \n"); 
  fputs($file,"  define (\"NAME\",\"$login\");\n"); 
  fputs($file,"  define (\"PASS\", \"$pwd\");\n"); 
  fputs($file,"  define (\"SERVER\", \"$server\");\n"); 
  fputs($file,"  define (\"BASE\", \"$databaseName\");\n\n"); 
   
  fputs($file,"// The SQL user: can only submit SELECT queries\n"); 
  fputs($file,"  define (\"SQLUser\", \"SQLUser\");\n"); 
  fputs($file,"  define (\"pwdSQL\", \"pwdSQL\");\n"); 
  fputs($file,"?>\n"); 
   
  fclose($file);          
} 
 
 
function Formulaire($server,$databaseName,$adminLogin,$adminPwd,$email,$firstName,$lastName,$affiliation,$pwdGenerator,$dir){ 
  $form = new Formulaire ("POST", "Setup.php",1); 
  $form->ajoutTexte("<h2><b>DataBase</b></h2>"); 
  $form->debutTable(); 
  $form->champCache ("post", "yes"); 
  $form->champTexte ("Server name", "server",$server, 30, 30); 
  $form->champTexte ("Database name", "databaseName",$databaseName, 30, 30); 
  $form->champTexte ("Admin login", "login",$adminLogin, 12, 30); 
  $form->champMotDePasse ("Admin password", "password",$adminPwd, 12, 30); 
  $form->finTable(); 
  $form->ajoutTexte("<h2><b>Admin</b></h2>"); 
  $form->debutTable(); 
  $form->champTexte ("Admin email", "email",$email, 30, 30); 
  $form->champTexte ("Admin first name", "firstName",$firstName, 30, 40); 
  $form->champTexte ("Admin last name", "lastName",$lastName, 30, 40); 
  $form->champTexte ("Affiliation", "affiliation",$affiliation, 40, 40); 
  $form->finTable(); 
  $form->ajoutTexte("<h2><b>Configuration</b></h2>"); 
  $form->debutTable(); 
  $form->champTexte ("Password generator", "passwordGenerator",$pwdGenerator, 10, 10); 
  $form->champTexte ("Uploaded papers directory", "uploadDir",$dir, 30, 30); 
  $form->finTable(); 
  $form->debutTable(); 
  $form->champValider ("COMMIT", "submit"); 
  $form->finTable(); 
  return $form->fin(false); 
}  
 
 
// Run all the commands from the Schema.sql script 
function CreateTables() 
{ 
  global $messages; 
  $file = fopen("./Schema.sql","r"); 
  $error=false; 
  $query=""; 
  while (!feof ($file))  
    { 
      $buffer = fgets($file, 2048); 
      $bool=false; 
 
      if (!preg_match("/^#/", $buffer))	{ 
	  $query=$query." ".$buffer;      
	  $bool=true; 
      } 
 
      if ($bool and strstr ($query, ";")) 
	{    
	  $split = preg_split("/;/",$query); 
	  $query=$split[0].";"; 
	   
	  @mysql_query($query) or $error=true;  
	  $erreur=mysql_error(); 
	  if ($error) { 
	    echo "Erreur pour requete <pre>$query</pre>";
	    $msg=$messages['DB_PERMISSIONS']." ".$erreur; 
	    $messagesErreur=array(); 
	    array_push($messagesErreur,$msg); 
	    fclose ($file); 
	    Erreur($messagesErreur);   
	  }       
	  $query=""; 
	} 
    } 
  fclose($file); 
  @mysql_query("delete from PCMember;") or $error=true; $erreur=mysql_error();  
  if ($error)  
    { 
      $msg=$messages['DB_PERMISSIONS']." ".$erreur; 
      $messagesErreur=array(); 
      array_push($messagesErreur,$msg); 
      Erreur($messagesErreur);      
    } 
} 
?>
