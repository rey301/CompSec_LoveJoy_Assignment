<?php
  session_start();
  require '../sqlConn.php';

  //$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

  // if (!$token || $token !== $_SESSION['token']) {
  //   // show an error message
  //   echo '<p class="error">Error: invalid form submission</p>';
  //   // return 405 http status code
  //   header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
  //   exit;
  // }

  // Values come from user, through webform
  $userName = $_POST['txtUserName'];
  $password = $_POST['txtPassword'];

  // Query
  $userResult = $conn->query("SELECT * FROM SystemUser");

  // Flag variable
  $userFound = 0;

  if ($userResult -> num_rows > 0) {
    while ($userRow = $userResult -> fetch_assoc()) {
      if ($userRow['UserName'] == $userName) {
        $userFound = 1;
        if (password_verify($password, $userRow['UserPassword'])) {
          //Save user's id to session variable
          if (isset($_SESSION['userID'])) {
            unset($_SESSION['userID']);
          }
          $_SESSION['userID'] = $userRow['UserID'];

          if ($userRow['UserAdmin'] == 1) {
            echo "<pre>";
            echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
            echo "<h2>Administrator page</h2>";
            echo "<a href='/viewRequests/viewRequestsForm.php'>View requests</a><br>";
            echo "</pre>";
          } 
          else {
            echo "<pre>";
            echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
            echo "<h2>Welcome to Lovejoy!</h2>";
            echo "<a href='/requestEvaluation/requestEvaluationForm.php'>Request evlauation</a><br>";
            echo "</pre>";
          }
          
        }
        else {
          echo "Wrong Password";
        }
      }
    }
  }

  if ($userFound == 0) {
    echo "This user was not found in our database";
  }
?>
