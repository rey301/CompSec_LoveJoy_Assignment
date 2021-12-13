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
                $userID = $userRow['UserID'];
                $userAttempts = $userRow['UserAttempts'];
                $userExpiry = $userRow['UserExpiry'];

                if ($userExpiry-time() <= 0 && $userExpiry != 1) {
                  $userExpiry = 1;
                  $userAttempts = 0;

                  // Set the expiry and attempts on database
                  $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ?, UserAttempts = ? WHERE UserID = ?");
                  // Bind parameters to the query
                  $stmt->bind_param("iii", $userExpiry, $userAttempts, $userID);
                  
                  if (!$stmt->execute()) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                    $errorOccurred = 1;
                  } 
                  $stmt -> close();
                } 

                // Check for attempt expiry
                if ($userExpiry == 1) {
                  if (password_verify($password, $userRow['UserPassword'])) {
                    $userExpiry = 1;
                    $userAttempts = 0;

                    // Set the expiry and attempts on database
                    $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ?, UserAttempts = ? WHERE UserID = ?");
                    // Bind parameters to the query
                    $stmt->bind_param("iii", $userExpiry, $userAttempts, $userID);
                    
                    if (!$stmt->execute()) {
                      echo "Error: " . $sql . "<br>" . $conn->error;
                      $errorOccurred = 1;
                    } 
                    $stmt -> close();
                    
                    if ($userRow['UserVerified']){
                      if (isset($_SESSION['resetPin'])) {
                        unset($_SESSION['resetPin']);
                      }
                      $_SESSION['resetPin'] = 1;
  
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
                      $_SESSION['userID'] = $userID;
  
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
                    $userAttempts += 1; 

                    // Set the attempts on database
                    $stmt = $conn->prepare("UPDATE SystemUser SET UserAttempts= ? WHERE UserID = ?");
                    // Bind parameters to the query
                    $stmt->bind_param("ii", $userAttempts, $userID);
                    
                    if (!$stmt->execute()) {
                      echo "Error: " . $sql . "<br>" . $conn->error;
                      $errorOccurred = 1;
                    }
                
                    $stmt -> close();

                    if ($userAttempts > 20) {
                      $userExpiry = time() + 1200;

                      // Set the expiry on database
                      $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ? WHERE UserID = ?");
                      // Bind parameters to the query
                      $stmt->bind_param("ii", $userExpiry, $userID);
                      
                      if (!$stmt->execute()) {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                        $errorOccurred = 1;
                      } 

                      $stmt -> close();
                    }

                    echo "You have " . (21-htmlspecialchars($userAttempts)) . " attempts left<br>";
                    $errorOccurred = 1;
                  }
                }
                else {
                    echo "Please wait " . ($userExpiry-time()) . " seconds before you can attempt to log in again<br>";
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