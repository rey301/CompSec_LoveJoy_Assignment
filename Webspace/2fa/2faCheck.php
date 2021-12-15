<?php
    session_start();

    $errorOccurred = 0;
    $authFailed = 0;

    echo "<pre>";

    // Check if the submit button was pressed
    if (isset($_POST['submit'])) {
        // Check if the session token set in the session variable matches the one created when the form was loaded
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            
            // Retrieve session variables
            if (isset($_SESSION['userAdmin'])) {
                $userAdmin = $_SESSION['userAdmin'];
                unset($_SESSION['userAdmin']);
            }
            else {
                echo "Session error<br>";
                $errorOccurred = 1;
            }
            
            if (isset($_SESSION['pin'])) {
                $pin = $_SESSION['pin'];
                unset($_SESSION['pin']);
            }
            else {
                echo "Session error<br>";
                $errorOccurred = 1;
            }
            
            if (isset($_SESSION['userName'])) {
                $userName = $_SESSION['userName'];
                unset($_SESSION['userName']);
            }
            else {
                echo "Session error<br>";
                $errorOccurred = 1;
            }

            $postPin = $_POST['txtPin'];

            if ($postPin == "") {
                echo "Pin is blank!<br>";
                $errorOccurred = 1;
            }
            else if ($postPin != strip_tags($postPin)) {
                echo "Pin contains HTML tags! Please remove!</br>";
                $errorOccurred = 1;
            }
            else {
                if ($pin == $postPin) {
                    // Generate a new token for the user for logging in 
                    require "../csrfToken.php";

                    // Check if the user is an admin or not, display pages appropriately
                    if ($userAdmin == 1) {
                      echo "<form action='/viewRequests/viewRequestsForm.php' method='POST'>";
                      echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
                      echo "<h2>Administrator page</h2>";
                      echo "<input type='hidden' name='token' value=".$token.">";
                      echo "<input name='submit' type='submit' value='View requests'><br>";
                      echo "</form>";
                    } 
                    else {
                      echo "<form action='/requestEvaluation/requestEvaluationForm.php' method='POST'>";
                      echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
                      echo "<h2>Welcome to Lovejoy!</h2>";
                      echo "<input type='hidden' name='token' value=".$token.">";
                      echo "<input name='submit' type='submit' value='Request evaluation'><br>";    
                      echo "</form>";
                    }
                }
                else {
                    echo "Pin failed<br>";
                    $errorOccurred = 1;
                }
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
        echo "Couldn't log in user<br>";
        echo "Please resend the 2fa email by logging in again<br>";
    }

    require "../home.php";
    echo "</pre>";
?>
