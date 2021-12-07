<?php
    echo "<form action='complexLoginCheck.php' method='POST'>";
    echo "<pre>";
    echo "<h1>Login</h1>";
    echo "Username  ";
    echo "<input name='txtName' type='text' /> <br/>";
    echo "Password  ";
    echo "<input name='txtPassword' type='password'/><br/><br/>";
    echo "<input type='submit' value='Submit'><br/><br/>";
    echo "<a href='forgotPassForm.php'>Forgot your password?</a><br/>";
    echo "<a href='registerForm.php'>Sign up</a>";
    echo "</pre>";
    echo "</form>";
?>
