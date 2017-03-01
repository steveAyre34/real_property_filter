<?php
/**
	This is the server side of a file importer that can be used for the Real Property or Board of Elections File Uploads.
	NOTE: file headers don't need to be in the same ORDER as the database headers, however every file header must have a corresponding column in 
		their designated table.
		This importer checks if this is the case.
*/
require("connection.php");
//require("createHeaders.php");




session_start();

$successMessage = "";
foreach($_FILES['uploadFile']['name'] as $k => $v) {
		//File name will not have county name included
		//Prepend county name based on value chosen from dropdown menu
		$databaseTable = $_POST['county'] . '_' . $v;
						
		/*****************
			Move files into 'data' directory within application 
		*****************/
		$upload_dir = 'data/' . ucfirst($_POST['county']) . '/';
		copy($v, $upload_dir . $v);
		
		//Open file to be uploaded ('countyName_fileName.txt')
		$importFile = fopen($upload_dir . $v, "r") or die("Unable to open file.");

		//Removes file extension from filename to give table name  
		$databaseTable = substr($databaseTable, 0, -4);

		//Connect to database, clear old data and then perform SELECT * so we can get table headers later
		$getDatabaseTable = mysqli_query($conn, "DELETE FROM " . $databaseTable);
		
		//Retrieves header layout from first line of file to be uploaded (each field is delimited with a tab)
		$fileHeaders = fgets($importFile);
		$fileHeaders = explode("\t", $fileHeaders);
		
		$headers = array();
		
		//Trim whitespace from header names (was causing a mismatch on last header due to whitespace)
		foreach($fileHeaders as $k=>$vHeader) {
			array_push($headers, trim($vHeader));			
		}
								
		//Create the LOAD DATA LOCAL INFILE statement
		$insertStatement = "LOAD DATA LOCAL INFILE '" . $v . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES (";
					
		//Now append the headers to the LOAD DATA LOCAL INFILE statement, in the order they were retrieved from the file
		//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
		foreach($headers as $h) {
			$insertStatement .= $h . ", ";
		}
					
		//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		$insertStatement = substr($insertStatement, 0, -2);
			
		//Now we specify the values to be inserted
		$insertStatement .= ");"; 
									
		/*//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		$insertStatement = substr($insertStatement, 0, -2);*/

		
		$failedCount = mysqli_query($conn, $insertStatement);
		$checkUpload = "SELECT COUNT(*) FROM " . $databaseTable;
		$uploadCount = mysqli_query($conn, $checkUpload) or die(mysqli_error());
		$uploadCounter = mysqli_fetch_assoc($uploadCount);
		if($failedCount == true) {
			$successMessage .= $databaseTable . " has uploaded " . $uploadCounter['COUNT(*)'] . " records successfully! ";
		}
}
		//$successMessage = json_encode($successMessage);
		echo '<script type="text/javascript"> alert ("' . $successMessage . '");
					window.history.back();
					</script>';	
?>
