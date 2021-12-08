<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'sqlConn.php';

    // Copy all of the data from the form into variables
    $email = $_POST['txtEmail'];
    $name = '';
    // Create a variable to indicate if email has been found or not
    $emailFound = False;

    // Retrieve the table SystemUser
    $userResult = $conn -> query("SELECT * FROM SystemUser");

    // Make sure that all text boxes were not blank.
    if ($email=="") {
        echo "Email is blank! <br/>";
    }
    else if ($email != strip_tags($email)) {
        echo "Email contains HTML tags ('<','>'). Please remove!</br>";
    }
    else {
        // Check if email already exists in the database
        // Loop from the first to the last record
        while ($userRow = mysqli_fetch_array($userResult)) {
        // Check to see if the current user's email matches the one in the database 
            if ($userRow['Email'] == $email) {
                $name == $userRow['Name'];
                $emailFound = True;
            }
        }
    }

    if ($emailFound) {
        //Construct token
        $token = random_bytes(35);
        $ts = bin2hex(random_bytes(8));

        $url = "https://lovejoyapplication.000webhostapp.com/newPasswordForm.php?token=".bin2hex($token)."&ts=".$ts;

        $expiry = time() + 1800; // Link expires after an hour
        
        //Delete from database, the previous tokens and ts
        $sql = "DELETE FROM ResetPassword WHERE resetEmail=?";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            echo "Successfully deleted<br/>";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        //Delete from database, the previous tokens and ts
        $sql = "INSERT INTO  ResetPassword (resetEmail, resetToken, resetTS, resetExpiry) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (defined('PASSWORD_ARGON2I')) {
            $tokenHash = password_hash($token, PASSWORD_ARGON2I);
        }
        else {
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);
        }
        
        $stmt->bind_param("ssss", $email, $tokenHash, $ts, $expiry);

        if ($stmt->execute()) {
            echo "Successfully inserted<br/>";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

        $stmt -> close();

        //Sending email
        require $_SERVER['DOCUMENT_ROOT'] . '/mail/Exception.php';
        require $_SERVER['DOCUMENT_ROOT'] . '/mail/PHPMailer.php';
        require $_SERVER['DOCUMENT_ROOT'] . '/mail/SMTP.php';

        $mail = new PHPMailer;
        $mail->isSMTP(); 
        $mail->SMTPDebug = 2; // 0 = off (for production use) - 1 = client messages - 2 = client and server messages
        $mail->Host = "smtp.gmail.com"; // use $mail->Host = gethostbyname('smtp.gmail.com'); // if your network does not support SMTP over IPv6
        $mail->Port = 587; // TLS only
        $mail->SMTPSecure = 'tls'; // ssl is deprecated
        $mail->SMTPAuth = true;
        $mail->Username = 'lovejoy5431@gmail.com'; // email
        $mail->Password = 'david504'; // password
        $mail->setFrom('david@lovejoy.com', 'LoveJoy'); // From email and name
        $mail->addAddress($email, $name); // to email and name
        $mail->Subject = 'PHPMailer GMail SMTP test';
        $mail->msgHTML($url); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
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
        } else {
            echo "Message sent!";
        }
    }
    else {
        if (!$emailFound) {
            echo "Email doesn't exist!<br/>";
        }
        echo "Email was not sent.";
    }
?>