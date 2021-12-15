<?php
    session_start();

    require '../sqlConn.php';

    $errorOccurred = 0;

    // SSL Data
    $key = $_SESSION['key'];
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $sha2len = 32;

    echo "<pre>";

    // Check if the submit button was pressed
    if (isset($_POST['submit'])) {
      // Check if the session token set in the session variable matches the one created when the form was loaded
      if (hash_equals($_SESSION['token'], $_POST['token'])) {
        // Check if the recaptcha box was ticked
        if (isset($_POST['g-recaptcha-response'])) {
          $captchaKey = "6LfUBKEdAAAAALbTSyxlPBzIQZEETfdGBQ8EV47P";
          $captchaResp = $_POST['g-recaptcha-response'];
          $captchaURL = "https://www.google.com/recaptcha/api/siteverify?secret=" . $captchaKey . "&response=" . $captchaResp;
          $captchaFile = file_get_contents($captchaURL);
          $captchaData = json_decode($captchaFile);

          if ($captchaData -> success == true) {
            // Retrieve inputted login data
            $postName = $_POST['txtUserName'];
            $postPass = $_POST['txtPassword'];
          
            // Retrieve user table data
            $stmt = $conn->prepare("SELECT * FROM SystemUser");
            if (!$stmt -> execute()) {
              echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
              $errorOccurred = 1;
            } 
            $userResult = $stmt->get_result();
            $stmt -> close();
            
            // Check if table is empty
            if ($userResult -> num_rows > 0) {
              $userFound = False;

              while ($userRow = $userResult -> fetch_assoc()) {
                // Decrypt username from table
                $encryptedName = $userRow['UserName'];
                $cName = base64_decode($encryptedName);
                $ivName = substr($cName, 0, $ivlen);
                $rawEncryptedName = substr($cName, $ivlen+$sha2len);
                $decryptedName = openssl_decrypt($rawEncryptedName, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivName);

                if ($postName == $decryptedName) {
                  $userFound = True;

                  // User data 
                  $userName = $decryptedName;
                  $userID = $userRow['UserID'];
                  $userAttempts = $userRow['UserAttempts'];
                  $userExpiry = $userRow['UserExpiry'];
                  $userAdmin = $userRow['UserAdmin'];
                  $userPassword =  $userRow['UserPassword'];
                  $userVerified = $userRow['UserVerified'];

                  // Decrypt user email
                  $encryptedEmail = $userRow['UserEmail'];
                  $cEmail = base64_decode($encryptedEmail);
                  $ivEmail = substr($cEmail, 0, $ivlen);
                  $rawEncryptedEmail = substr($cEmail, $ivlen+$sha2len);
                  $userEmail = openssl_decrypt($rawEncryptedEmail, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivEmail);
                }
              }

              if ($userFound) {
                // Checking if the expiry time for attempts has elapsed 
                if ($userExpiry < time() && $userExpiry != 1) {
                  // Reset the expiry and attempts on database
                  $userExpiry = 1;
                  $userAttempts = 0;
                  
                  $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ?, UserAttempts = ? WHERE UserID = ?");
                  $stmt->bind_param("iii", $userExpiry, $userAttempts, $userID);
                  
                  if (!$stmt->execute()) {
                    echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                    $errorOccurred = 1;
                  } 
                  $stmt -> close();
                } 

                if ($userExpiry == 1) {
                  if (password_verify($postPass, $userPassword)) {
                    // Reset the expiry and attempts on database
                    $userExpiry = 1;
                    $userAttempts = 0;

                    $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ?, UserAttempts = ? WHERE UserID = ?");
                    $stmt->bind_param("iii", $userExpiry, $userAttempts, $userID);
                    
                    if (!$stmt->execute()) {
                      echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                      $errorOccurred = 1;
                    } 
                    $stmt -> close();

                    // Check if user has been verified via email
                    if ($userVerified) {
                      // Session variables needed for 2 factor authentication
                      if (isset($_SESSION['userEmail'])) {
                        unset($_SESSION['userEmail']);
                      }
                      $_SESSION['userEmail'] = $userEmail;

                      if (isset($_SESSION['userAdmin'])) {
                        unset($_SESSION['userAdmin']);
                      }
                      $_SESSION['userAdmin'] = $userAdmin;
                      
                      if (isset($_SESSION['userID'])) {
                        unset($_SESSION['userID']);
                      }
                      $_SESSION['userID'] = $userID;

                      if (isset($_SESSION['userName'])) {
                        unset($_SESSION['userName']);
                      }
                      $_SESSION['userName'] = $userName;
                    
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

                    // Increment attempts on database
                    $userAttempts += 1; 

                    $stmt = $conn->prepare("UPDATE SystemUser SET UserAttempts= ? WHERE UserID = ?");
                    $stmt->bind_param("ii", $userAttempts, $userID);
                    
                    if (!$stmt->execute()) {
                      echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                      $errorOccurred = 1;
                    }
                
                    $stmt -> close();

                    // If exceeds max attempts then set the expiry from current time
                    if ($userAttempts > 20) {
                      // Set the expiry on database
                      $userExpiry = time() + 1200;

                      $stmt = $conn->prepare("UPDATE SystemUser SET UserExpiry= ? WHERE UserID = ?");
                      $stmt->bind_param("ii", $userExpiry, $userID);
                      
                      if (!$stmt->execute()) {
                        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
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
              else {
                echo "User does not exist<br>";
              }

            }
            else {
              echo "User table is empty<br>";
              $errorOccurred = 1;
            }
          }
          else {
            echo "ReCaptcha failed<br>";
            $errorOccurred = 1;
          }
        }
        else {
          echo "ReCaptcha error<br>";
          $errorOccurred = 1;
        }
      }
      else {
          echo "Failed to authenticate token<br>";
          $errorOccurred = 1;
      }
    }
    else {
        echo "Form error<br>";
        $errorOccurred = 1;
    }

    if ($errorOccurred == 1) {
        echo "Login unsuccessful<br>";
    }
    require "../home.php";
    echo "</pre>";
?>