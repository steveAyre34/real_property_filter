<?php

$servername = "localhost";
$username = "root";
$password = "SUNYFinaidOffice10940";
$dbname = "rp2_database";




//Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

?>
