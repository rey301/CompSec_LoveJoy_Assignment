<?php
    echo "<form action='/requestEvaluation/requestEvaluationCheck.php' method='POST' 
    enctype=multipart/form-data>";
    echo "<pre>";
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
    echo "<input type='submit' value='Submit'>";
    echo "</pre>";
    echo "</form>";
?>