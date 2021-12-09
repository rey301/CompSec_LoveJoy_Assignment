<?php
    require "../sqlConn.php";

    // Query
    $userResult = $conn->query("SELECT * FROM SystemUser");
    $errorOccurred = 0;

    echo "<form action='/viewRequests/viewRequestsCheck.php'' method='POST'>";
    echo "<pre>";
    echo "<h1>List of users</h1>";
    echo "<h2>Click usernames to view requests</h2>";
    echo "<table border='1'>
    <tr>
    <th>User ID</th>
    <th>Name</th>
    </tr>";
    
    if ($userResult -> num_rows > 0) {
        while ($userRow = $userResult -> fetch_assoc()) {
            if ($userRow['Admin'] == 0) {
                {
                    echo "<tr>";
                    echo "<td>" . $userRow['UserID'] . "</td>";
                    echo "<td><input type='submit' value=" . $userRow['Name'] . " name=" . $userRow['UserID'] . "></td>";
                    echo "</tr>";
                }
            }
        }
    }
    else {
        echo "User table is empty.<br>";
        $errorOccurred = 1;
    }
    echo "</table>";
    echo "</pre>";
    echo "</form>";
?>