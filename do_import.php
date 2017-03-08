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
//header('Content-Type: text/event-stream');
//require("createHeaders.php");
//require("import.php");

session_start();
//mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5000);
$totalSize = 0;
$currentlyUploadedSize = 0;

function createTable($fileHeaders, $databaseTable) {
	$return = "CREATE TABLE " . $databaseTable . " (primaryID INT NOT NULL AUTO_INCREMENT, ";
	
	foreach($fileHeaders as $f) {
		$return .= $f . " VARCHAR(50), ";
	}
	
	$return .= "PRIMARY KEY (primaryID));";
	
	return $return;
}

/**
	Creates the LOAD DATA LOCAL INFILE statement for a file
*/
function createLoadStatement($fileHeaders, $filename, $databaseTable) {
	
	$return = "LOAD DATA LOCAL INFILE '" . $filename . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES(";
				
	foreach($fileHeaders as $f) {
		$return .= $f . ", ";
	}
	
	//Above loop leaves a trailing ', ' (comma space) so this removes it
	$return = substr($return, 0, -2);
	
	//Now we specify the values to be inserted
	$return .= ");";

	return $return;
}

/*
	Calculate percentage (as a function of total upload size) of file upload left.
	Also updates currentlyUploadedSize.
*/
function calcUploadPercentage($currentFileSize) {
	$currentlyUploadedSize += $currentFileSize;
	return ($currentlyUploadedSize / $totalSize);
}

//foreach($_FILES['uploadFile'] as $keyOuter => $valueOuter) {
	/*print('uploadFileKey: ' . $key);
	print('uploadFileValue: ');
	print_r($value);*/

/*
	Get total size of files to be uploaded
*/

for($i = 0; $i < sizeOf($_FILES['uploadFile']['name']);  ++$i) {
	$totalSize += $_FILES['uploadFile']['size'][$i];
}

for($i = 0; $i < sizeOf($_FILES['uploadFile']['name']); ++$i) {
	$filename = $_FILES['uploadFile']['name'][$i];
	$tempPath = $_FILES['uploadFile']['tmp_name'][$i];
	$filesize = $_FILES['uploadFile']['size'][$i];
	$databaseTable = $_POST['county'] . '_' . $filename;
	$databaseTable = substr($databaseTable, 0, -4);
		
	/*****************
		Move files into 'data' directory within application 
	*****************/
	$upload_dir = 'data/' . ucfirst($_POST['county']) . '/';
	copy(realpath($_FILES['uploadFile']['tmp_name'][$i]), $upload_dir . $_FILES['uploadFile']['name'][$i]);
	$localFile = $upload_dir . $_FILES['uploadFile']['name'][$i];
		
	//Open file to be uploaded ('countyName_fileName.txt')
	$importFile = fopen(realpath($localFile), "r") or die("Unable to open file.");

	//Check if file has existing table and drop if so 
	$checkTable = mysqli_query($conn, "SHOW TABLES LIKE '" . $databaseTable . "';");
	if(mysqli_num_rows($checkTable) > 0) {
		$getDatabaseTable = mysqli_query($conn, "DROP TABLE " . $databaseTable);
	}

	//Retrieves header layout from first line of file to be uploaded (each field is delimited with a tab)
	$fileHeaders = fgets($importFile);
	$fileHeaders = explode("\t", $fileHeaders);	
			
	//Create corresponding table based on file headers 
	$createTableStatement = createTable($fileHeaders, $databaseTable);
	$checkTable = mysqli_query($conn, $createTableStatement);
	if(!$checkTable) {
		print "Error creating " . $databaseTable . ".";
	}
			
	//Create the load data local infile statement
	$loadStatement = createLoadStatement($fileHeaders, $localFile, $databaseTable);
	/*echo "<br><br><br>";
	print "LOAD STATEMENT: " . $loadStatement;
	echo "<br><br><br>";*/
				
	$failedCount = mysqli_query($conn, $loadStatement);
	$checkUpload = "SELECT COUNT(primaryID) FROM " . $databaseTable;
	$uploadCount = mysqli_query($conn, $checkUpload) or die(mysqli_error());
	$uploadCounter = mysqli_fetch_assoc($uploadCount);
	/*if($failedCount == true) {
		//print $uploadCounter['COUNT(primaryID)'] . " records added successfully to " . $databaseTable . "<br>";
		$return = calcUploadPercentage($filesize);
		echo $return . PHP_EOL;
		ob_flush();
		flush();
	}*/
	/*else {
		print "Records not added successfully.<br>";
	}*/
}
	/*foreach($value as $v) {
		foreach($v as $key => $vs) {
			print "Key: " . $key . "<br>";
			print "Value: " . $vs . "<br>";
		}
	}
	echo 'End outer <br>';*/
	/*foreach($value as $file) {
		print_r($file);
		echo ' end outer <br>';
		/*foreach($file as $key => $innerValue) {
			
			print "Key: " . $key . "<br>";
			print "Inner value: " . $file[$key] . "<br>";
			//print_r($key);
			echo "END FILE KEY<br>"; 
			//print_r($fileValue);
			
			//echo $FILES['name'][0];
			//echo realpath($FILES['tmp_name'][0]);
			//print_r($FILES);
			//File name will not have county name included
			//Prepend county name based on value chosen from dropdown menus	
			/*$databaseTable = $_POST['county'] . '_' . $file[$key];
			echo 'database table: ' . $databaseTable . '<br>';
			echo "<br><br><br>";
		}*/
	//}
//}
			
			//echo "UPLOAD DIRECTORY: " . $localFile;
?>

