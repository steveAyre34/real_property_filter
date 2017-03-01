<?php
		
	//require("do_import.php");
	require("connection.php");
	
/**
	Checks that each of the file headers exists in their corresponding database table.
	Does not check the order of headers in the file -- the structure of our insert statement later renders this unnecessary
*/
function getMissingHeaders($databaseTableHeaders, $fileHeaders) {
	$return = array();
	
	foreach($databaseTableHeaders as $db) {
		$flag = 0;
		foreach($fileHeaders as $f) {
			if(strcmp($f, $db) == 0 || ((levenshtein($f, $db) >= 0) && (!in_array($f, $return)))) {
				$flag = 1;
			}
		}
		if($flag == 0)
			array_push($return, $db);
	}

	return $return;
}

function getMisspelledHeaders($databaseTableHeaders, $fileHeaders) {
	$return = array();
	
	foreach($fileHeaders as $f) {
		$flag = 0;
		foreach($databaseTableHeaders as $db) {
			if(levenshtein($f, $db) < 0 && levenshtein($f, $db->name) > 3)
				$flag = 1;
		}
		if($flag == 0)
			array_push($return, $f);
	}
	
	return $return;
}
/**
	Trims header information to just names and returns array of column names 
	mysqli_fetch_fields returns an object, we only need names
*/
function trimHeaderInfo($databaseTableHeaders) {
	$return = array();
	foreach($databaseTableHeaders as $db) {
		array_push($return, trim($db->name));
	}
	return $return;
}

/*
	Maps DB and File headers to associative array 
	DB Header => File Header
*/
function mapHeaders($databaseTableHeaders, $fileHeaders) {
	$return = array();
	
	
	foreach($databaseTableHeaders as $key => $value) {
		foreach($fileHeaders as $f) {
			if(strcmp($f, $value) == 0) {
				$return[$value] = $f;
				break;
			}
			else if((levenshtein($f, $value) > 0) && (!in_array($f, $return))) {
				$return[$value] = $f;
				break;
			}
		}
	}
	return $return;
}	
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
	
	$databaseTable = $_GET['county'] . '_owner.txt';
		
	//Open file to be uploaded ('countyName_fileName.txt')
	$importFile = fopen('owner.txt', "r") or die("Unable to open file.");
		
		//Removes file extension from filename to give table name 
		$databaseTable = substr($databaseTable, 0, -4);
		
		//Connect to database, clear old data and then perform SELECT * so we can get table headers later
		$getDatabaseTable = mysqli_query($conn, "DELETE FROM " . $databaseTable);
		$getDatabaseTable = mysqli_query($conn, "SELECT * FROM " . $databaseTable);
		
		//Get headers (in order) from specified table in the database and trim unnecessary object info 
		$databaseTableHeaders = mysqli_fetch_fields($getDatabaseTable);
		$databaseTableHeaderNames = trimHeaderInfo($databaseTableHeaders);

		//Retrieves header layout from first line of file to be uploaded (each field is delimited with a tab)
		$fileHeaders = fgets($importFile);
		$fileHeaders = explode("\t", $fileHeaders);
		
		$headers = array();
		//Trim whitespace from header names (was causing a mismatch on last header due to whitespace)
		foreach($fileHeaders as $k=>$vHeader) {
			array_push($headers, trim($vHeader));
		}
		
		//$misspelledHeaders = getMisspelledHeaders($databaseTableHeaderNames, $headers);
		$map = mapHeaders($databaseTableHeaderNames, $headers);
		$missingHeaders = getMissingHeaders($databaseTableHeaderNames, $map);
?>
<!DOCTYPE html>
<html>
	<head>
	<link rel='stylesheet' type='text/css' href='import.css'>
		</head>
			<body>
				<p>These are the file headers for <?php echo $databaseTable ?>.txt that already exist in the RP2 database.<br></p>
				<h4>File Headers &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp DB Headers</h4>
				<form action='do_import_owner.php' method='POST' enctype='multipart/form-data'>
<?php			foreach($map as $key => $value) { ?>
					<input type='text' name='fileHeaders[]' id='fileHeader' value='<?php echo $value ?>'/>
<?php				if(levenshtein($key, $value) == 1) { ?>
						<input type='text' name='databaseHeaders[]' value='<?php echo $key ?>' class='potentialMisspell'/><br>
<?php				}
					else {?>
						<input type='text' name='databaseHeaders[]' value='<?php echo $key ?>'/><br>
<?php 				}			
				} ?>
<?php			if(!empty($missingHeaders)) {  ?>
					<h4>These are headers in the file but not in the database.</h4>
<?php				foreach($missingHeaders as $m) { ?>
						<input type='text' name='missingHeaders[]' value='<?php echo $m ?>'/><br>
<?php				}
				}  ?>
			</form>
			</body>
		</html> 