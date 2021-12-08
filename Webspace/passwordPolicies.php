<?php
    //Password policies
    //Policy 1: Password must not be null/empty
    if ($password1=="" OR $password2=="") {
        echo "Password is blank! <br/>";
        $errorOccurred = 1;
    }
    //Policy 2: Passwords must match
    else if (strcmp($password1, $password2) != 0) {
        echo "Passwords do not match! <br/>";
        $errorOccurred = 1; 
    }
    //Policy 3: Password must be between 8-16 characters
    else if (strlen($password1) < 8 OR strlen($password1) > 16) {
        echo "Password must be between 8-16 characters.<br/>";
        $errorOccurred = 1;
    }
    //Policy 4: Password must contain both upper and lower case letters
    else if (!preg_match("/[A-Z]/", $password1) == 1) {
        echo "Password must contain upper case letters.</br>";
        $errorOccurred = 1;
    }
    else if (!preg_match("/[a-z]/", $password1) == 1) {
        echo "Password must contain lower case letters.</br>";
        $errorOccurred = 1;
    }
    //Policy 5: Password must contain at least one punctuation (excluding tags <>)
    else if (!preg_match("/[[:punct:]]/",$password1)) {
        echo "Password must contain at least one punctuation (excluding tags <>).</br>";
        $errorOccurred = 1;
    }
    //Policy 6: Password must not contain any HTML tags to prevent XSS
    else if ($password1 != strip_tags($password1)) {
        echo "Password contains HTML tags ('<','>'). Please remove!</br>";
        $errorOccurred = 1;
    }
    //Policy 7: Password must contain at least one number
    else if (!preg_match("/[0-9]/",$password1)) {
        echo "Password must contain at least one number.</br>";
        $errorOccurred = 1;
    }
?>