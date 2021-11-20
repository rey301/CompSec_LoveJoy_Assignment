<?php
  // Database connection information
  $mysql_host="krier.uscs.susx.ac.uk";
  $mysql_database="G6077_ar629"; // name of the database, it is empty for now
  $mysql_user="ar629"; // type your username
  $mysql_password="Mysql_492467"; // type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.

  // Connect to the server
  $conn = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");

  // Copy all of the data from the form into variables
  $email1 = $_POST['txtEmail1'];
  $email2 = $_POST['txtEmail2'];
  $password1 = $_POST['txtPassword1'];
  $password2 = $_POST['txtPassword2'];
  $name = $_POST['txtName'];
  $phoneNumber = $_POST['txtPhoneNumber'];

  // Create a variable to indicate if an error has occurred or not, 0=false and 1=true. 
  $errorOccurred = 0;

  // Make sure that all text boxes were not blank.

  if ($email1=="" OR $email2=="")
  {
    echo "Email was blank! <br/>";
    $errorOccurred = 1;
  }

  if ($password1=="" OR $password2="")
  {
    echo "Password was blank! <br/>";
    $errorOccurred = 1;
  }

  if ($username=="")
  {
    echo "Name was blank! <br/>";
    $errorOccurred = 1;
  }

  if ($phoneNumber=="")
  {
    echo "Telephone Contact Number was blank! <br/>";
    $errorOccurred = 1;
  }

  // Query the database to retrieve the table SystemUser
  $userResult = $conn -> query("SELECT * FROM SystemUser");

  // Check if username already exists in the database
  // Loop from the first to the last record
  while ($userRow = mysqli_fetch_array($userResult))
  {
    // Check to see if the current user's name matches the one in the database
    if ($userRow['Name'] == $name)
    {
      echo "The username has already been used. <br/>";
      $errorOccurred = 1;
    }
  }

  // Check if email already exists in the database
  // Loop from the first to the last record
  while ($userRow = mysqli_fetch_array($userResult))
  {
    // Check to see if the current user's email matches the one in the database 
    if ($userRow['Email'] == $email1)
    {
      echo "This email address has already been used! <br/>";
      $errorOccurred = 1;
    }
  }

  // Check to make sure that the email address contains @
  if (strpos ($email1, "@") == false OR strpos($email2,"@") == false)
  {
    echo "The second email address is not valid! <br/>";
  }

  // Check to make sure that emails match
  if($email1 != $email2)
  {
    echo "Emails do not match! <br/>";
  }

  // Check to make sure that passwords match
  if ($password1 != $password2)
  {
    echo "Passwords do not match! <br/>";
    $errorOccurred = 1; 
  }

  // Check to see if an error has occurred, if so add contents to the database
  if ($errorOccurred == 0)
  {
    // Add all of the contents of the variables to the SystemUser table
    $sql = "INSERT INTO SystemUser (Email, Password, Name, PhoneNumber)
    VALUES ('$email1', '$password1', '$name', '$phoneNumber')";
    if ($conn -> query ($sql) === TRUE)
      {
        // Thank the new user for joining
        echo "Hello &ensp" .$forename ."-".$surname ."<br/>";
        echo "Thank you for joining the Computing Security network";
      } 
  }
?>
