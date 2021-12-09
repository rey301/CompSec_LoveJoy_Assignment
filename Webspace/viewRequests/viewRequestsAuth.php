<?php
    session_start();

    require "../sqlConn.php";
    $errorOccurred = 0;

    echo "<pre>";
    // Retrieve which user was clicked from previous page
    $userResult = $conn->query("SELECT * FROM SystemUser");
    if ($userResult -> num_rows > 0) {
        while ($userRow = $userResult -> fetch_assoc()) {
            if ($userRow['UserAdmin'] == 0) {
                {
                    if (isset($_POST[$userRow['UserID']])) {
                        if (hash_equals($_SESSION['token'], $_POST['token'])) {
                            // Retrieve which user was clicked from previous page
                            $userResult = $conn->query("SELECT * FROM SystemUser");
                            if ($userResult -> num_rows > 0) {
                                while ($userRow = $userResult -> fetch_assoc()) {
                                    if ($userRow['UserAdmin'] == 0) {
                                        {
                                            if (isset($_POST[$userRow['UserID']])) {
                                                $userID = $userRow['UserID'];
                                                $userName = $_POST[$userRow['UserID']];
                                                echo "<h1>Requests from ". htmlspecialchars($userName) . "</h1>";
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
                                $stmt = $conn->prepare("SELECT * FROM EvaluationRequest WHERE UserID=?");
                                $stmt->bind_param("i", $userID);
                                $stmt->execute();
                                $evalReqResult = $stmt->get_result();
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
                                        echo "<td>" . $evalReqRow['Description'] . "</td>";
                                        echo "<td>" . $evalReqRow['Request'] . "</td>";
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
                                    echo "No requests from this user.<br><br>";
                                }
                                
                            }
                        }
                        else {
                            echo "Failed to authenticate token<br>";
                            $errorOccurred = 1;
                        }
                    } 
                }
            }
        }
    }

    if ($errorOccurred == 1) {
        echo "Could not view requests<br>";
    } 
    else {
        require "../csrfToken.php";
        echo "<form action='/viewRequests/viewRequestsForm.php' method='POST'>";
        echo "<input name='submit' type='submit' value='Go back'>";
        echo "<input type='hidden' name='token' value=".$token.">";
        echo "</form>";
    }

    require "../home.php";
    echo "</pre>";
?>