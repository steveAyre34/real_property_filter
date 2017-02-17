<?php
	
function chooseHeaders($fileHeaders, $databaseTable) {
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
	
	//echo "<!DOCTYPE html>"
	echo "<html>";
	echo "<head>";
	echo "<link rel='stylesheet' type='text/css' href='import.css'>";
	echo "</head>";
		echo "<body>";
			print("<b>File Headers:</b><br>");
			print("These are the file headers for " . $databaseTable . ".txt. Please select a type and length for each field:<br><br>");
			foreach($fileHeaders as $f) {
				print $f;
				echo "<select name='headerType' id='headerType'>";
				echo "<option value='selected'>Choose a data type</option>";
				foreach($mysql_data_type_map as $k => $v) {
					echo "<option value='" . $v . "'>" . $v . "</option>";
				}
				echo "</select>";
				echo "<input name='headerLength' id='headerLength' placeholder='Enter field length'></input><br><br>";
			}
			echo "<button type="submit" name="btn-upload">Next</button>";
		echo "</body>";
	echo "</html>";
}?> 