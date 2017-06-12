<?php

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "rp2_database";




//Create connection
$link = new mysqli($servername, $username, $password, $dbname);


if($link->connect_error) {
	die("Connection failed: " . $link->connect_error);
}

?>
