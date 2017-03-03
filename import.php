<?php
	//require("createHeaders.php");
	require("do_import.php");
	$tableData = $_POST[""]
	$tableData = json_decode($tableData, TRUE);
	$county = $_POST['county'];
	$fileName = $_POST['fileName'];
	
		//Prepend county name based on value chosen from dropdown menu
		$databaseTable = $county . '_' . $fileName;
						
		/*****************
			Move files into 'data' directory within application 
		*****************/
		$upload_dir = 'data/' . ucfirst($county) . '/';
		copy($fileName, $upload_dir . $fileName);
		
		//Open file to be uploaded ('countyName_fileName.txt')
		$importFile = fopen($upload_dir . $fileName, "r") or die("Unable to open file.");

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
		$insertStatement = "LOAD DATA LOCAL INFILE '" . $fileName . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES (";
					
		//Now append the headers to the LOAD DATA LOCAL INFILE statement, in the order they were retrieved from the file
		//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
		foreach($headers as $h) {
			$insertStatement .= $h . ", ";
		}
					
		//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		$insertStatement = substr($insertStatement, 0, -2);
			
		//Now we specify the values to be inserted
		$insertStatement .= ");"; 
		
		$failedCount = mysqli_query($conn, $insertStatement);
?>