<?php
		
	//include("importChooseCounty.php");
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
		if($key < count($fileHeaders)) {
			$return[$value] = $fileHeaders[$key];
		}
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
	
	$databaseTable = $_GET['county'] . '_' . $_GET['fileName'];
		
	//Open file to be uploaded ('countyName_fileName.txt')
	$importFile = fopen($_GET['fileName'], "r") or die("Unable to open file.");
		
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
		$rowID = 0;
?>
<!DOCTYPE html>
<html>
	<head>
	<link rel='stylesheet' type='text/css' href='import.css'>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		</head>
			<body>
				<p>These are the file headers for <?php echo $databaseTable ?>.txt that already exist in the RP2 database.<br></p>
				<form name="table_form" id='<?php echo $databaseTable ?>'method='POST' enctype='multipart/form-data'>
				<table>
				<tr>
				<th>File Headers</th>
				<th>Database Headers</th>
				</tr>
				<tbody>
<?php				foreach($map as $key => $value) {
					if($key != "missing") { 
					/*if($key !== $value) {	?>	
						<tr bgcolor="red" id="row">
<?php					} 	
					else { ?>*/
					?>	<tr>
			<?php	//	}		?>
					<td><input type='text' name='fileHeaders[]' value='<?php echo $value ?>'/></td>
					<td><!--<input type='text' name='databaseHeaders[]' value='<//?php echo $key ?>'/>-->
						<select name='databaseHeaders[]' id='databaseHeaders_<?php echo $databaseTable ?>' onchange="changeValue(this)">
						<option value='<?php echo $key ?>'><?php echo $key ?></option>
<?php						foreach($databaseTableHeaderNames as $db) { ?>
								<option value='<?php echo $key ?>'><?php echo $db ?></option>
<?php							} ?>
					</td>
					</tr>	
<?php				}} ?>
				</tbody>
				</table><br><br>
<?php			if(!empty($map['missing'])) {  ?>
					<h4>These are headers in the file but not in the database.</h4>
<?php				foreach($map['missing'] as $m) { 
					if($m != 'Array')				?>
						<input type='text' name='missingHeaders[]' value='<?php echo $m ?>'/><br>
<?php				}
				}
				else {
					echo "No missing";
				}?>
				<input type="button" value="Import" onclick="submitTable('<?php echo $databaseTable ?>',  '<?php echo $_GET['fileName'] ?>')"/>
			</form>
			</body>
		</html>
<script type="text/javascript">
	//$('#'+'<?php echo $databaseTable ?>'+' tbody tr td').each(function() {
		/*var $fileHeader = $('td:eq(0)', this);
		var $databaseHeader = $('td:eq(1)', this);
		
		console.log("FILE HEADER: " + $fileHeader.text());
		console.log("DATABASE HEADER: " + $databaseHeader.text());		
		
		if($fileHeader.text() != $databaseHeader.text()) {
			$fileHeader.css('backgroundColor', 'red');
			$databaseHeader.css('backgroundColor', 'red');
		}
		else {
			$fileHeader.css('backgroundColor', 'white');	
			$databaseHeader.css('backgroundColor', 'white');
		}*/
	
	//});
	
	/*function changeValue(control) {
		var headers = headers.elements['databaseTableHeaders[]'];
		for(var i = 0; i < headers.length; ++i) {
			console.log(headers[i]);
		}
	}

	inputCheck();
	$("#<?php echo $databaseTable ?> input").bind("change paste keyup", function() {
		inputCheck();
	});
	
	//document.getElementById('databaseHeaders_<?php echo $databaseTable ?>').addEventListener('change', inputCheck(), true);
	
	function inputCheck() {
		var fileInput = $("#<?php echo $databaseTable ?> input").length;
		var databaseInput = document.getElementById('databaseHeaders_<?php echo $databaseTable ?>').value;
		
		var fileValArr = [];
		for(var j = 0; j < fileInput - 1; ++j) {
			var fileVal = $("#<?php echo $databaseTable?> input:eq(" + j + ")").val();
			//console.log("TOTAL FILE INPUT VAL ARRAY " + j + ": " + fileVal);
			if(fileVal != 'Import') {
				fileValArr.push(fileVal);
			}
		}
		
		for(var i = 0; i < fileInput - 1; ++i) {
			var databaseVal = $("#<?php echo $databaseTable ?> select:eq(" + i + ")").val();
			//console.log("TOTAL DATABASE INPUT VAL ARRAY " + i + ": " + databaseVal);
		}
		
		//var sorted_arr = fileValArr.sort();
		var results = [];
		for(var i = 0; i < fileValArr.length - 1; ++i) {
			if(sorted_arr[i + 1] == sorted_arr[i]) {
				results.push(sorted_arr[i]);
			}
		}
		
		$("#<?php echo $databaseTable ?> input").removeClass('highlight');
		for(var k = 0; k < fileInput; ++k) {
			$("#<?php echo $databaseTable ?> :input[value='" + results[k] + "']").addClass('highlight');
		}
	}*/
</script>
