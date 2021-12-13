<?php
    session_start();
    echo "<pre>";
    if (isset($_POST['submit'])) {
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            $userAdmin = $_SESSION['userAdmin'];
            if (isset($_SESSION['userAdmin'])) {
                unset($_SESSION['userAdmin']);
            }

            $pin = $_SESSION['pin'];
            if (isset($_SESSION['pin'])) {
                unset($_SESSION['pin']);
            }

            $errorOccurred = 0;
            $authFailed = 0;

            $userName = $_SESSION['userName'];
            if (isset($_SESSION['userName'])) {
                unset($_SESSION['userName']);
            }

            $txtPin = $_POST['txtPin'];

            if ($txtPin == "") {
                echo "Pin is blank!<br>";
                $errorOccurred = 1;
            }
            else if ($txtPin != strip_tags($txtPin)) {
                echo "Pin contains HTML tags! Please remove!</br>";
                $errorOccurred = 1;
            }
            else {
                if ($pin == $txtPin) {
                    // Generate a new token for the user
                    require "../csrfToken.php";
                    if ($userAdmin == 1) {
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
                    echo "Pin failed<br>";
                    $errorOccurred = 1;
                }
            }
        }
        else {
            echo "Failed to authenticate token<br>";
            $authFailed = 1;
            $errorOccurred = 1;
        }
    }
    else {
        echo "Failed to authenticate user<br>";
        $authFailed = 1;
        $errorOccurred = 1;
    }
    
    if ($errorOccurred == 1) {
        echo "Couldn't log in user<br>";
        echo "Please resend the 2fa email by logging in again<br>";
    }

    require "../home.php";
    echo "</pre>";
?>
