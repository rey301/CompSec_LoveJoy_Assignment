<?php
    session_start();

    require '../sqlConn.php';

    echo "<pre>";

    $token = hex2bin($_GET["token"]);
    $ts = $_GET["ts"];

    // Retrieve the table ResetPassword
    $userResult = $conn -> query("SELECT * FROM SystemUser");
    
    // Find token in database
    $tokenFound = False;
    $userVerified = False;
    while ($userRow = mysqli_fetch_array($userResult)) {
        // Verify user
        if (password_verify($token, $userRow['UserToken']) && $userRow['UserTS'] == $ts) {
            $key = $_SESSION['key'];
            $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
            $sha2len = 32;

            // Decrypt username
            $encryptedName = $userRow['UserName'];
            $cName = base64_decode($encryptedName);
            $ivName = substr($cName, 0, $ivlen);
            $rawEncryptedName = substr($cName, $ivlen+$sha2len);
            $userName = openssl_decrypt($rawEncryptedName, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivName);

            $userID = $userRow['UserID'];
            $tokenFound = True;
            if ($userRow['UserVerified'] == 1) {
                echo "User " . htmlspecialchars($userName) . " is already verified!<br>";
                $userVerified = True;
            }
        }
    }

    if (!$userVerified && $tokenFound) { 
        //Update the user verified status to 1
        $verified = 1;
        $stmt = $conn->prepare("UPDATE SystemUser SET UserVerified = ? WHERE UserID = ?");
        $stmt->bind_param("is", $verified, $userID);
        if ($stmt->execute()) {
            echo "User" .  htmlspecialchars($userName) . " verified!<br>";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
        $stmt -> close();
    }
    else {
        echo "Could not verify token<br>";
    }

    require "../home.php";
    echo "</pre>";
?>