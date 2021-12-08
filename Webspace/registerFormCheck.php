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

  // Retrieve the table SystemUser
  $userResult = $conn -> query("SELECT * FROM SystemUser");

  // Make sure that all text boxes were not blank.
  if ($email1=="" OR $email2=="") {
    echo "Email is blank! <br/>";
    $errorOccurred = 1;
  }
  else if ($email1 != strip_tags($email1)) {
	  echo "Email contains HTML tags ('<','>'). Please remove!</br>";
	  $errorOccurred = 1;
  }
  else if (strpos ($email1, "@") == false OR strpos($email2,"@") == false) {
    echo "Email is not valid! Must contain '@'. <br/>";
    $errorOccurred = 1;
  }
  // Check to make sure that emails match
  else if(strcmp($email1, $email2) != 0) { 
    echo "Emails do not match! <br/>";
    $errorOccurred = 1;
  }
  else {
    // Check if email already exists in the database
    // Loop from the first to the last record
    while ($userRow = mysqli_fetch_array($userResult)) {
      // Check to see if the current user's email matches the one in the database 
      if ($userRow['Email'] == $email1) {
      echo "This email address has already been used! <br/>";
      $errorOccurred = 1;
      }
    }
  }
  
  //Password policies
  //Policy 1: Password must not be null/empty
  if ($password1=="" OR $password2=="") {
    echo "Password is blank! <br/>";
    $errorOccurred = 1;
  }
  //Policy 2: Password must be between 8-16 characters
  else if (strlen($password1) < 8 OR strlen($password1) > 16) {
    echo "Password must be between 8-16 characters.<br/>";
  }
  //Policy 3: Password must contain both upper and lower case letters
  else if (!preg_match("/[A-Z]/", $password1) == 1) {
    echo "Password must contain upper case letters.</br>";
	  $errorOccurred = 1;
  }
  else if (!preg_match("/[a-z]/", $password1) == 1) {
    echo "Password must contain lower case letters.</br>";
	  $errorOccurred = 1;
  }
  //Policy 4: Password must contain at least one punctuation (excluding tags <>)
  else if (!preg_match("/[[:punct:]]/",$password1)) {
    echo "Password must contain at least one punctuation (excluding tags <>).</br>";
	  $errorOccurred = 1;
  }
  //Policy 5: Password must not contain any HTML tags to prevent XSS
  else if ($password1 != strip_tags($password1)) {
	  echo "Password contains HTML tags ('<','>'). Please remove!</br>";
	  $errorOccurred = 1;
  }
  //Policy 6: Password must contain at least one number
  else if (!preg_match("/[0-9]/",$password1)) {
    echo "Password must contain at least one number.</br>";
	  $errorOccurred = 1;
  }
  // Check to make sure that passwords match
  else if (strcmp($password1, $password2) != 0) {
    echo "Passwords do not match! <br/>";
    $errorOccurred = 1; 
  }

  //Checking if rest of the form is blank
  if ($name=="") {
    echo "Name is blank! <br/>";
    $errorOccurred = 1;
  }
  else {
    while ($userRow = mysqli_fetch_array($userResult)) {
      // Check to see if the current user's name matches the one in the database
      if ($userRow['Name'] == $name) {
        echo "Name has already been used. <br/>";
        $errorOccurred = 1;
      }
    }
  }

  if ($phoneNumber=="") {
    echo "Telephone Number is blank! <br/>";
    $errorOccurred = 1;
  }

  //HTML tag checking (making sure no tags are used within the text fields)
  if ($name != strip_tags($name)) {
	  echo "Name contains HTML tags! Please remove!</br>";
	  $errorOccurred = 1;
  }
  
  if ($phoneNumber != strip_tags($phoneNumber)) {
	  echo "Phone number contains HTML tags! Please remove!</br>";
	  $errorOccurred = 1;
  }

  // Check if name already exists in the database
  // Loop from the first to the last record
  while ($userRow = mysqli_fetch_array($userResult)) {
    // Check to see if the current user's name matches the one in the database
    if ($userRow['Name'] == $name) {
      echo "Name has already been used. <br/>";
      $errorOccurred = 1;
    }
  }

  // Check to see if an error has occurred, if so add contents to the database
  if ($errorOccurred == 0) {
    // Add all of the contents of the variables to the SystemUser table
    if (defined('PASSWORD_ARGON2I')) {
      $passwordHash = password_hash($password1, PASSWORD_ARGON2I);
    }
    else {
      $passwordHash = password_hash($password1, PASSWORD_DEFAULT);
    }
    
    $stmt = $conn->prepare("INSERT INTO SystemUser (Email, Password, Name, PhoneNumber) 
    VALUES (?, ?, ?, ?)");

    // Bind parameters to the query
    $stmt->bind_param("ssss", $email1, $passwordHash, $name, $phoneNumber);
    
    if ($stmt->execute()) {
      // Thank the new user for joining
      echo "Hello " . $name ."</br>";
      echo "Thank you for joining the Computing Security network";
    } 
    else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt -> close();
  }
  else if ($errorOccurred == 1) {
    echo "User could not be registered.";
  }
?>