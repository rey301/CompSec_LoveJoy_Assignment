<?php
  require '../sqlConn.php';

  $errorOccurred = 0;

  $email1 = $_POST['txtEmail1'];
  $email2 = $_POST['txtEmail2'];
  $password1 = $_POST['txtPassword1'];
  $password2 = $_POST['txtPassword2'];
  $userName = $_POST['txtUserName'];
  $phoneNumber = $_POST['txtPhoneNumber'];

  // Retrieve the table SystemUser
  $userResult = $conn -> query("SELECT * FROM SystemUser");

  // Email checking
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
    while ($userRow = mysqli_fetch_array($userResult)) {
      if ($userRow['UserEmail'] == $email1) {
      echo "This email address has already been used! <br/>";
      $errorOccurred = 1;
      }
    }
  }

  // Check if password passes our policies
  require '../passwordPolicies.php';

  // Checking if rest of the form is blank
  if ($userName=="") {
    echo "Username is blank! <br/>";
    $errorOccurred = 1;
  }
  else {
    // Check if username already exists in the database
    while ($userRow = mysqli_fetch_array($userResult)) {
      if ($userRow['UserName'] == $userName) {
        echo "Username has already been used. <br/>";
        $errorOccurred = 1;
      }
    }
  }

  if ($phoneNumber=="") {
    echo "Telephone Number is blank! <br/>";
    $errorOccurred = 1;
  }

  //HTML tag checking (making sure no tags are used within the text fields)
  if ($userName != strip_tags($userName)) {
    echo "Username contains HTML tags! Please remove!</br>";
    $errorOccurred = 1;
  }

  if ($phoneNumber != strip_tags($phoneNumber)) {
    echo "Phone number contains HTML tags! Please remove!</br>";
    $errorOccurred = 1;
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
    
    $stmt = $conn->prepare("INSERT INTO SystemUser (UserEmail, UserPassword, UserName, UserNumber) 
    VALUES (?, ?, ?, ?)");

    // Bind parameters to the query
    $stmt->bind_param("ssss", $email1, $passwordHash, $userName, $phoneNumber);
    
    if ($stmt->execute()) {
      // Thank the new user for joining
      echo "Hello " . $userName ."</br>";
      echo "Thank you for joining the Computing Security network";
    } 
    else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      $errorOccurred = 1;
    }

    $stmt -> close();
  }
  
  if ($errorOccurred == 1) {
    echo "User could not be registered.";
  }
?>