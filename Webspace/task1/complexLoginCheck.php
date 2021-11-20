<?php
// Server and db connection
 $mysql_host="krier.uscs.susx.ac.uk";
 // qW2bp4hG5&31v6jVOeTd
 $mysql_database="G6077_ar629";    // name of the database, it is empty for now
 $mysql_user="ar629";    // type your username
 $mysql_password="Mysql_492467";  //  type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.
 // 


// connect to the server
$conn = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");

// values come from user, through webform
 $username =$_POST['txtUsername'];
 $password = $_POST['txtPassword'];

 // query
 $userQuery = "SELECT * FROM SystemUser";
 $userResult = $conn->query($userQuery);

 // flag variable
 $userFound = 0;

 echo "<table border='1'>";
  if ($userResult -> num_rows > 0)
  {
    while ($userRow = $userResult -> fetch_assoc())
    {
      if ($userRow['Username'] == $username)
      {
        $userFound = 1;
	  if ($userRow['Password'] == $password)
	  {
	    echo "Hi" .$username . "!";
	    echo "<br/> Welcome to our website";
	  }
	  else
	  {
	    echo "Wrong Password";
	  }
      }
    }
  }
  echo "</table>";

  if ($userFound == 0)
  {
   echo "This user was not found in our database";
  }

?>
