<?php
    session_start();

    require '../sqlConn.php';

    $errorOccurred = 0;
    
    $userID = $_SESSION['userID'];
    $password1 = $_POST['txtPassword1'];
    $password2 = $_POST['txtPassword2'];

    // Check if password passes our policies
    require '../passwordPolicies.php';
    echo "<pre>";
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
    
    if ($errorOccurred == 1) {
        echo "Could not update password<br>";
    }
    require "../home.php";
    echo "</pre>";
?>