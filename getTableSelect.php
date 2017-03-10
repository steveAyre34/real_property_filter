<?php
	require("connection.php");
	$county = $_GET['county'];
	
	/*
	* Creates a master list of categories present in owner.txt for this county
	* This way we don't pull duplicate search categories across files (everything in owner is searchable, not necessarily so for others)
	*/
	$query = "SELECT * FROM " 

	/*
	* For display purposes table name was capitalized on view page
	* This removes the capitalization to match table names
	*/
	$table = $county . '_' . lcfirst($_GET['table']);
	
	/*
	* Now we need to get the fields for whichever table is being requested
	*/
	$query = "SELECT * FROM " . $table . ";";
	$fieldNames = array();
	if($result = mysqli_query($conn, $query)) {
		//Get field information for all fields 
		$fieldInfo = mysqli_fetch_fields($result);
		
		foreach($fieldInfo as $name) {
			if($name->name != 'primaryID')
				array_push($fieldNames, $name->name);
		}
	
		mysqli_free_result($result);
	}
	mysqli_close($conn);	
?>

<html>
<body>
	<ul>
		<?php foreach($fieldNames as $name) { ?>
			<li><?php echo $name ?></li>
<?php	} ?>
	</ul>
</body>
</html>
