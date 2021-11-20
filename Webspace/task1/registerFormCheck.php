<?php
 $mysql_host="krier.uscs.susx.ac.uk";
 
 $mysql_database="G6077_ar629";    // name of the database, it is empty for now
 $mysql_user="ar629";    // type your username
 $mysql_password="Mysql_492467";  //  type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.
 // 

 // connect to the server
 $connection = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");

 // select the database
// $db = mysqli_select_db ($mysql_database, $connection) or die ("Could not select a valid database");

 // Copy all of the data from the form into variables
 $forename = $_POST['txtForename'];
 $surname = $_POST['txtSurname'];
 $username = $_POST['txtUsername'];
 $email1 = $_POST['txtEmail1'];
 $email2 = $_POST['txtEmail2'];
 $password1 = $_POST['txtPassword1'];
 $password2 = $_POST['txtPassword2'];

 // Create a variable to indicate if an error has occurred or not, 0=false and 1=true. 
 $errorOccurred = 0;

 // Make sure that all text boxes were not blank.
 if ($forename == "")
 {
   echo "Forname was blank !<br/>";
   $errorOccurred = 1;
 }

 if ($surname == "")
 {
  echo "Surname was blank <br/>";
  $errorOccurred = 1;
 }

 if ($username=="")
 {
  echo "username was blank !<br/>";
  $errorOccurred = 1;
 }

 if ($email1=="" OR $email2=="")
 {
   echo "Email not provided <br/>";
   $errorOccurred = 1;
 }

 if ($password1=="" OR $password2="")
 {
   echo "Password empty, check it. <br/>";
   $errorOccurred = 1;
 }

 // Check if username already exists in the database. 
 $userResult = $connection -> query("SELECT * FROM SystemUser");
 
 //Loop through from the first to the last record
 while ($userRow = mysqli_fetch_array($userResult))
 {
	 // CHeck to see if the curren user' username matchs the one from the user
	 if ($userRow['Username'] == $username)
	 {
	  echo "The username has already been used ! <br/>";
	  $errorOccurred = 1;
	 }
 }

 // Check to see if the email address is registered.
 $userResult = $connection -> query("SELECT * FROM SystemUser");

 // Loop from the first to the last record
 while ($userRow = mysqli_fetch_array($userResult))
 {
    // CHeck to see if the Email entered matches with any value in the database. 
    if ($userRow['Email'] == $email1)
    {
      echo "This email address has already been used. <br/>";
      $errorOccurred = 1;
    }
 }

 // Check to make sure that email address contain @
 if (strpos ($email1, "@") == false OR strpos($email2,"@") == false)
 {
  echo "The second email address are not valid <br/>";
 }

 // Check to make sure that emails match
 if($email1 != $email2)
  {
   echo "Emails do not match <br/>";
 }

 // Check to make sure that password values match
/* if ($password1 != $password2)
 {
   echo "The passwords are different <br/>";
   $errorOccurred = 1; 
 }
 */
   // Check to see if an error has occurred. Then add the details to the database. 
 if ($errorOccurred == 0)
 {
   // add all of the contents of the variables to the SystemUser table
    	
    $sql = "INSERT INTO SystemUser (Username, Password, Forename, Surname, Email)
	  VALUES ('$username', '$password1', '$forename', '$surname', '$email1')";
     if ($connection -> query ($sql) === TRUE)
      {
         // Thank the new user for joining.
	 echo "Hello &ensp" .$forename ."-".$surname ."<br/>";
	 echo "Thank you for joining the Computing Security network";
      } 
 }
?>
