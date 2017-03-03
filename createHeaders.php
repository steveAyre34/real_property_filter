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
		$return[$value] = $fileHeaders[$key];
	}

	$return['missing'] = array();

	if(count($fileHeaders) > count($databaseTableHeaders)) {
		$returnMissingIndex = 0;
		for($i = count($databaseTableHeaders); $i < count($fileHeaders); ++$i) {
			$return['missing'][$returnMissingIndex++] = $fileHeaders[$i];
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
		//$missingHeaders = getMissingHeaders($databaseTableHeaderNames, $map);
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
				<table id="table">
				<tr>
				<th>File Headers</th>
				<th>Database Headers</th>
				</tr>
<?php				foreach($map as $key => $value) {
					if($key != "missing") { 
					/*if($key !== $value) {	?>	
						<tr bgcolor="red" id="row">
<?php					} 	
					else { ?>
						<tr>
<?php					}*/	?>	
					<td><input type='text' name='fileHeaders[]' value='<?php echo $value ?>'/></td>
					<td><input type='text' name='databaseHeaders[]' value='<?php echo $key ?>'/></td>
					</tr>	
<?php				}} ?>
				</table><br><br>
<?php			if(!empty($map['missing'])) {  ?>
					<h4>These are headers in the file but not in the database.</h4>
<?php				foreach($map['missing'] as $m) { 
					if($m != 'Array')				?>
						<input type='text' name='missingHeaders[]' value='<?php echo $m ?>'/><br>
<?php				}
				}  ?>
				<button type="submit">Import</button>
			</form>
			</body>
		</html>

<!--<script type="text/javascript">
	 $("#databaseHeader").change(function() {
		var databaseHeaders = document.getElementByClassName("databaseHeader");
		var fileHeaders = document.getElementByClassName("fileHeader");
		
	});
</script>-->
<!--<script type="text/javascript">
	$(".databaseHeader").change(function() {
		compareRows(document.getElementById('table'));
	});
	$(".fileHeader").change(function() {
	});

	function compareRows(table) {
		var row, rows = table.rows;
		var cell, cells;
		var rowText;

		//For each row in the table
		for(var i = 0; iLength = rows.length; i < iLength; ++i) {
			rows = rows[i];
			cells = rows.cells;
			
			//Compare each cell
			for(var j = 0; jLength = cells.length; j < jLength; ++j) {
				for(var k = 0; k < jLength; k++) {
					if(k != j && cells[k].textContent == cell.textContent-->
<script type="text/javascript">
	$('td').attr('contenteditable', 'true');
	var cell;

	function highlight() {
		$(arguments).toggleClass('invalid', true);
	}

	function compareHeaders(e) {
		//Reset style before re-checking
		$('td.invalid').toggleClass('invalid');
		//Get table rows as array of array
		var rows = $('tr').map(function(elem, i) {
			return [$(this).children('td').toArray()];
		}).toArray();

		//Loop through the rows and highlight non-equal
		for(var i = 0; i < rows.length; ++i) {
			cell = {};
			for(var j = 0; j < rows[i].length; ++j) {
				var cellText = $(rows[i][j]).text();
				if(cell[cellText] != $(rows[i][j]).text()) {
					highlight(cell[cellText], rows[i][j]);
				}
				else {
					cell[cellText] = rows[i][j];
				}
				/*if(i < rows.length - 1 && cellText != $(rows[i + 1][j]).text()) {
					highlight(rows[i][j], rows[i + 1][j]);
				}*/
			}
		}
	}

	$('td').change(compareHeaders);
</script>
