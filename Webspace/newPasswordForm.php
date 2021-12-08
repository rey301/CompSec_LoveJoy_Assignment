<?php
    session_start();

    require 'sqlConn.php';

    //Verify user
    $token = hex2bin($_GET["token"]);
    $ts = hex2bin($_GET["ts"]);
    $email;
    $expiry;

    // Retrieve the table SystemUser
    $resetPwdResult = $conn -> query("SELECT * FROM ResetPassword");

    //Delete from database, the previous tokens and ts
    $tokenFound = False;
    $userVerified = False;
    while ($resetPwdRow = mysqli_fetch_array($resetPwdResult)) {
        // Check to see if the current user's name matches the one in the database
        if (password_verify($token, $resetPwdRow['resetToken']) && $resetPwdRow['resetTS'] != $ts) {
            $email = $resetPwdRow['resetEmail'];
            $expiry = $resetPwdRow['resetExpiry'];
            $tokenFound = True;
        }
    }

    if ($tokenFound) {
        //Check expiry
        if ($expiry < time()) {
            echo "Link expired!<br/>";
        }
        else {
            // Find user 
            $userResult = $conn -> query("SELECT * FROM SystemUser");

            while ($userRow = $userResult -> fetch_assoc()) {
                if ($userRow['Email'] == $email) {
                    $name = $userRow['Name'];

                    $id = $userRow['userID'];
                    // Remove the 'id' key if it exists
                    if (isset($_SESSION['id'])) {
                        unset($_SESSION['id']);
                    }
                    $_SESSION['id'] = $id;

                    $userVerified = True;
                }
            }

            if ($userVerified) {
                // Allow user to input new password
                echo "<form action='newPasswordCheck.php' method='POST'>";
                echo "<pre>";
                echo "<h1>Set a new password for ".htmlspecialchars($name)."</h1>";
                echo "New password      ";
                echo "<input name='txtPassword1' type='password' /> <br/>";
                echo "Confirm password  ";
                echo "<input name='txtPassword2' type='password'/><br/><br/>";
                echo "<input type='submit' value='Submit'><br/><br/>";
                echo "</pre>";
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
?>
