<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Database connection information
    $mysql_host="krier.uscs.susx.ac.uk";
    $mysql_database="G6077_ar629"; // name of the database, it is empty for now
    $mysql_user="ar629"; // type your username
    $mysql_password="Mysql_492467"; // type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.

    // Connect to the server
    $conn = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");

    // Copy all of the data from the form into variables
    $recoveryEmail = $_POST['txtRecoveryEmail'];
    $name = '';
    // Create a variable to indicate if email has been found or not
    $emailFound = False;

    // Retrieve the table SystemUser
    $userResult = $conn -> query("SELECT * FROM SystemUser");

    // Make sure that all text boxes were not blank.
    if ($recoveryEmail=="") {
        echo "Email is blank! <br/>";
    }
    else if ($recoveryEmail != strip_tags($recoveryEmail)) {
        echo "Email contains HTML tags ('<','>'). Please remove!</br>";
    }
    else {
        // Check if email already exists in the database
        // Loop from the first to the last record
        while ($userRow = mysqli_fetch_array($userResult)) {
        // Check to see if the current user's email matches the one in the database 
            if ($userRow['Email'] == $recoveryEmail) {
                $name == $userRow['Name'];
                $emailFound = True;
            }
        }
    }

    if ($emailFound) {
        //Send instructions
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
        $mail->addAddress($recoveryEmail, $name); // to email and name
        $mail->Subject = 'PHPMailer GMail SMTP test';
        $mail->msgHTML("test body"); //$mail->msgHTML(file_get_contents('contents.html'), __DIR__); //Read an HTML message body from an external file, convert referenced images to embedded,
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