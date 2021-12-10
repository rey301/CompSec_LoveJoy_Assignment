<?php
    session_start();

    require '../sqlConn.php';

    echo "<pre>";
    //Verify user
    $token = hex2bin($_GET["token"]);
    $ts = $_GET["ts"];

    // Retrieve the table ResetPassword
    $userResult = $conn -> query("SELECT * FROM SystemUser");
    
    // Find token in database
    $tokenFound = False;
    $userVerified = False;
    while ($userRow = mysqli_fetch_array($userResult)) {
        if (password_verify($token, $userRow['UserToken']) && $userRow['UserTS'] == $ts) {
            $userName = $userRow['UserName'];
            $userID = $userRow['UserID'];
            $tokenFound = True;
            if ($userRow['UserVerified'] == 1) {
                echo "User" . htmlspecialchars($userName) . " is already verified!<br>";
                $userVerified = True;
            }
        }
    }

    if (!$userVerified && $tokenFound) { 
        $verified = 1;
        //Update user
        $stmt = $conn->prepare("UPDATE SystemUser SET UserVerified = ? WHERE UserID = ?");
        // Bind parameters to the query
        $stmt->bind_param("is", $verified, $userID);
        if ($stmt->execute()) {
            // Acknowledge password has been updated
            echo "User" .  htmlspecialchars($userName) . " verified!<br>";
        } 
        else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        }
          $stmt -> close();
    }
    else {
        echo "Could not verify token<br>";
    }

    require "../home.php";
    echo "</pre>";
?>