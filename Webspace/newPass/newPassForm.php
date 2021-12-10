<?php
    session_start();

    require '../sqlConn.php';

    //Verify user
    $token = hex2bin($_GET["token"]);
    $ts = $_GET["ts"];
    $email;
    $expiry;

    // Retrieve the table ResetPassword
    $resetPwdResult = $conn -> query("SELECT * FROM ResetPassword");

    //Delete from database, the previous tokens and ts
    $tokenFound = False;
    $userVerified = False;
    while ($resetPwdRow = mysqli_fetch_array($resetPwdResult)) {
        // Check to see if the current user's name matches the one in the database
        if (password_verify($token, $resetPwdRow['ResetToken']) && $resetPwdRow['ResetTS'] == $ts) {
            $email = $resetPwdRow['ResetEmail'];
            $expiry = $resetPwdRow['ResetExpiry'];
            $tokenFound = True;
        }
    }

    echo "<pre>";
    if ($tokenFound) {
        //Check expiry
        if ($expiry < time()) {
            echo "Link expired!<br/>";
        }
        else {
            // Find user 
            $userResult = $conn -> query("SELECT * FROM SystemUser");

            while ($userRow = $userResult -> fetch_assoc()) {
                if ($userRow['UserEmail'] == $email) {
                    $userName = $userRow['UserName'];

                    // Remove the 'id' key if it exists
                    if (isset($_SESSION['userID'])) {
                        unset($_SESSION['userID']);
                    }
                    $_SESSION['userID'] = $userRow['UserID'];

                    $userVerified = True;
                }
            }

            if ($userVerified) {
                // Allow user to input new password
                require "../csrfToken.php";
                echo "<form action='/newPass/newPassCheck.php' method='POST'>";
                echo "<h1>Set a new password for ".htmlspecialchars($userName)."</h1>";
                echo "New password      ";
                echo "<input name='txtPassword1' type='password' /> <br/>";
                echo "Confirm password  ";
                echo "<input name='txtPassword2' type='password'/><br/><br/>";
                echo "<input type='hidden' name='token' value=".$token.">";
                echo "<input name='submit' type='submit' value='Submit'><br/><br/><br>";
                echo "</form>";
            }
            else {
                echo "Could not verify user. Try again.<br/>";
            }
        }
    }
    else {
        echo "Could not find token. Try again.<br/>";
    }
    require "../home.php";
    echo "</pre>";
?>
