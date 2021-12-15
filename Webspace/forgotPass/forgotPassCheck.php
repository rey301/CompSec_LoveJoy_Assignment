<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    session_start();

    $errorOccurred = 0;
    $authFailed = 0;
    
    echo "<pre>";
    if (isset($_POST['submit'])) {
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            $key = $_SESSION['key'];
            $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
            $iv = openssl_random_pseudo_bytes($ivlen);
            $sha2len = 32;
            
            require '../sqlConn.php';
        
            // Copy all of the data from the form into variables
            $postEmail = $_POST['txtEmail'];

            // Retrieve user table
            $stmt = $conn->prepare("SELECT * FROM SystemUser");
            if($stmt->execute()) {
                $userResult = $stmt->get_result();
            }
            else {
                echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                $errorOccurred = 1;
            }
            $stmt -> close();
        
            // Checking if text box is blank
            if ($postEmail == "") {
                echo "Email is blank! <br>";
                $errorOccurred = 1;
            }
            // Checking for HTML tags to prevent XSS
            else if ($postEmail != strip_tags($postEmail)) {
                echo "Email contains HTML tags ('<','>'). Please remove!<br>";
                $errorOccurred = 1;
            }
            else {
                $emailFound = False;
                // Check if email exists in the database
                if ($userResult -> num_rows > 0) {
                    while ($userRow = $userResult -> fetch_assoc()) {
                        // Decrypt current email
                        $encryptedEmail = $userRow['UserEmail'];
                        $cEmail = base64_decode($encryptedEmail);
                        $ivEmail = substr($cEmail, 0, $ivlen);
                        $rawEncryptedEmail = substr($cEmail, $ivlen+$sha2len);
                        $decryptedEmail= openssl_decrypt($rawEncryptedEmail, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivEmail);
                        
                        if ($postEmail == $decryptedEmail) {
                            $emailFound = True;

                            // Decrypt username
                            $encryptedName = $userRow['UserName'];
                            $cName = base64_decode($encryptedName);
                            $ivName = substr($cName, 0, $ivlen);
                            $rawEncryptedName = substr($cName, $ivlen+$sha2len);
                            $userName = openssl_decrypt($rawEncryptedName, $cipher, $key, $options=OPENSSL_RAW_DATA, $ivName);
                        }
                    }
                    if ($emailFound) {
                        //Construct token and ts
                        $token = random_bytes(35);
                        $ts = bin2hex(random_bytes(8));
            
                        // URL to be sent to user's email address
                        $url = "https://lovejoyapplication.000webhostapp.com/newPass/newPassForm.php?token=".bin2hex($token)."&ts=".$ts;
            
                        // Link expires after 30 minutes
                        $expiry = time() + 900; 
                        
                        // Delete previous token and ts from database
                        $stmt = $conn->prepare("DELETE FROM ResetPassword WHERE ResetEmail = ?");
                        $stmt->bind_param("s", $email);
                        if (!$stmt->execute()) {
                            echo "Error: " . $sql . "<br>" . $conn->error;
                        } 
                        $stmt -> close();
            
                        //Insert new token and ts
                        $stmt = $conn->prepare("INSERT INTO ResetPassword (ResetEmail, ResetToken, ResetTS, ResetExpiry) VALUES (?, ?, ?, ?)");
            
                        // Encrypt token
                        if (defined('PASSWORD_ARGON2I')) {
                            $tokenHash = password_hash($token, PASSWORD_ARGON2I);
                        }
                        else {
                            $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                        }

                        // Encrypt email
                        $rawEncryptedEmail = openssl_encrypt($postEmail, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
                        $hmacEmail = hash_hmac('sha256', $rawEncryptedEmail, $key, $as_binary=true);
                        $encryptedEmail = base64_encode( $iv.$hmacEmail.$rawEncryptedEmail );
                        
                        $stmt->bind_param("ssss", $encryptedEmail, $tokenHash, $ts, $expiry);
            
                        if (!$stmt->execute()) {
                            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
                        } 
            
                        $stmt -> close();

                        // Generate message
                        $msgHTML = "Hello " . htmlspecialchars($userName) . ", we got a request to reset your Lovejoy password: " .$url;

                        //Sending email using PHPmailer
                        require $_SERVER['DOCUMENT_ROOT'] . '/mail/Exception.php';
                        require $_SERVER['DOCUMENT_ROOT'] . '/mail/PHPMailer.php';
                        require $_SERVER['DOCUMENT_ROOT'] . '/mail/SMTP.php';
                        
                        $mail = new PHPMailer;
                        $mail->isSMTP(); 
                        $mail->SMTPDebug = 0; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
                        $mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
                        $mail->Port = 587; // TLS only
                        $mail->SMTPSecure = 'tls'; // ssl is deprecated
                        $mail->SMTPAuth = true;
                        $mail->Username = 'lovejoy5431@gmail.com'; // email
                        $mail->Password = 'david504'; // password
                        $mail->setFrom('david@lovejoy.com', 'LoveJoy'); // From email and name
                        $mail->addAddress($email, $userName); // to email and name
                        $mail->Subject = 'Reset your Lovejoy password';
                        $mail->msgHTML($msgHTML); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
                        $mail->AltBody = 'HTML messaging not supported'; // If html emails is not supported by the receiver, show this body
                        // $mail->addAttachment('images/phpmailer_mini.png'); //Attach an image file
                        $mail->SMTPOptions = array(
                            'ssl' => array(
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            )
                        );
            
                        if(!$mail->send()) {
                            echo "Mailer Error: " . $mail->ErrorInfo;
                            $errorOccurred = 1;
                        }
                    }
                    else {
                        echo "Email doesn't exist<br>";
                        $errorOccurred = 1;
                    }
                } 
                else {
                    echo "User table is empty<br>";
                    $errorOccurred = 1;
                }
            }
        }
        else {
            echo "Failed to authenticate token<br>";
            $authFailed = 1;
            $errorOccurred = 1;
        }
    }
    else {
        echo "User not authenticated<br>";
        $authFailed = 1;
        $errorOccurred = 1;
    }
    if ($errorOccurred == 0) {  
        echo "Instructions have been sent to your email address<br>";
    }
    else {
        echo "Email was not sent<br>";
    }

    // If authentication token is valid then allow the user to go back
    if ($authFailed == 0) {
        echo "<form action='/forgotPass/forgotPassForm.php' method='POST'>";
        echo "<input type='submit' value='Go back'>";
        echo "</form>";
    }
    
    require "../home.php";
    echo "</pre>";
?>