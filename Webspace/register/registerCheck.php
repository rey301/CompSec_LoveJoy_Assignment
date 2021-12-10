<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    session_start();

    $errorOccurred = 0;
    $authFailed = 0;

    echo "<pre>";
    if (isset($_POST['submit'])) {
        if (hash_equals($_SESSION['token'], $_POST['token'])) {
            require '../sqlConn.php';

            $email1 = $_POST['txtEmail1'];
            $email2 = $_POST['txtEmail2'];
            $password1 = $_POST['txtPassword1'];
            $password2 = $_POST['txtPassword2'];
            $userName = $_POST['txtUserName'];
            $phoneNumber = $_POST['txtPhoneNumber'];

            // Retrieve the table SystemUser
            $userResult = $conn -> query("SELECT * FROM SystemUser");

            // Email checking
            if ($email1=="" OR $email2=="") {
                echo "Email is blank! <br/>";
                $errorOccurred = 1;
            }
            else if ($email1 != strip_tags($email1)) {
                echo "Email contains HTML tags ('<','>'). Please remove!</br>";
                $errorOccurred = 1;
            }
            else if (strpos ($email1, "@") == false OR strpos($email2,"@") == false) {
                echo "Email is not valid! Must contain '@'. <br/>";
                $errorOccurred = 1;
            }
            // Check to make sure that emails match
            else if(strcmp($email1, $email2) != 0) { 
                echo "Emails do not match! <br/>";
                $errorOccurred = 1;
            }
            else {
                // Check if email already exists in the database
                while ($userRow = mysqli_fetch_array($userResult)) {
                if ($userRow['UserEmail'] == $email1) {
                echo "This email address has already been used! <br/>";
                $errorOccurred = 1;
                }
                }
            }

            // Check if password passes our policies
            require '../passwordPolicies.php';

            // Checking if rest of the form is blank
            if ($userName=="") {
                echo "Username is blank! <br/>";
                $errorOccurred = 1;
            }
            else {
                // Check if username already exists in the database
                while ($userRow = mysqli_fetch_array($userResult)) {
                if ($userRow['UserName'] == $userName) {
                    echo "Username has already been used. <br/>";
                    $errorOccurred = 1;
                }
                }
            }

            if ($phoneNumber=="") {
                echo "Telephone Number is blank! <br/>";
                $errorOccurred = 1;
            }

            //HTML tag checking (making sure no tags are used within the text fields)
            if ($userName != strip_tags($userName)) {
                echo "Username contains HTML tags! Please remove!</br>";
                $errorOccurred = 1;
            }

            if ($phoneNumber != strip_tags($phoneNumber)) {
                echo "Phone number contains HTML tags! Please remove!</br>";
                $errorOccurred = 1;
            }

            // Check to see if an error has occurred, if so add contents to the database
            if ($errorOccurred == 0) {
                // Add all of the contents of the variables to the SystemUser table
                if (defined('PASSWORD_ARGON2I')) {
                $passwordHash = password_hash($password1, PASSWORD_ARGON2I);
                }
                else {
                $passwordHash = password_hash($password1, PASSWORD_DEFAULT);
                }
                
                $stmt = $conn->prepare("INSERT INTO SystemUser (UserEmail, UserPassword, UserName, UserNumber) 
                VALUES (?, ?, ?, ?)");

                // Bind parameters to the query
                $stmt->bind_param("ssss", $email1, $passwordHash, $userName, $phoneNumber);
                
                if ($stmt->execute()) {
                    // Thank the new user for joining
                    echo "Hello " . htmlspecialchars($userName)."<br>";
                    echo "Thank you for joining the Computing Security network<br>";
                    
                    //Construct token and ts
                    $token = random_bytes(35);
                    $ts = bin2hex(random_bytes(8));

                    // URL to be sent to user's email address
                    $url = "https://lovejoyapplication.000webhostapp.com/userVerify/userVerifyCheck.php?token=".bin2hex($token)."&ts=".$ts;

                    // Update previous token and ts from database
                    // Encrypt token to ensure confidentiality 
                    if (defined('PASSWORD_ARGON2I')) {
                        $tokenHash = password_hash($token, PASSWORD_ARGON2I);
                    }
                    else {
                        $tokenHash = password_hash($token, PASSWORD_DEFAULT);
                    }
                    $stmt = $conn->prepare("UPDATE SystemUser SET UserToken=?, UserTS=? WHERE UserEmail = ?");
                    $stmt->bind_param("sss", $tokenHash,$ts,$email1);
                    if (!$stmt->execute()) {
                        echo "Error: " . $conn->error;
                    } 

                    // Generate message 
                    $msgHTML = "Hello " . $userName . ", please verify your account: " .$url;

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
                    $mail->addAddress($email1, $userName); // to email and name
                    $mail->Subject = 'Verify your LoveJoy account';
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
                    echo "Please verify your account by clicking on the link we sent via email<br>";
                } 
                else {
                    echo "Error: " . $conn->error;
                    $errorOccurred = 1;
                }

                $stmt -> close();
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
        echo "User could not be registered<br>";
    }

    if ($authFailed == 0) {
        require "../csrfToken.php";
        echo "<form action='/register/registerForm.php' method='POST'>";
        echo "<input name='submit' type='submit' value='Go back'>";
        echo "<input type='hidden' name='token' value=".$token.">";
        echo "</form>";
    }

    require "../home.php";
    echo "</pre>";
?>