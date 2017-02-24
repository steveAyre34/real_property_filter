<?php

function chooseHeaders($importFilePath, $importFileName, $databaseTable, $databaseTableHeaders, $missingHeaders) {
	//mysqli_fetch_fields yields a number for field type. This map will allow us to replace the number.
	$mysql_data_type_map = array(
		1	=>	'tinyint',
		2	=>	'smallint',
		3	=>	'int',
		4	=>	'float',
		5	=>	'double',
		7	=>	'timestamp',
		8	=>	'bigint',
		9	=>	'mediumint',
		10	=>	'date',
		11	=>	'time',
		12	=>	'datetime',
		13	=>	'year',
		16	=>	'bit',
		252	=>	'text',
		253	=>	'varchar',
		254	=>	'char',
		246	=>	'decimal'	
	);
	
	$importFile = fopen($importFilePath, "r") or die("Unable to open file.");
	$fileHeaders = fgets($importFile);
	$fileHeaders = explode("\t", $fileHeaders);
	
	if(empty($missingHeaders)) {
		//echo "<!DOCTYPE html>"
		echo "<html>";
		echo "<head>";
		echo "<link rel='stylesheet' type='text/css' href='import.css'>";
		echo "</head>";
			echo "<body>";
				print("<b>File Headers:</b><br>");
				print("These are the file headers for " . $databaseTable . ".txt. <br><br>");
				echo "<form action='do_import_owner.php' method='POST' enctype='multipart/form-data'>";
				foreach($fileHeaders as $f) {
					echo "<input type='text' name='fieldName' id='fieldName' value=\"" . $f . "\"/>";
					echo "<select name='fieldType' id='fieldType'/>";
					echo "<option value='selected'>Choose a data type</option>";
					foreach($mysql_data_type_map as $k => $v) {
						echo "<option value='" . $v . "'>" . $v . "</option>";
					}
					echo "</select>";
					echo "<input name='fieldLength' id='fieldLength' placeholder='Enter field length'></input><br><br>";
				}
				echo "<button type='submit' name='btn-upload'>Next</button>";
			echo "</form>";
			echo "</body>";
		echo "</html>";
	
	
	/*if($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		/*
			Create the table
		*/
		
		/*Creating the insert statement
		*/
		/*$insertStatement = "LOAD DATA INFILE '" . $importFileName . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES(";
		
		//Now append the headers to the LOAD DATA INFILE statement, in the order they were retrieved from the file
		//By structuring the query this way, even if the order of headers in the file changes the data can still be inserted
		foreach($fileHeaders as $h) {
			$insertStatement .= $h . ", ";
		}
		
		//Above loop leaves a trailing ", " (comma, space) on insertStatement, so this will remove it 
		$insertStatement = substr($insertStatement, 0, -2);
				
		//Now we specify the values to be inserted
		$insertStatement .= ");"; 
		
		return $insertStatement;*/
	}
?> 