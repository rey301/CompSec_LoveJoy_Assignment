<?php
    session_start();

    require '../sqlConn.php';

    $errorOccurred = 0;
    echo "<pre>";
    if (isset($_POST['submit'])) {
      if (hash_equals($_SESSION['token'], $_POST['token'])) {
        $userID = $_SESSION['userID'];
        $password1 = $_POST['txtPassword1'];
        $password2 = $_POST['txtPassword2'];

        // Check if password passes our policies
        require '../passwordPolicies.php';
        if ($errorOccurred == 0) {
            // Add all of the contents of the variables to the SystemUser table
            if (defined('PASSWORD_ARGON2I')) {
              $passwordHash = password_hash($password1, PASSWORD_ARGON2I);
            }
            else {
              $passwordHash = password_hash($password1, PASSWORD_DEFAULT);
            }
            
            //Update user
            $stmt = $conn->prepare("UPDATE SystemUser SET UserPassword = ? WHERE UserID = ?");
            // Bind parameters to the query
            $stmt->bind_param("si", $passwordHash, $userID);
            
            if ($stmt->execute()) {
              // Acknowledge password has been updated
              echo "Password updated!<br>";
            } 
            else {
              echo "Error: " . $sql . "<br>" . $conn->error;
              $errorOccurred = 1;
            }
        
            $stmt -> close();
        }
      }
      else {
        echo "Failed to authenticate token<br>";
        $errorOccurred = 1;
      }
    }
    else {
      echo "Failed to authenticate user<br>";
      $errorOccurred = 1;
    }
    
    if ($errorOccurred == 1) {
        echo "Could not update password<br>";
    }

    require "../home.php";
    echo "</pre>";
?>