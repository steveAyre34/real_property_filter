<?php
	require("connection.php");
	$county = $_GET['county'];
	
	/*
	* Creates a master list of categories present in owner.txt for this county
	* This way we don't pull duplicate search categories across files (everything in owner is searchable, not necessarily so for others)
	*/
	$query = "SHOW COLUMNS FROM " . $county . "_owner;";
	$ownerFields = array();
	if($result = mysqli_query($conn, $query)) {
		//Get field information for all fields 
		while($row = $result->fetch_assoc()) {
			array_push($ownerFields, $row['Field']);
		}
		mysqli_free_result($result);
	}
	
	$fieldNames = array();
	
	if(strcmp($_GET['table'], 'Owner') != 0) {
		/*
		* For display purposes table name was capitalized on view page
		* This removes the capitalization to match table names
		*/
		$table = $county . '_' . lcfirst($_GET['table']);
		
		/*
		* Now we need to get the fields for whichever table is being requested
		*/
		$query = "SHOW COLUMNS FROM " . $table . ";";
		if($result = mysqli_query($conn, $query)) {
			//Get field information for all fields 		
			while($row = $result->fetch_assoc()) {
				if(!in_array($row['Field'], $ownerFields))
					array_push($fieldNames, $row['Field']);
			}
		
			mysqli_free_result($result);
		}
		mysqli_close($conn);
	}
	else {
		foreach($ownerFields as $owner) {
			if(strcmp($owner, 'primaryID') != 0)
				array_push($fieldNames, $owner);
		}
		mysqli_close($conn);
	}
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
