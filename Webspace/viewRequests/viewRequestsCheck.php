<?php
    session_start();

    require "../sqlConn.php";

    $errorOccurred = 0;
    $authFailed = 0;

    // SSL Data
    $key = $_SESSION['key'];
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $sha2len = 32;

    echo "<pre>";
    // Retrieve which user was clicked from previous page
    $userResult = $conn->query("SELECT * FROM SystemUser");
    if ($userResult -> num_rows > 0) {
        while ($userRow = $userResult -> fetch_assoc()) {
            if ($userRow['UserAdmin'] == 0) {
                {
                    if (isset($_POST[$userRow['UserID']])) {
                        if (hash_equals($_SESSION['token'], $_POST['token'])) {
                            $userID = $userRow['UserID'];
                            $userName = $_POST[$userRow['UserID']];
                            echo "<h1>Requests from ". htmlspecialchars($userName) . "</h1>";
                        }
                        else {
                            echo "Failed to authenticate token<br>";
                            $authFailed = 1;
                            $errorOccurred = 1;
                        }
                    }
                }
            }
        }
    }
    else {
        echo "User table is empty.<br>";
        $errorOccurred = 1;
    }

    if ($errorOccurred == 0) {
        // Retrieve all requests from specified user
        $stmt = $conn->prepare("SELECT * FROM EvaluationRequest WHERE UserID = ?");
        $stmt -> bind_param("i", $userID);
        $stmt -> execute();
        $evalReqResult = $stmt -> get_result();
        $stmt -> close();

        if ($evalReqResult -> num_rows > 0) {
            echo "<table border='1'>
            <tr>
            <th>Request ID</th>
            <th>Description</th>
            <th>Request</th>
            <th>Image</th>
            </tr>";
            while ($evalReqRow = $evalReqResult -> fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $evalReqRow['RequestID'] . "</td>";

                // Decrypt description
                $encryptedDesc = $evalReqRow['Description'];
                $cDesc = base64_decode($encryptedDesc);
                $ivDesc = substr($cDesc, 0, $ivlen);
                $hmacDesc = substr($cDesc, $ivlen, $sha2len);
                $rawEncryptedDesc = substr($cDesc, $ivlen+$sha2len);
                $description = openssl_decrypt($rawEncryptedDesc, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivDesc);
                $calcmacDesc = hash_hmac('sha256', $rawEncryptedDesc, $key, $as_binary=true);
                if (hash_equals($hmacDesc, $calcmacDesc)) // timing attack safe comparison
                {
                    echo "<td>" . htmlspecialchars($description) . "</td><br>";
                }

                // Decrypt request
                $encryptedReq = $evalReqRow['Request'];
                $cReq = base64_decode($encryptedReq);
                $ivReq = substr($cReq, 0, $ivlen);
                $hmacReq = substr($cReq, $ivlen, $sha2len);
                $rawEncryptedReq = substr($cReq, $ivlen+$sha2len);
                $request = openssl_decrypt($rawEncryptedReq, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivReq);
                $calcmacReq = hash_hmac('sha256', $rawEncryptedReq, $key, $as_binary=true);
                if (hash_equals($hmacReq, $calcmacReq)) // timing attack safe comparison
                {
                    echo "<td>" . htmlspecialchars($request) . "</td><br>";
                }

                if ($evalReqRow['FileName'] == NULL) {
                    echo "<td>Image not provided</td>";
                }
                else if (file_exists("/images/" . $evalReqRow['FileName'])) {
                    echo "<td>Image unavailable</td>";
                }
                else {
                    echo "<td><img src=/images/" . $evalReqRow['FileName'] . "></td>";
                }
                echo "</tr>";
            }
            echo "</table><br><br>";
        }
        else {
            echo "No requests from this user<br>";
        }

        if ($authFailed == 0) {}
            require "../csrfToken.php";
            echo "<form action='/viewRequests/viewRequestsForm.php' method='POST'>";
            echo "<input name='submit' type='submit' value='Go back'>";
            echo "<input type='hidden' name='token' value=".$token.">";
            echo "</form>";
        }
    }
    else 
    {
        echo "Could not view requests<br>";
    } 

    require "../home.php";
    echo "</pre>";
?>