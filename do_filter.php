<?php
	require("connection.php");
	
	$filterStatement = "SELECT COUNT(" . $_POST['county'] . "_owner.owner_id) FROM " . $_POST['county'] . "_owner, ";
	$tablesAddedToStatement = array();
	array_push($tablesAddedToStatement, "owner");

	//Add all table names necessary besides owner into select statement
	foreach($_POST as $postKey => $postValue) {
		if($postKey != "county") {
			$separatePostValue = explode('||', $postKey);
			if(array_search($separatePostValue[1], $tablesAddedToStatement) == false && $separatePostValue[1] != "owner") {
				foreach($postValue as $selectMenuValue) {
					if(array_search($separatePostValue[1], $tablesAddedToStatement) == false){
						$filterStatementAppend = $_POST['county'] . "_" . $separatePostValue[1] . ", ";	
						$filterStatement .= $filterStatementAppend;
						array_push($tablesAddedToStatement, $separatePostValue[1]);
						break;
					}
				}
			}	
		}
	}
	//Remove trailing ', ' (comma space)
	$filterStatement = substr($filterStatement, 0, -2);
	$filterStatement .= " WHERE ";

	//Now add where clauses
	foreach($_POST as $postKey => $postValue) {
		if($postKey != "county") {
			$separatePostValue = explode('||', $postKey);
			if($separatePostValue[1] != "owner" && $separatePostValue[1] != "spec_dist" && $separatePostValue[1] != "specdist_def") {
                //Need to check if current table has ownerID so we know how to construct WHERE clause
                $checkForOwnerId = "SHOW COLUMNS IN {$_POST['county']}_{$separatePostValue[1]} LIKE 'owner_id';";
                $checkForOwnerIdResult = mysqli_query($link, $checkForOwnerId);
                //Assume table does not have ownerId
                $hasOwnerId = 0;
                $checkForOwnerIdArray = mysqli_fetch_assoc($checkForOwnerIdResult);
                if(!empty($checkForOwnerIdArray))
                    $hasOwnerId = 1;

				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = "(" . $_POST['county'] . "_" . $separatePostValue[1] . "." . $separatePostValue[0] . "='" . $selectMenuValue . "'";
					$whereClause .= ") AND ";
					//If this table has owner_id, match with owner by ownerID and muni_code
					if($hasOwnerId == 1) {
						$whereClause .= "(" . $_POST['county'] . "_owner.owner_id=" . $_POST['county'] . "_" . $separatePostValue[1] . ".owner_id) AND ";
						$whereClause .= "(" . $_POST['county'] . "_owner.muni_code=" . $_POST['county'] . "_" . $separatePostValue[1] . ".muni_code)";
					}
					//This table does not have owner_id
                    //Match by muni_code and parcel_id
					else {
                        $whereClause .= "(" . $_POST['county'] . "_owner.muni_code=" . $_POST['county'] . "_". $separatePostValue[1] . ".muni_code) AND ";
                        $whereClause .= "(" . $_POST['county'] . "_owner.parcel_id=" . $_POST['county'] . "_" . $separatePostValue[1] . ".parcel_id)";
                    }
					$filterStatement .= $whereClause;
					$filterStatement .= ") OR (";
				}
				$filterStatement .= ")";
			}
			else if($separatePostValue[1] == "owner") {
				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = $_POST['county'] . "_owner." . $separatePostValue[0] . "='" . $selectMenuValue . "'";
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
	$filterQuery = mysqli_query($link, $filterStatement);
	$filterResult = mysqli_fetch_assoc($filterQuery);
	print("Filter Result: ");
	foreach($filterResult as $resultKey => $resultValue) {
		print($resultValue . "<br>");
	}

?>	
