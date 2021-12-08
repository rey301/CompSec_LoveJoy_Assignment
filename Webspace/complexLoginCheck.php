<?php
  require 'sqlConn.php';

  //$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

  // if (!$token || $token !== $_SESSION['token']) {
  //   // show an error message
  //   echo '<p class="error">Error: invalid form submission</p>';
  //   // return 405 http status code
  //   header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
  //   exit;
  // }

  // Values come from user, through webform
  $name = $_POST['txtName'];
  $password = $_POST['txtPassword'];

  // Query
  $userQuery = "SELECT * FROM SystemUser";
  $userResult = $conn->query($userQuery);

  // Flag variable
  $userFound = 0;

  echo "<table border='1'>";
  if ($userResult -> num_rows > 0) {
    while ($userRow = $userResult -> fetch_assoc()) {
      if ($userRow['Name'] == $name) {
        $userFound = 1;
        if (password_verify($password, $userRow['Password'])) {
          echo "Hi " . htmlspecialchars($name) . "!<br/>";
          echo "Welcome to our website!";
        }
        else {
          echo "Wrong Password";
        }
      }
    }
  }
  echo "</table>";

  if ($userFound == 0) {
    echo "This user was not found in our database";
  }
?>
