<?php
	$cache_ext = '.php';
	$cache_folder = 'views/' . $_GET['county'] . '/';
	$ignore_pages = array('', '');
	
	$dynamic_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
	$cache_file = $cache_folder . md5($dynamic_url) . $cache_ext;
	$ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;
	
	if(!$ignore && file_exists($cache_file)) {
		ob_start('ob_gzhandler');
		readfile($cache_file);
		ob_end_flush();
		exit();
	}
	else {
	
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
					$query = "SELECT DISTINCT " . $field . " FROM " . $table . /*" USE INDEX queryIndex*/";";
					if($result = mysqli_query($conn, $query)) {
						while($row = $result->fetch_array(MYSQLI_ASSOC)) {
							if(trim($row[$field]) != "") {
								array_push($this->selectMenuValues, $row[$field]);
							}
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
		
		ob_start('ob_gzhandler');
	}
?>

<html>
	<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
	<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
	<script src="jquery.multiselect.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
	<link rel="stylesheet" href="jquery.multiselect.css"/>
<body>
	
		<?php foreach($fields as $field) { 
			if(sizeOf($field->selectMenuValues) > 0) {?>
			<h4><?php echo $field->fieldName ?></h4>
			<select name="<?php echo $field->fieldName ?>||<?php echo strtolower($_GET['table']) ?>[]" multiple="multiple" class="selectMenu[]">
<?php			foreach($field->selectMenuValues as $menuValue) { ?>
						<option value="<?php echo $menuValue ?>"><?php echo $menuValue ?></option>
<?php			} ?>
			</select>
			<br>
<?php		}} ?>
	
</body>
</html>

<!--<script type="text/javascript">
	$(".selectMenu").each(function() {
		$(this).multiselect({
			placeholder: $(this).attr('placeholder')
		})
	});
</script>-->

<?php
	if(!is_dir($cache_folder)) {
		mkdir($cache_folder);
	}
	if(!$ignore) {
		$fp = fopen($cache_file, 'w');
		fwrite($fp, ob_get_contents());
		fclose($fp);
	}
	ob_end_flush();
?>
