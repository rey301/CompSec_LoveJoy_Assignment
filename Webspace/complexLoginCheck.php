<?php
  // Server and db connection
  $mysql_host="krier.uscs.susx.ac.uk";
  // qW2bp4hG5&31v6jVOeTd
  $mysql_database="G6077_ar629";    // name of the database, it is empty for now
  $mysql_user="ar629";    // type your username
  $mysql_password="Mysql_492467";  //  type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.

  // Connect to the server
  $conn = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");

  $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

  if (!$token || $token !== $_SESSION['token']) {
    // show an error message
    echo '<p class="error">Error: invalid form submission</p>';
    // return 405 http status code
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
    exit;
  }

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
