<?php 
    require "../sqlConn.php";

    // Query
    $userResult = $conn->query("SELECT * FROM SystemUser");
    $errorOccurred = 0;
    $userID;

    if ($userResult -> num_rows > 0) {
        while ($userRow = $userResult -> fetch_assoc()) {
            if ($userRow['Admin'] == 0) {
                {
                    if (isset($_POST[$userRow['UserID']])) {
                        $userID = $userRow['UserID'];
                        $userName = $_POST[$userRow['UserID']];
                    }
                }
            }
        }
    }
    else {
        echo "User table is empty.<br>";
        $errorOccurred = 1;
    }

    echo "<pre>";
    echo "<h1>Requests from ". htmlspecialchars($userName) . "</h1>";

    $sql = "SELECT * FROM EvaluationRequest WHERE UserID = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $userID);


    $stmt = $conn->prepare("SELECT * FROM EvaluationRequest WHERE UserID=?");

    // Bind parameters to the query
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $evalReqResult = $stmt->get_result();
    $errorOccurred = 0;
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
        echo "</table>";
    }
    else {
        echo "No requests from this user.<br>";
        $errorOccurred = 1;
    }

    echo "</pre>";

    $stmt -> close();
?>