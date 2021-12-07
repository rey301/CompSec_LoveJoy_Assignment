<?php
    echo "<form action='complexLoginCheck.php' method='POST'>";
    echo "<pre>";
    echo "Username  ";
    echo "<input name='txtName' type='text' /> <br/>";
    echo "Password  ";
    echo "<input name='txtPassword' type='password'/><br/><br/>";
    echo "<input type='submit' value='Login'><br/><br/>";
    echo "Forgot password? Click <a href='forgotPassForm.php'> HERE </a><br/>";
    echo "Not registered yet? Click <a href='registerForm.php'> HERE </a>";
    echo "</pre>";
    echo "</form>";
?>
