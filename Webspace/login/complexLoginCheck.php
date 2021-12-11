<?php
    session_start();
    $errorOccurred = 0;
    
    echo "<pre>";
    if (isset($_POST['submit'])) {
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            require '../sqlConn.php';
  
            $userName = $_POST['txtUserName'];
            $password = $_POST['txtPassword'];
          
            // Find the user data
            $stmt = $conn->prepare("SELECT * FROM SystemUser WHERE UserName = ?");
            $stmt->bind_param("s", $userName);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $stmt->close();
          
            if ($userResult -> num_rows > 0) {
              while ($userRow = $userResult -> fetch_assoc()) {
                if (password_verify($password, $userRow['UserPassword'])) {
                  if ($userRow['UserVerified']){
                    if (isset($_SESSION['userEmail'])) {
                      unset($_SESSION['userEmail']);
                    }
                    $_SESSION['userEmail'] = $userRow['UserEmail'];

                    if (isset($_SESSION['userAdmin'])) {
                      unset($_SESSION['userAdmin']);
                    }
                    $_SESSION['userAdmin'] = $userRow['UserAdmin'];
                    
                    //Save user's id to session variable
                    if (isset($_SESSION['userID'])) {
                      unset($_SESSION['userID']);
                    }
                    $_SESSION['userID'] = $userRow['UserID'];

                    //Save user name to session variable
                    if (isset($_SESSION['userName'])) {
                      unset($_SESSION['userName']);
                    }
                    $_SESSION['userName'] = $userRow['UserName'];
                  
                    // 2 factor authentication
                    require '../2fa/2faForm.php';
                  }
                  else {
                    echo "Please verify your registered email before logging in<br>";
                    $errorOccurred = 1;
                  }
                }
                else {
                  echo "Wrong Password<br>";
                  $errorOccurred = 1;
                }
              }
            }
            else {
              echo "User does not exist<br>";
              $errorOccurred = 1;
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
        echo "Login unsuccessful<br>";
    }
    require "../home.php";
    echo "</pre>";
?>