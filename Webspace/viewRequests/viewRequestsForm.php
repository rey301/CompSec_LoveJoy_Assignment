<?php
    session_start();

    require "../sqlConn.php";  

    $errorOccurred = 0;

    // SSL Data
    $key = $_SESSION['key'];
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $sha2len = 32;
    
    echo "<pre>";
    if (isset($_POST['submit'])) {
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            require "../csrfToken.php";
            echo "<form action='/viewRequests/viewRequestsCheck.php'' method='POST'>";
            echo "<input type='hidden' name='token' value=".$token.">";
            echo "<h1>List of users</h1>";
            echo "<h2>Click usernames to view requests</h2>";
            echo "<table border='1'>
            <tr>
            <th>User ID</th>
            <th>Name</th>
            </tr>";
            
            // View the users except admin
            $userResult = $conn->query("SELECT * FROM SystemUser");
            if ($userResult -> num_rows > 0) {
                while ($userRow = $userResult -> fetch_assoc()) {
                    if ($userRow['UserAdmin'] == 0) {
                        {
                            echo "<tr>";
                            echo "<td>" . $userRow['UserID'] . "</td>";

                            // Decrypt username
                            $encryptedName = $userRow['UserName'];
                            $cName = base64_decode($encryptedName);
                            $ivName = substr($cName, 0, $ivlen);
                            $rawEncryptedName = substr($cName, $ivlen+$sha2len);
                            $userName = openssl_decrypt($rawEncryptedName, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivName);

                            echo "<td><input type='submit' value=" . $userName . " name=" . $userRow['UserID'] . "></td>";
                            echo "</tr>";
                        }
                    }
                }
            }
            else {
                echo "User table is empty<br>";
                $errorOccurred = 1;
            }
            echo "</table><br>";
            echo "</pre>";
            echo "</form>";
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
        echo "Could not view requests.<br>";
    }
    
    require "../home.php";
    echo "</pre>";
?>