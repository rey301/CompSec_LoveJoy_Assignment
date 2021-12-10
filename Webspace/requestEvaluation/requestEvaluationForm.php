<?php
    session_start();

    $errorOccurred = 0; 
    
    echo "<pre>";
    if (isset($_POST['submit'])) {
      if (hash_equals($_SESSION['token'], $_POST['token'])) {
        echo "<form action='/requestEvaluation/requestEvaluationCheck.php' method='POST' 
        enctype=multipart/form-data>";

        // Generate new token
        require "../csrfToken.php";
        echo "<input type='hidden' name='token' value=".$token.">";

        echo "<h1>Request Evaluation</h1>";
        echo "Description     ";
        echo "<input name='txtDescription' type='text' /> <br>";
        echo "Request         ";
        echo "<input name='txtRequest' type='text'/><br>";
        echo "<label for='contactMethod'>Preferred method of contact </label>";
        echo "<select name='contactMethod' id='contactMethod'>
                <option value='telephone'>Telephone</option>
                <option value='email'>Email</option>
              </select><br>";
        echo "Image           ";
        echo "<input name='fileImage' type='file'><br><br>";
        echo "<input name='submit' type='submit' value='Submit'><br>";
        
        
        echo "</form>";


      }
      else {
        echo "Failed to authenticate token<br>";
      }
    }
    else {
      echo "Failed to authenticate user<br>";
    }
    require "../home.php";
    echo "</pre>";
    
?>