<?php
    session_start();
    require '../sqlConn.php';

    $errorOccurred = 0;
    $authFailed = 0;
    echo "<pre>";
    // Check if the submit button was pressed
    if (isset($_POST['submit'])) {
        // Check if the session token set in the session variable matches the one created when the form was loaded
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            // Check if the recaptcha box was ticked
            if (isset($_POST['g-recaptcha-response'])) {
                $captchaKey = "6LfUBKEdAAAAALbTSyxlPBzIQZEETfdGBQ8EV47P";
                $captchaResp = $_POST['g-recaptcha-response'];
                $captchaURL = "https://www.google.com/recaptcha/api/siteverify?secret=" . $captchaKey . "&response=" . $captchaResp;
                $captchaFile = file_get_contents($captchaURL);
                $captchaData = json_decode($captchaFile);
                if ($captchaData -> success == true) {
                    $userID = $_SESSION['userID'];
                    $description = $_POST['txtDescription'];
                    $request = $_POST['txtRequest'];
                    $contactMethod = $_POST['contactMethod'];

                    // Check text boxes if blank
                    if ($description == "") {
                        echo "Description is blank!<br>";
                        $errorOccurred = 1;
                    }
                    if ($request == "") {
                        echo "Request is blank!<br>";
                        $errorOccurred = 1;
                    }

                    // HTML tag checking (making sure no tags are used within the text fields)
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
                    if (in_array($accFileExt, $allowedExt) || $fileName == "") {
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
                        $key = $_SESSION['key'];
                        $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
                        $iv = openssl_random_pseudo_bytes($ivlen);

                        $rawEncryptedDesc = openssl_encrypt($description, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
                        $hmacDesc = hash_hmac('sha256', $rawEncryptedDesc, $key, $as_binary=true);
                        $encryptedDesc = base64_encode( $iv.$hmacDesc.$rawEncryptedDesc );

                        $rawEncryptedReq = openssl_encrypt($request, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
                        $hmacReq = hash_hmac('sha256', $rawEncryptedReq, $key, $as_binary=true);
                        $encryptedReq = base64_encode( $iv.$hmacReq.$rawEncryptedReq );
                        
                        $stmt = $conn->prepare("INSERT INTO EvaluationRequest (UserID, Description, Request, ContactMethod, FileName) 
                            VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("issss", $userID, $encryptedDesc, $encryptedReq, $contactMethod, $newFileName);
                        if ($stmt->execute()) {
                            echo "Evaluation submitted!<br>";
                        } 
                        else {
                            echo "Error: " . $sql . "<br>" . $conn -> error . "<br>";
                            $errorOccurred = 1;
                        }
                        $stmt -> close();
                    }
                }
                else {
                    echo "ReCaptcha failed<br>";
                    $errorOccurred = 1;
                }
            }
            else {
                echo "ReCaptcha error<br>";
                $errorOccurred = 1;
            }
        }
        else {
            echo "Failed to authenticate token<br>";
            $authFailed = 1;
            $errorOccurred = 1;
        }
    }
    else {
        echo "Failed to authenticate user<br>";
        $authFailed = 1;
        $errorOccurred = 1;
    }

    if ($errorOccurred == 1) {
        echo "Request could not be submitted<br>";
    }

    // If authentication token is valid then allow the user to go back
    if ($authFailed == 0) {
        require "../csrfToken.php";
        echo "<form action='/requestEvaluation/requestEvaluationForm.php' method='POST'>";
        echo "<input name='submit' type='submit' value='Go back'>";
        echo "<input type='hidden' name='token' value=".$token.">";
        echo "</form>";
    }

    require "../home.php";
    echo "</pre>";
?>