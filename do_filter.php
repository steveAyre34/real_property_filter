<?php
	/*require("connection.php");
	
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
	$filterStatement .= " where ";	

	//Now add where clauses
	foreach($_POST as $postKey => $postValue) {
		if($postKey != "county") {
			$separatePostValue = explode('||', $postKey);
			if($separatePostValue[1] != "owner") {
				$filterStatement .= "(";
				foreach($postValue as $selectMenuValue) {
					$whereClause = "(" . $_POST['county'] . "_" . $separatePostValue[1] . "." . $separatePostValue[0] . "='" . $selectMenuValue . "'";
					$whereClause .= " AND (";
					if(/*table has owner id) {
						. $_POST['county'] . "_owner.owner_id=" . $_POST['county'] . "_owner.owner_id)";
					}
					else {
						//match by parcel_id and muni_code 
					}
					$filterStatement .= $whereClause;
					$filterStatement .= " OR ";
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
	$filterStatement = substr($filterStatement, 0, -5);
	
	//Add trailing semicolon
	$filterStatement .= ";";
	echo "<br><br>";
	echo $filterStatement;
	$filterQuery = mysqli_query($conn, $filterStatement);
	$filterResult = mysqli_fetch_assoc($filterQuery);
	print("Filter Result: ");
	foreach($filterResult as $resultKey => $resultValue) {
		print($resultValue . "<br>");
	}*/



	//This code block isolates the selected field values -- need to get names associated as well
	foreach($_POST as $POST) {
		if(!empty($POST) && $POST[0] != -1 && is_array($POST)) {
            print_r($POST);
            echo '<br>';
        }
	}
?>	
