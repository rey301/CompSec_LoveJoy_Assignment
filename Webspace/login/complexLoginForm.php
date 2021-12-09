<?php
    echo "<form action='/login/complexLoginAuth.php' method='POST'>";
    echo "<pre>";
    echo "<h1>Login</h1>";
    echo "Username  ";
    echo "<input name='txtUserName' type='text' /> <br/>";
    echo "Password  ";
    echo "<input name='txtPassword' type='password'/><br><br>";
    echo "<input type='hidden' name='token' value=".$token.">";
    echo "<input name='submit' type='submit' value='Submit'><br/><br/>";
    echo "<a href='/forgotPass/forgotPassForm.php'>Forgot your password?</a><br/>";
    echo "<a href='/register/registerForm.php'>Sign up</a>";
    echo "</pre>";
    echo "</form>";
?>
