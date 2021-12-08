<?php
    // Database connection information
    $mysql_host="krier.uscs.susx.ac.uk";
    $mysql_database="G6077_ar629"; // name of the database, it is empty for now
    $mysql_user="ar629"; // type your username
    $mysql_password="Mysql_492467"; // type the password, it is Mysql_<Personcod> You will need to replace person code with number from your ID card.

    // Connect to the server
    $conn = new mysqli($mysql_host, $mysql_user,$mysql_password, $mysql_database) or die ("could not connect to the server");
?>