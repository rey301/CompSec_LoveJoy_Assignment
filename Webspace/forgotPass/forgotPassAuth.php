<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    session_start();
    $errorOccurred = 0;
    
    echo "<pre>";
    if (isset($_POST['submit'])) {
        echo "session token: ".$_SESSION['token']."<br>";
        echo "post token: ".$_POST['token']."<br>";
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            require '../sqlConn.php';
        
            // Copy all of the data from the form into variables
            $email = $_POST['txtEmail'];
        
            // Retrieve email row using prepared statement
            $stmt = $conn->prepare("SELECT * FROM SystemUser WHERE UserEmail = ?");
            $stmt->bind_param("s", $email);
            if($stmt->execute()) {
                $userResult = $stmt->get_result();
            }
            else {
                echo "Error: " . $sql . "<br>" . $conn->error;
                $errorOccurred = 1;
            }
            $stmt -> close();
        
            // Checking if text box is blank
            if ($email=="") {
                echo "Email is blank! <br>";
                $errorOccurred = 1;
            }
            // Checking for HTML tags to prevent XSS
            else if ($email != strip_tags($email)) {
                echo "Email contains HTML tags ('<','>'). Please remove!<br>";
                $errorOccurred = 1;
            }
            else {
                // Check if email exists in the database
                if ($userResult -> num_rows > 0) {
                    while ($userRow = $userResult -> fetch_assoc()) {
                        $userName = $userRow['UserName'];
                    }
                    //Construct token and ts
                    $token = random_bytes(35);
                    $ts = bin2hex(random_bytes(8));
        
                    // URL to be sent to user's email address
                    $url = "https://lovejoyapplication.000webhostapp.com/newPass/newPassForm.php?token=".bin2hex($token)."&ts=".$ts;
        
                    // Link expires after an 30 minutes
                    $expiry = time() + 900; 
                    
                    //Delete previous token and ts from database
                    $stmt = $conn->prepare("DELETE FROM ResetPassword WHERE ResetEmail = ?");
                    $stmt->bind_param("s", $email);
                    if (!$stmt->execute()) {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    } 
                    $stmt -> close();
        
                    //Insert new token and ts
                    $stmt = $conn->prepare("INSERT INTO ResetPassword (ResetEmail, ResetToken, ResetTS, ResetExpiry) VALUES (?, ?, ?, ?)");
        
                    // Encrypt token to ensure confidentiality 
                    if (defined('PASSWORD_ARGON2I')) {
                        $tokenHash = password_hash($token, PASSWORD_ARGON2I);
                    }
                    else {
                        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                    }
                    
                    $stmt->bind_param("ssss", $email, $tokenHash, $ts, $expiry);
        
                    if (!$stmt->execute()) {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    } 
        
                    $stmt -> close();
        
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
                    $mail->msgHTML("Hello " . $userName . ", we got a request to reset your Lovejoy password: " .$url); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
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
                    echo "Email does not exist.<br>";
                    $errorOccurred = 1;
                }
            }
        }
        else {
            echo "Failed to authenticate token<br>";
            $errorOccurred = 1;
        }
    }
    else {
        echo "Submit button not pressed<br>";
        $errorOccurred = 1;
    }
    if ($errorOccurred == 0) {  
        echo "Instructions have been sent to your email address<br>";
    }
    else {
        echo "Email was not sent<br>";
    }
    require "../home.php";
    echo "</pre>";
?>