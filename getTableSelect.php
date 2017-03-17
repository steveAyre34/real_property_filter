<?php
	require("connection.php");
	//include("select2.php");
	session_start();
	
	$county = $_GET['county'];
	$alreadyDisplayedFields = $_SESSION['alreadyDisplayedFields'];
	
	$fieldNames = array();
	
	/*
	* For display purposes table name was capitalized on view page
	* This removes the capitalization to match table names
	*/
	$table = $county . '_' . lcfirst($_GET['table']);
		
	/*
	* Now we need to get the fields for whichever table is being requested
	* Also need to check for duplicates that already are displayed in other accordions and make sure primary key (primaryID) is not displayed - not a searchable field
	*/
	$query = "SHOW COLUMNS FROM " . $table . ";";
	if($result = mysqli_query($conn, $query)) {
		//Get field information for all fields 		
		while($row = $result->fetch_assoc()) {
			//If the field isn't a duplicate and isn't the primary key, add it to list of fields to be displayed and add to duplicate list for future checks
			if(!in_array($row['Field'], $alreadyDisplayedFields) && strcmp($row['Field'], 'primaryID') != 0) {
				array_push($fieldNames, $row['Field']);
				array_push($alreadyDisplayedFields, $row['Field']);
			}
		}
		
		mysqli_free_result($result);
	}
	mysqli_close($conn);
	
	//Update the fields displayed for this table for future duplicate checks
	$_SESSION['alreadyDisplayedFields'] = $alreadyDisplayedFields;
?>

<html>
	<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
	<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
<body>
	<ul>
		<?php foreach($fieldNames as $name) { ?>
			<li><?php echo $name ?></li>
<?php	} ?>
	</ul>
</body>
</html>
