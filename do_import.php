<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	</head>
	
</html>
<?php
/**
	This is the server side of a file importer that can be used for the Real Property or Board of Elections File Uploads.
	NOTE: file headers don't need to be in the same ORDER as the database headers, however every file header must have a corresponding column in 
		their designated table.
		This importer checks if this is the case.
*/
require("connection.php");
//require("createHeaders.php");
//require("import.php");




session_start();

$successMessage = "";
$owner = false;
$file = array();
array_push($file, $_POST['filename']);

if(empty($file)) {
	echo '<script type="text/javascript"> alert("No more files!"); </script>';
}
else {
foreach($file as $filename) {
	//if(strcmp($v, 'owner.txt') != 0) {
		//File name will not have county name included
		//Prepend county name based on value chosen from dropdown menu
		$databaseTable = $_POST['databaseTable'];
		$headers = json_decode($_POST['headers']);
		/*foreach($_POST['headers'] as $h) {
			array_push($headers, $h);
		}*/

		/*echo '<script type="text/javascript">
			console.log("From do_import");
			</script>';

		foreach($headers as $h) {
			echo '<script type="text/javascript>
				console.log("'. $h->value .'");
				</script>';
		}*/
		//parse_str($_POST['headers'], $headers);					
		/*****************
			Move files into 'data' directory within application 
		*****************/
		/*$upload_dir = 'data/' . ucfirst($_POST['county']) . '/';
		copy($v, $upload_dir . $v);
		*/
		//Open file to be uploaded ('countyName_fileName.txt')
		$importFile = fopen($filename, "r") or die("Unable to open file.");

		//Removes file extension from filename to give table name  
		//$databaseTable = substr($filename, 0, -4);

		//Connect to database, clear old data and then perform SELECT * so we can get table headers later
		$getDatabaseTable = mysqli_query($conn, "DELETE FROM " . $databaseTable);
								
		//Create the LOAD DATA LOCAL INFILE statement
		$insertStatement = "LOAD DATA LOCAL INFILE '" . $filename . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES (";
					
		//Now append the headers to the LOAD DATA LOCAL INFILE statement, in the order they were retrieved from the file
		//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
		foreach($headers as $h) {
			$insertStatement .= $h->value . ', ';
			//echo '<script type="text/javascript"> console.log("' . $h  . '"); </script>';
		}
					
		//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		$insertStatement = substr($insertStatement, 0, -2);
			
		//Now we specify the values to be inserted
		$insertStatement .= ");"; 
		/*echo '<script type="text/javascript">
			console.log("INSERT STATEMENT: "' . $insertStatement . '");
			</script>';*/								
		//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		//$insertStatement = substr($insertStatement, 0, -2);
	
		/*$failedCount = mysqli_query($conn, $insertStatement);
		if($failedCount == true) {
			echo '<script type="text/javascript">
				console.log("SUPPOSEDLY TRUE INSERT");
				</script>';
		}
		else if ($failedCount == false) {
			echo '<script type="text/javascript">
				console.log("' . $mysqli->error . '");
				</script>';
		}*/

		if(!mysqli_query($conn, $insertStatement)) {
			echo 'script type="text/javascript">
				console.log("' . mysqli_error($conn) . '");
				</script>';
		}
		else {
			echo '<script type="text/javascript">
				console.log("SUPPOSEDLY TRUE INSERT");
				</script>';
		}	
		/*$checkUpload = "SELECT COUNT(*) FROM " . $databaseTable;
		$uploadCount = mysqli_query($conn, $checkUpload) or die(mysqli_error());
		$uploadCounter = mysqli_fetch_assoc($uploadCount);
		if($failedCount == true) {
			$successMessage .= $databaseTable . " has uploaded " . $uploadCounter['COUNT(*)'] . " records successfully! ";
		}*/
//	}
	/*else {
		$owner = true;
	}*/
//}
		//$successMessage = json_encode($successMessage);
		/*echo '<script type="text/javascript">
				history.back(alert ("' . $successMessage . '"));
					</script>';*/
/*if($owner == false) {
	echo '<script type="text/javascript">
			history.back(alert("Upload Finished!"));
		</script>';
} */
//else {

	/*ob_start();
	include "createHeaders.php";
	$createHeaders = ob_get_clean();
	print $createHeaders;(*/
}}
?>

