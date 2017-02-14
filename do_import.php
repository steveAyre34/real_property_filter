<?php
require("connection.php");
require("common.php");
require("class.php");

session_start();

//File name will not have county name included
//Prepend county name based on value chosen from dropdown menu
$databaseTable = $_POST['county'] . '_' . $_FILES['uploadFile']['name'];

//Open file to be uploaded
$importFile = fopen($_FILES['uploadFile']['name'], "r") or die("Unable to open file.");

//Removes file extension from filename to give table name 
$databaseTable = substr($databaseTable, 0, -4);

$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);

//Get headers (in order) from specified table in the database
$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);


//Retrieves header layout from first line of file to be uploaded
$classHeaders = getClassFileHeaders($_FILES['uploadFile']['name'], $databaseTableHeaders);

//First line of file contains header layout, which we have already retrieved
//Therefore the first line of the file can be "thrown away"
$throwawayFirstLine = fgets($importFile);

$uploadCount = 0;
$errorCount = 0;
//This loop will read the file line by line until the end is reached
while(!feof($importFile)) {
	//Read one line
	$fileLine = fgets($importFile);
	
	//Fields are tab-delimited, so this separates each field into its own array index
	$fileLine = explode("\t", $fileLine);
	
	//Begin creating the insert statement
	//Specify table to which data will be inserted
	$insertStatement = "INSERT INTO " . $databaseTable . " (";
	
	//Now append the headers to the insert statement, in the order they were retrieved from the file
	//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
	foreach($classHeaders as $c) {
		$append = "\'";
		$append .= $c . "'" . ", ";
		$insertStatement .= $append;
	}
	
	//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
	$insertStatement = substr($insertStatement, 0, -2);
	
	//Now we specify the values to be inserted
	$insertStatement .= ") VALUES (";
	foreach($fileLine as $f) {
		$insertStatement .= "'" . $f . "'" . ", ";
	}
	
	//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
	$insertStatement = substr($insertStatement, 0, -2);
	
	//Appends ending parentheses
	//MySQL commands must end in a semicolon, so this appends a semicolon
	$insertStatement .= ");";
	
	
	if($conn->query($insertStatement) == TRUE) 
		++$uploadCount;
	else {
		echo "Error: " . $insertStatement . "<br>" . $conn->error;
		++$errorCount;
	}
}

print $uploadCount . " records added successfully.<br>";
print $errorCount . " records not added successfully.<br>";
?>
