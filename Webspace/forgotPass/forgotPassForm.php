<?php
    session_start();
    // Generate new token
    require "../csrfToken.php";

    echo "<form action='/forgotPass/forgotPassAuth.php' method='POST'>";
    echo "<pre>";
    echo "<h1>Reset your password</h1>";
    echo "<h2>Instructions will be sent to your email address</h2>";
    echo "Email ";
    echo "<input name='txtEmail' type='text' /> <br/>";
    echo "<input type='hidden' name='token' value=".$token."><br>";
    echo "<input name='submit' type='submit' value='Submit'><br><br>";
    require "../home.php";
    echo "</pre>";
    ;
    echo "</form>";
?>