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
                    //Save user's id to session variable
                    if (isset($_SESSION['userID'])) {
                      unset($_SESSION['userID']);
                    }
                    $_SESSION['userID'] = $userRow['UserID'];
                    
                    // Generate a new token for the user
                    require "../csrfToken.php";
                    if ($userRow['UserAdmin'] == 1) {
                      echo "<form action='/viewRequests/viewRequestsForm.php' method='POST'>";
                      echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
                      echo "<h2>Administrator page</h2>";
                      echo "<input name='submit' type='submit' value='View requests'><br/>";
                      echo "<input type='hidden' name='token' value=".$token."><br>";
                      echo "</form>";
                    } 
                    else {
                      echo "<form action='/requestEvaluation/requestEvaluationForm.php' method='POST'>";
                      echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
                      echo "<h2>Welcome to Lovejoy!</h2>";
                      echo "<input name='submit' type='submit' value='Request evaluation'><br/>";
                      echo "<input type='hidden' name='token' value=".$token."><br>";
                      echo "</form>";
                    }
                  }
                  else {
                    echo "Please verify your email before logging in<br>";
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