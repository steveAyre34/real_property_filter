<?php
require("connection.php");
require("common.php");

session_start();

$table = $_POST['county'] . '_' . $_FILES['uploadFile']['name'];
$result = mysqli_query($conn, "SELECT * FROM" . $table);
$fields = $result->fetch_fields();

$columns = array();
foreach($fields as $f) {
	array_push($columns, $f->name);
}



?>
