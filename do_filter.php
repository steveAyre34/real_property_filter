<?php
	require("connection.php");
	/*print("POST:");
	print_r($_POST);
	echo "<br><br><br>";*/
	$county = $_POST['county'];

	$filterStatement = "SELECT COUNT({$county}_owner.owner_id) FROM {$county}_owner ";
	$tablesAddedToStatement = array();
	array_push($tablesAddedToStatement, "{$county}_owner");

	/*
	 * First need to get all tables necessary for the filter statement (besides owner, obviously)
	 * Table name(s) is/are contained within each $_POST key
	 * Add joins
	 */
	foreach($_POST as $postKey => $postValue) {
		if($postKey != 'county' && (!empty($postValue) || substr($postKey, -5 == 'check'))) {
			/*
			 * First separate 'countyName_tableName||fieldName' to get just 'countyName_tableName'
			 */
			$table = explode("||", $postKey);
			$table = $table[0];
			if($table != "${county}_owner" && !in_array($table, $tablesAddedToStatement) && strpos($table, 'def') === FALSE) {
                array_push($tablesAddedToStatement, $table);

                /*
                 * Check if current table contains owner_id
                 */
                $checkForOwnerIdStatement = "SHOW COLUMNS IN {$table} LIKE 'owner_id';";
                $checkForOwnerIdResult = mysqli_query($link, $checkForOwnerIdStatement);

                //Table contains owner_id, so join with county_owner on owner_id
                if ($checkForOwnerIdResult && $checkForOwnerIdResult->num_rows == 1) {
                    $filterStatement .= "JOIN {$table} ON ({$county}_owner.owner_id={$table}.owner_id AND ";
                    $filterStatement .= " {$county}_owner.muni_code={$table}.muni_code) ";
                }
                else {
                    //Table doesn't contain owner_id so make sure it contains muni_code and parcel_id
                    //If so join with county_owner on those two fields
                    $checkForMuniCodeStatement = "SHOW COLUMNS IN {$table} LIKE 'muni_code';";
                    $checkForParcelIdStatement = "SHOW COLUMNS IN {$table} LIKE 'parcel_id';";
                    $checkForMuniCodeResult = mysqli_query($link, $checkForMuniCodeStatement);
                    $checkForParcelIdResult = mysqli_query($link, $checkForParcelIdStatement);

                    if (($checkForMuniCodeResult && $checkForParcelIdResult) && ($checkForMuniCodeResult->num_rows == 1 && $checkForParcelIdResult->num_rows == 1)) {
                        $filterStatement .= "JOIN {$table} ON ({$county}_owner.muni_code={$table}.muni_code AND {$county}_owner.parcel_id={$table}.parcel_id) ";
                    }
                }
            }
		}
	}
	//Remove trailing space to be neat because I feel like it
	$filterStatement = substr($filterStatement, 0, -1);
	/*print("TABLES ADDED: ");
	print_r($tablesAddedToStatement);
	echo "<br>";
	print("FILTER STATEMENT (no where): {$filterStatement}<br>");*/

	/*
	 * Construct the where clauses
	 */

	$filterStatement .= " WHERE(";

	foreach($_POST as $postKey => $postValue) {
        $fullField = str_replace('||', '.', $postKey);

		if(!empty($postValue) && $postKey != 'county') {
			//Multiple values selected for this field
            if(sizeOf($postValue) > 1) {
            	$filterStatement .= "(";
				foreach($postValue as $value) {
					if($value != '') {
                        $filterStatement .= "{$fullField}='{$value}' OR ";
                    }
				}
				//Remove trailing ' OR ' (space OR space = 4 characters)
				$filterStatement = substr($filterStatement, 0, -4);
				$filterStatement .= ") AND ";
				//$filterStatement .= ") AND ";
			}
			else {
            	if(!empty($postValue[0])) {
                    //Field is a min field
                    if (substr($fullField, -3) == 'min') {
                        $filterStatement .= "(" . substr($fullField, 0, -4) . ">='{$postValue[0]}') AND ";
                    }
                    else if (substr($fullField, -3) == 'max') {
                        $filterStatement .= "(" . substr($fullField, 0, -4) . "<='{$postValue[0]}') AND ";
                    }
                    else {
                        $filterStatement .= "({$fullField}='{$postValue[0]}') AND ";
                    }
                }
            }
		}
		//Checkbox values won't have a post value but will have a key name containing 'check'
        else {
            if (substr($fullField, -5) == 'check') {
                $filterStatement .= "(" . substr($fullField, 0, -6) . ">'0') AND ";
            }
        }
	}

	//Remove trailing ' AND ' (space AND space = 5 characters)
	$filterStatement = substr($filterStatement, 0, -5);
	$filterStatement .= ");";

	print("FILTER STATEMENT (where): {$filterStatement}<br>");

	//Remove trailing ' and ' (space and space)
	/*$filterStatement = substr($filterStatement, 0, -7);
	
	//Add trailing semicolon
	$filterStatement .= ";";
	echo "<br><br>";
	echo $filterStatement;
	echo "<br>";
	print_r($_POST);
	echo "<br>";*/
	$filterQuery = mysqli_query($link, $filterStatement);
	/*$filterResult = mysqli_fetch_assoc($filterQuery);
	print("Filter Result: ");
	foreach($filterResult as $resultKey => $resultValue) {
		print($resultValue . "<br>");
	}
	if(!$filterResult) {
		print("Error: " . mysqli_error($link));
	}*/
	if($filterQuery && $filterQuery->num_rows > 0) {
		while($row = mysqli_fetch_assoc($filterQuery)) {
			//print_r($row);
			print("Count = " . $row["COUNT({$county}_owner.owner_id)"]);
			echo "<br>";
		}
	}
	else {
		print("Error: " . mysqli_error($link));
	}
?>	
