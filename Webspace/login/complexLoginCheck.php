<?php
  session_start();

  require '../sqlConn.php';

  $errorOccurred = 0;
  
  //$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

  // if (!$token || $token !== $_SESSION['token']) {
  //   // show an error message
  //   echo '<p class="error">Error: invalid form submission</p>';
  //   // return 405 http status code
  //   header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
  //   exit;
  // }

  $userName = $_POST['txtUserName'];
  $password = $_POST['txtPassword'];

  // Find the user data
  $stmt = $conn->prepare("SELECT * FROM SystemUser WHERE UserName = ?");
  $stmt->bind_param("s", $userName);
  $stmt->execute();
  $userResult = $stmt->get_result();
  $stmt->close();

  echo "<pre>";
  if ($userResult -> num_rows > 0) {
    while ($userRow = $userResult -> fetch_assoc()) {
      if (password_verify($password, $userRow['UserPassword'])) {
        //Save user's id to session variable
        if (isset($_SESSION['userID'])) {
          unset($_SESSION['userID']);
        }
        $_SESSION['userID'] = $userRow['UserID'];

        if ($userRow['UserAdmin'] == 1) {
          echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
          echo "<h2>Administrator page</h2>";
          echo "<a href='/viewRequests/viewRequestsForm.php'>View requests</a><br>";
        } 
        else {
          echo "<h1>Hello " . htmlspecialchars($userName) . "</h1>";
          echo "<h2>Welcome to Lovejoy!</h2>";
          echo "<a href='/requestEvaluation/requestEvaluationForm.php'>Request evlauation</a><br>";
        }
        
      }
      else {
        echo "Wrong Password<br>";
        $errorOccurred = 1;
      }
    }
  }
  else {
    echo "User does not exist<br>";
    $errorOccurred = 1;
  }

  if ($errorOccurred == 1) {
    echo "Login unsuccessful<br>";
  }

  echo "</pre>";
?>
