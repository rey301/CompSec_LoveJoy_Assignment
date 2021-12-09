<?php 
    require "../sqlConn.php";

    $userID = $_POST['userID'];
    echo "userID: ".$userID."<br>";
    echo "<pre>";

    echo "<table border='1'>
    <tr>
    <th>Request ID</th>
    <th>Description</th>
    <th>Request</th>
    <th>File Name</th>
    </tr>";

    $sql = "SELECT * FROM EvaluationRequest WHERE UserID = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $userID);

    $evalReqResult = $conn->query("SELECT * FROM EvaluationRequest");
    $errorOccurred = 0;
    if ($evalReqResult -> num_rows > 0) {
        while ($evalReqRow = $evalReqResult -> fetch_assoc()) {
            if ($evalReqRow['UserID'] == $userID) {
                echo "<tr>";
                echo "<td>" . $evalReqRow['RequestID'] . "</td>";
                echo "<td>" . $evalReqRow['Description'] . "</td>";
                echo "<td>" . $evalReqRow['Request'] . "</td>";
                echo "<td>" . $evalReqRow['FileName'] . "</td>";
                echo "</tr>";
            }
        }
    }
    else {
        echo "Evaluation request table is empty.<br>";
        $errorOccurred = 1;
    }
    echo "</table>";
    echo "</pre>";

    $stmt -> close();
?>