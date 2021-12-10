<?php
    session_start();
    
    require "../csrfToken.php";

    echo "<pre>";
    echo "<form action='/register/registerCheck.php' method='POST'>";
    echo "<h1>Sign up</h1>";
    echo "Email";
    echo "               <input name='txtEmail1' type='text' /><br/>";
    echo "Confirm Email";
    echo "       <input name='txtEmail2' type='text' /><br/>";
    echo "Password";
    echo "            <input name='txtPassword1' type='password' /><br/>";
    echo "Confirm Password";
    echo "    <input name='txtPassword2' type='password' /><br/>";
    echo "Username";
    echo "            <input name='txtUserName' type='text' /><br/>";
    echo "Telephone Number";
    echo "    <input name='txtPhoneNumber' type='text' /><br/>";
    echo "<input type='hidden' name='token' value=".$token."><br>";
    echo "<input name='submit' type='submit' value='Register'><br><br>";
        
    require "../home.php";
    echo "</pre>";
    echo "</form>";
?>



























