<?php
	require("connection.php");
	//include("select2.php");
	session_start();
	
	
	/*
	* Class to hold field names along with values for the field's dropdown select menu
	* Seems like this is an easier way to link the dropdown menu values with a field name rather than using an associative array
	*/
	class Field
	{
		public $fieldName;
		public $selectMenuValues = array();
		
		public function __construct($field, $table, $conn){
			
				$this->fieldName = $field;
				$query = "SELECT DISTINCT " . $field . " FROM " . $table . /*" USE INDEX (" . $field . "_index)*/";";
				if($result = mysqli_query($conn, $query)) {
					while($row = $result->fetch_array(MYSQLI_ASSOC)) {
						array_push($this->selectMenuValues, $row[$field]);
					}
				}
		}
	}
	
	
	
	$county = $_GET['county'];
	$alreadyDisplayedFields = $_SESSION['alreadyDisplayedFields'];
	$fields = array();
	
	/*
	* For display purposes table name was capitalized on view page
	* This removes the capitalization to match table names
	*/
	$table = $county . '_' . strtolower($_GET['table']);
		
	/*
	* Now we need to get the fields for whichever table is being requested
	* Check for duplicates that already are displayed in other accordions and make sure primary key (primaryID) is not displayed - not a searchable field
	* Gather distinct selection options for dropdown menu
	*/
	$query = "SHOW COLUMNS FROM " . $table . ";";
	if($result = mysqli_query($conn, $query)) {
		//Get field information for all fields 		
		while($row = $result->fetch_assoc()) {
			//If the field isn't a duplicate and isn't the primary key, add it to list of fields to be displayed and add to duplicate list for future checks
			if(!in_array($row['Field'], $alreadyDisplayedFields) && strcmp($row['Field'], 'primaryID') != 0)/* && strcmp($row['Field'], "owner_id") != 0)*/ {
				array_push($fields, new Field($row['Field'], $table, $conn));
				array_push($alreadyDisplayedFields, $row['Field']);
			}
		}
		
		mysqli_free_result($result);
	}
	//mysqli_close($conn);
	
	//Update the fields displayed for this table for future duplicate checks
	$_SESSION['alreadyDisplayedFields'] = $alreadyDisplayedFields;
?>

<html>
	<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
	<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
	<script src="jquery.multiselect.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
	<link rel="stylesheet" href="jquery.multiselect.css"/>
<body>
	
		<?php foreach($fields as $field) { ?>
			<select name="<?php echo $field->fieldName ?>_menu[]" multiple="multiple" class="selectMenu" placeholder="<?php echo $field->fieldName ?>">
<?php			foreach($field->selectMenuValues as $menuValue) { ?>
						<option value="<?php echo $menuValue ?>"><?php echo $menuValue ?></option>
<?php			} ?>
			</select>
			<br>
<?php		} ?>
	
</body>
</html>

<script type="text/javascript">
	$(".selectMenu").multiselect({
		columns: 2
	});
</script>