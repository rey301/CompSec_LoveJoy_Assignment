<?php
    session_start();

    require "../csrfToken.php";

    echo "<form action='/forgotPass/forgotPassCheck.php' method='POST'>";
    echo "<pre>";
    echo "<h1>Reset your password</h1>";
    echo "<h2>Instructions will be sent to your email address</h2>";
    echo "Email ";
    echo "<input name='txtEmail' type='text' /><br><br>";
    echo "<input type='hidden' name='token' value=".$token.">";
    echo "<input name='submit' type='submit' value='Submit'><br>";
    require "../home.php";
    echo "</pre>";
    echo "</form>";
?>