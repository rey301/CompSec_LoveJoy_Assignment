<?php
    require "../sqlConn.php";

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
    
    // View the users except admin
    $userResult = $conn->query("SELECT * FROM SystemUser");
    if ($userResult -> num_rows > 0) {
        while ($userRow = $userResult -> fetch_assoc()) {
            if ($userRow['UserAdmin'] == 0) {
                {
                    echo "<tr>";
                    echo "<td>" . $userRow['UserID'] . "</td>";
                    echo "<td><input type='submit' value=" . $userRow['UserName'] . " name=" . $userRow['UserID'] . "></td>";
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