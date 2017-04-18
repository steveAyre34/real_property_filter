<?php
	require("connection.php");
	session_start();
	
	$filterStatement = "SELECT COUNT(" . $_SESSION['county'] . "_owner.owner_id) FROM " . $_SESSION['county'] . "_owner, ";
	$tablesAddedToStatement = array();
	array_push($tablesAddedToStatement, "owner");

	//Add all table names necessary besides owner into select statement
	foreach($_POST as $postKey => $postValue) {
		if($postKey != "county") {
			print("POST KEY: {$postKey}, POST VALUE:<br>");
			print_r($postValue);
			echo "<br>";
			$separatePostValue = explode('||', $postKey);
			print('SEPARATE POST VALUE: ');
			echo "<br>";
			print_r($separatePostValue);
			if(array_search($separatePostValue[0], $tablesAddedToStatement) == false && $separatePostValue[0] != "owner") {
				//print("POST VALUE 0: {$separatePostValue[0]}");
				//echo "<br>";
				foreach($postValue as $selectMenuValue) {
					if(array_search($separatePostValue[0], $tablesAddedToStatement) == false){
						$filterStatementAppend = $separatePostValue[0] . ", ";
						$filterStatement .= $filterStatementAppend;
						array_push($tablesAddedToStatement, $separatePostValue[0]);
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
			if($separatePostValue[0] != "owner" && $separatePostValue[0] != "spec_dist" && $separatePostValue[0] != "specdist_def") {
                //Need to check if current table has ownerID so we know how to construct WHERE clause
                $checkForOwnerId = "SHOW COLUMNS IN {$_SESSION['county']}_{$separatePostValue[0]} LIKE 'owner_id';";
                $checkForOwnerIdResult = mysqli_query($link, $checkForOwnerId);
                //Assume table does not have ownerId
                $hasOwnerId = 0;
                $checkForOwnerIdArray = mysqli_fetch_assoc($checkForOwnerIdResult);
                if(!empty($checkForOwnerIdArray))
                    $hasOwnerId = 1;

				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = "(" . $_SESSION['county'] . "_" . $separatePostValue[0] . "." . $separatePostValue[1] . "='" . $selectMenuValue . "'";
					$whereClause .= ") AND ";
					//If this table has owner_id, match with owner by ownerID and muni_code
					if($hasOwnerId == 1) {
						$whereClause .= "(" . $_SESSION['county'] . "_owner.owner_id=" . $_SESSION['county'] . "_" . $separatePostValue[1] . ".owner_id) AND ";
						$whereClause .= "(" . $_SESSION['county'] . "_owner.muni_code=" . $_SESSION['county'] . "_" . $separatePostValue[1] . ".muni_code)";
					}
					//This table does not have owner_id
                    //Match by muni_code and parcel_id
					else {
                        $whereClause .= "(" . $_SESSION['county'] . "_owner.muni_code=" . $_SESSION['county'] . "_". $separatePostValue[0] . ".muni_code) AND ";
                        $whereClause .= "(" . $_SESSION['county'] . "_owner.parcel_id=" . $_SESSION['county'] . "_" . $separatePostValue[0] . ".parcel_id)";
                    }
					$filterStatement .= $whereClause;
					$filterStatement .= ") OR (";
				}
				$filterStatement .= ")";
			}
			else if($separatePostValue[0] == "owner") {
				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = $_SESSION['county'] . "_owner." . $separatePostValue[1] . "='" . $selectMenuValue . "'";
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
	}
	session_destroy();
?>	
