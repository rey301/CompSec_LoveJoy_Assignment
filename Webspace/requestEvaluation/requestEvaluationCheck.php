<?php
    session_start();
    require '../sqlConn.php';

    $errorOccurred = 0;

    // Values come from user, through webform
    $userID = $_SESSION['userID'];
    $description = $_POST['txtDescription'];
    $request = $_POST['txtRequest'];
    $contactMethod = $_POST['contactMethod'];

    // Check text boxes if blank
    if ($descripition == "") {
        echo "Description is blank!<br>";
        $errorOccurred = 1;
    }
    if ($request=="") {
        echo "Request is blank!<br>";
        $errorOccurred = 1;
    }

    //HTML tag checking (making sure no tags are used within the text fields)
    if ($description != strip_tags($description)) {
        echo "Description contains HTML tags! Please remove!</br>";
        $errorOccurred = 1;
    }

    if ($request != strip_tags($request)) {
        echo "Request contains HTML tags! Please remove!</br>";
        $errorOccurred = 1;
    }
    
    //Retrieve file attributes
    $fileImage = $_FILES['fileImage'];
    $fileName = $_FILES['fileImage']['name'];
    $fileTmpName = $_FILES['fileImage']['tmp_name'];
    $fileSize = $_FILES['fileImage']['size'];
    $fileError = $_FILES['fileImage']['error'];
    $fileType = $_FILES['fileImage']['type'];
    $fileExt = explode('.', $fileName);

    // Separate file name into into its extension and name
    $accFileExt = strtolower(end($fileExt)); 
    $allowedExt = array('jpg', 'jpeg', 'png');

    // Checking if file is an image, error occurred and file size doesn't exceed 8MB
    if (in_array($accFileExt, $allowedExt)) {
        if ($fileError === 0) {
            if ($fileSize < 8000000) {
                $newFileName = uniqid('',true).".".$accFileExt;
                $fileDestination = "../images/".$newFileName;
                move_uploaded_file($fileTmpName, $fileDestination);
            }
            else {
                echo "File size is too big. Max is 8MB.<br>";
                $errorOccurred = 1;
            }
        }
        else {
            echo "File error: " . $fileError . "<br>";
            $errorOccurred = 1;
        }
    }
    else {
        echo "File type is not permitted.<br>";
        $errorOccurred = 1;
    }

    // Add data to database
    if ($errorOccurred == 0) {
        $stmt = $conn->prepare("INSERT INTO EvaluationRequest (UserID, Description, Request, ContactMethod, FileName) 
            VALUES (?, ?, ?, ?, ?)");

        // Bind parameters to the query
        $stmt->bind_param("issss", $userID, $description, $request, $contactMethod, $newFileName);
        
        if ($stmt->execute()) {
            echo "Evaluation submitted! <br>";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
            $errorOccurred = 1;
        }
    
        $stmt -> close();
    }

    if ($errorOccurred == 1) {
        echo "Request could not be submitted.<br>";
    }
    
?>