<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require "../csrfToken.php";
    
    // Generate pin 
    $num = random_int(0,99999);

    $userEmail = $_SESSION['userEmail'];
    if (isset($_SESSION['userEmail'])) {
        unset($_SESSION['userEmail']);
    }

    if ($num > 10000) {
        $pin = $num;
    }
    else if ($num > 999) {
        $pin = '0'.$num;
    }
    else if ($num > 99) {
        $pin = '00'.$num;
    }
    else if ($num > 9) {
        $pin = '000'.$num;
    }
    else if ($num < 9) {
        $pin = '0000'.$num;
    }

    if (isset($_SESSION['pin'])) {
        unset($_SESSION['pin']);
    }
    $_SESSION['pin'] = $pin;

    // Generate message 
    $msgHTML = "Hello " . $userName . ", here is the code you need to access your account: " .$pin;

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
    $mail->addAddress($userEmail, $userName); // to email and name
    $mail->Subject = '2 Factor Authentication for your LoveJoy account';
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

    echo "<pre>";
    echo "<form action='/2fa/2faCheck.php' method='POST'>";
    if ($resetPin == 1) {
        echo "<h1>Please enter the pin we sent to your email</h1>";
    }
    else {
        echo "<h1>Please re-enter the pin we sent to your email</h1>";
    }
    echo "<input name='txtPin' type='text' maxlength='5' minLength='5'/><br><br>";
    echo "<input name='submit' type='submit' value='Submit'><br/>";
    echo "<input type='hidden' name='token' value=".$token."><br>";
    echo "</form>";

    echo "</pre>";
?>