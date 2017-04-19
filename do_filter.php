<?php
	require("connection.php");
	print("POST:");
	print_r($_POST);
	echo "<br><br><br>";
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
		if($postKey != 'county') {
			/*
			 * First separate 'countyName_tableName||fieldName' to get just 'countyName_tableName'
			 */
			$table = explode("||", $postKey);
			$table = $table[0];
			if($table != "${county}_owner" && !in_array($table, $tablesAddedToStatement) && strpos($table, 'def') === FALSE) {
                //$filterStatement .= "{$table}, ";
                array_push($tablesAddedToStatement, $table);

                /*
                 * Check if current table contains owner_id
                 */
                //$hasOwnerId = 0;
                $checkForOwnerIdStatement = "SHOW COLUMNS IN {$table} LIKE 'owner_id';";
                $checkForOwnerIdResult = mysqli_query($link, $checkForOwnerIdStatement);

                //Table contains owner_id, so join with county_owner on owner_id
                if ($checkForOwnerIdResult && $checkForOwnerIdResult->num_rows == 1) {
                    $filterStatement .= "JOIN {$table} ON ({$county}_owner.owner_id={$table}.owner_id), ";
                }
                else {
                    //Table doesn't contain owner_id so make sure it contains muni_code and parcel_id
                    //If so join with county_owner on those two fields
                    $checkForMuniCodeStatement = "SHOW COLUMNS IN {$table} LIKE 'muni_code';";
                    $checkForParcelIdStatement = "SHOW COLUMNS IN {$table} LIKE 'parcel_id';";
                    $checkForMuniCodeResult = mysqli_query($link, $checkForMuniCodeStatement);
                    $checkForParcelIdResult = mysqli_query($link, $checkForParcelIdStatement);

                    if (($checkForMuniCodeResult && $checkForParcelIdResult) && ($checkForMuniCodeResult->num_rows == 1 && $checkForParcelIdResult->num_rows == 1)) {
                        $filterStatement .= "JOIN {$table} ON ({$county}_owner.muni_code={$table}.muni_code AND {$county}_owner.parcel_id={$table}.parcel_id), ";
                    }
                }
            }
		}
	}
	//Remove trailing comma space (', ')
	$filterStatement = substr($filterStatement, 0, -2);
	print("TABLES ADDED: ");
	print_r($tablesAddedToStatement);
	echo "<br>";
	print("FILTER STATEMENT (no where): {$filterStatement}<br>");
	/*
	 * Construct the where clauses
	 *

	$filterStatement .= " WHERE ";

	foreach($_POST as $postKey => $postValue) {
		if($postKey != 'county') {
			//Replace '||' with '.' so that full field name reflects correctly MYSQL syntax (tableName.fieldName)
			$fullField = str_replace('||', '.', $postKey);

			//Get the table name for the current field
			$table = explode('_', explode('||', $postKey)[0])[1];
			if($table != 'owner') {
				/*
				 * Check if this table contains owner_id
				 * If so, join with owner on owner_id
				 *

			}
		}*/

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	//Now add where clauses
	/*foreach($_POST as $postKey => $postValue) {
		if($postKey != "county") {
			$separatePostValue = explode('||', $postKey);
			if($separatePostValue[0] != "owner" && $separatePostValue[0] != "spec_dist" && $separatePostValue[0] != "specdist_def") {
                //Need to check if current table has ownerID so we know how to construct WHERE clause
                $checkForOwnerId = "SHOW COLUMNS IN {{$county}_{$separatePostValue[0]} LIKE 'owner_id';";
                $checkForOwnerIdResult = mysqli_query($link, $checkForOwnerId);
                //Assume table does not have ownerId
                $hasOwnerId = 0;
                $checkForOwnerIdArray = mysqli_fetch_assoc($checkForOwnerIdResult);
                if(!empty($checkForOwnerIdArray))
                    $hasOwnerId = 1;

				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = "({$county}_{$separatePostValue[0]}.{$separatePostValue[1]}='{$selectMenuValue}'";
					$whereClause .= ") AND ";
					//If this table has owner_id, match with owner by ownerID and muni_code
					if($hasOwnerId == 1) {
						$whereClause .= "({$county}_owner.owner_id={$county}_{$separatePostValue[1]}.owner_id) AND ";
						$whereClause .= "({$county}_owner.muni_code={$county}_{$separatePostValue[1]}.muni_code)";
					}
					//This table does not have owner_id
                    //Match by muni_code and parcel_id
					else {
                        $whereClause .= "({$county}_owner.muni_code={$county}_{$separatePostValue[0]}.muni_code) AND ";
                        $whereClause .= "({$county}_owner.parcel_id={$county}_{$separatePostValue[0]}.parcel_id)";
                    }
					$filterStatement .= $whereClause;
					$filterStatement .= ") OR (";
				}
				$filterStatement .= ")";
			}
			else if($separatePostValue[0] == "owner") {
				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = "{$county}_owner.{$separatePostValue[1]}='{$selectMenuValue}'";
					$filterStatement .= $whereClause;
					$filterStatement .= " OR ";
				}
				$filterStatement .= ")";
			}
			$filterStatement = substr($filterStatement, 0, -5);
			$filterStatement .= ") AND ";
		}
	}

	//Remove trailing ' and ' (space and space)
	$filterStatement = substr($filterStatement, 0, -7);
	
	//Add trailing semicolon
	$filterStatement .= ";";
	echo "<br><br>";
	echo $filterStatement;
	echo "<br>";
	print_r($_POST);
	echo "<br>";
	$filterQuery = mysqli_query($link, $filterStatement);
	$filterResult = mysqli_fetch_assoc($filterQuery);
	print("Filter Result: ");
	foreach($filterResult as $resultKey => $resultValue) {
		print($resultValue . "<br>");
	}
	if(!$filterResult) {
		print("Error: " . mysqli_error($link));
	}*/
?>	
