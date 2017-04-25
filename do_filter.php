<?php
	require("connection.php");

	$county = $_POST['county'];
	$owner = $county . '_owner';
	/*
	 * Array that contains the field names of the standard fields to be exported
	 * This way if user is filtering by a standard field, it's not appended again to the export
	 * i.e. if user searches by zip code, then zip code column won't appear in export file twice
	 */
	$standardColumns = [
		"owner_id",
		"secondary_name",
		"owner_first_name",
		"owner_init_name",
		"owner_last_name",
		"owner_name_suffix",
		"owner_secondary_name",
		"concatenated_address_1",
		"concatenated_address_2",
		"mail_city",
		"owner_mail_state",
		"mail_zip",
		"mail_country"
	];

	$filterStatement = "SELECT {$owner}.secondary_name AS CompanyName, {$owner}.owner_first_name AS FirstName, ";
	$filterStatement .= "{$owner}.owner_init_name AS MiddleInitial, {$owner}.owner_last_name AS LastName, {$owner}.owner_name_suffix AS Suffix, ";
	$filterStatement .=   "{$owner}.secondary_name AS SecondaryName, {$owner}.concatenated_address_1 as AddressLine1, ";
	$filterStatement .= "{$owner}.concatenated_address_2 as AddressLine2, ";
	$filterStatement .=	"{$owner}.mail_city AS City, {$owner}.owner_mail_state AS State, {$owner}.mail_zip AS Zip, ";
	$filterStatement .=   "{$owner}.mail_country AS Country, {$owner}.owner_id AS ID, {$owner}.crrt AS CRRT, {$owner}.dp3 AS DP3, ";

	/*
	 * Now add the user-selected query fields to select statement
	 */
	foreach($_POST as $postKey => $postValue) {
	    if($postKey != 'county') {
            $key = str_replace("||", ".", $postKey);
	        //If field name ends in 'checkbox', remove '_checkbox'
            if(substr($postKey, -8) == 'checkbox')
                $key = substr($postKey, 0, -9);

            //If field name ends in 'min' or 'max', remove '_min' or '_max' accordingly
            else if(substr($postKey, -3) == 'min' || substr($postKey, -3) == 'max')
                $key = substr($postKey, 0, -4);

            //Get field alias
            //$alias = explode(".", $key);
            //$alias = $key[1];
            $filterStatement .= "{$key}, ";
        }
    }
    $filterStatement = substr($filterStatement, 0, -2);
	$filterStatement .= " FROM {$owner} ";
	$tablesAddedToStatement = array();
	$fullFieldNames = array();
	//$fullFieldValues = array();
	array_push($tablesAddedToStatement, "{$county}_owner");

	/*
	 * First need to get all tables necessary for the filter statement (besides owner, obviously)
	 * Table name(s) is/are contained within each $_POST key
	 * Add joins
	 */
	foreach($_POST as $postKey => $postValue) {
		if($postKey != 'county' && (!empty($postValue) || substr($postKey, -8) == 'checkbox')) {
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

	/*
	 * Construct the where clauses
	 */

	$filterStatement .= " WHERE(";

	foreach($_POST as $postKey => $postValue) {
        $fullField = str_replace('||', '.', $postKey);
        $fieldName = explode('.', $fullField);
		if($postKey != 'county') {
            $fieldName = $fieldName[1];
            array_push($fullFieldNames, $fieldName);
        }

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
            if (substr($fullField, -8) == 'checkbox') {
                $filterStatement .= "(" . substr($fullField, 0, -9) . ">'0') AND ";
            }
        }
	}

	//Remove trailing ' AND ' (space AND space = 5 characters)
	$filterStatement = substr($filterStatement, 0, -5);
	$filterStatement .= ");";

	//print("FILTER STATEMENT (where): {$filterStatement}<br>");

	//$filterQuery = mysqli_query($link, $filterStatement);

	/*if($filterQuery && $filterQuery->num_rows > 0) {
		while($row = mysqli_fetch_assoc($filterQuery)) {
			//print_r($row);
			print("Count = " . $row["count"]);
			echo "<br>";
		}
	}
	else {
		print("Error: " . mysqli_error($link));
	}

	$filterQuery = mysqli_query($link, $filterStatement);*/
	/*if($filterQuery && $filterQuery->num_rows > 0) {
   	 	while($row = mysqli_fetch_assoc($filterQuery)) {
        	echo "<tr>";
			echo "<td></td>";
			echo "<td>" . $row["CompanyName"] . "</td>";
			echo "<td>" . $row["FirstName"] . "</td>";
			echo "<td>" . $row["MiddleInitial"] . "</td>";
			echo "<td>" . $row["LastName"] . "</td>";
			echo "<td>" . $row["Suffix"] . "</td>";
			echo "<td>" . $row["SecondaryName"] . "</td>";
			echo "<td>" . $row["AddressLine1"] . "</td>";
			echo "<td>" . $row["AddressLine2"] . "</td>";
			echo "<td>" . $row["City"] . "</td>";
			echo "<td>" . $row["State"] . "</td>";
			echo "<td>" . $row["Zip"] . "</td>";
			echo "<td>" . $row["Country"] . "</td>";
			echo "<td>" . $row["ID"] . "</td>";
			echo "</tr>";
		}
	}
	$results = array();
	if($filterQuery && $filterQuery->num_rows > 0) {
		while($row = mysqli_fetch_assoc($filterQuery)) {
			array_push($results, $row);
		}
	}*/
	/*if($_SERVER['REQUEST_METHOD'] === 'GET'){
		$_GET['filterStatement'] = $filterStatement;
	}*/
?>

<html>
	<head>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
		<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
		<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
	</head>
	<body>
		<table id="results" class="resultsTable">
			<thead>
				<tr>
					<!--Headers for the standard export fields-->
					<th>Actions</th>
					<th>Company Name</th>
					<th>First Name</th>
					<th>Middle Initial</th>
					<th>Last Name</th>
					<th>Suffix</th>
					<th>Secondary Name</th>
					<th>Address Line 1</th>
					<th>Address Line 2</th>
					<th>City</th>
					<th>State</th>
					<th>Zip Code (+4)</th>
					<th>Country</th>
                    <th>ID</th>
					<th>CRRT</th>
					<th>DP3</th>
					<!--Now headers for any selected fields that aren't a standard export field -->
					<?php foreach($fullFieldNames as $fields) {
   		 					if (!in_array($fields, $standardColumns)) {
        						print("<th>{$fields}</th>");
    						}
					} ?>
				</tr>
			</thead>
			<tbody>
				<!--hp foreach($results as $results) {
					echo "<tr>";
					echo "<td></td>";
					echo "<td>" . $results["CompanyName"] . "</td>";
					echo "<td>" . $results["FirstName"] . "</td>";
					echo "<td>" . $results["MiddleInitial"] . "</td>";
					echo "<td>" . $results["LastName"] . "</td>";
					echo "<td>" . $results["Suffix"] . "</td>";
					echo "<td>" . $results["SecondaryName"] . "</td>";
					echo "<td>" . $results["AddressLine1"] . "</td>";
					echo "<td>" . $results["AddressLine2"] . "</td>";
					echo "<td>" . $results["City"] . "</td>";
					echo "<td>" . $results["State"] . "</td>";
					echo "<td>" . $results["Zip"] . "</td>";
					echo "<td>" . $results["Country"] . "</td>";
					echo "<td>" . $results["ID"] . "</td>";
					echo "</tr>";
				} ?>-->
			</tbody>
		</table>
	</body>
</html>

<script type="text/javascript">
    var fields = JSON.stringify(<?php echo json_encode($fullFieldNames)?>);
    var fieldsParse = JSON.parse(fields);
    var data = [
        'Actions',
        'CompanyName',
        'FirstName',
        'MiddleInitial',
        'LastName',
        'Suffix',
        'SecondaryName',
        'AddressLine1',
        'AddressLine2',
        'City',
        'State',
        'Zip',
        'Country',
        'ID',
        'CRRT',
        'DP3'
    ];

    for(i = 0; i < fieldsParse.length; ++i) {
        data.push(fieldsParse[i]);
    }
	$('#results').DataTable({
		"processing": true,
		//"serverSide": true,
		"ajax" : {
		    url : "get_results.php",
			type: "GET",
			data: {filterStatement: "<?php echo $filterStatement ?>", fields: JSON.stringify(<?php echo json_encode($fullFieldNames) ?>)}
		},
		"columns": data/*[
            { data: 'Actions' },
			{ data:	'CompanyName' },
			{ data: 'FirstName' },
			{ data: 'MiddleInitial' },
			{ data: 'LastName' },
			{ data: 'Suffix' },
			{ data: 'SecondaryName' },
			{ data: 'AddressLine1' },
			{ data: 'AddressLine2' },
			{ data: 'City' },
			{ data: 'State' },
			{ data: 'Zip' },
			{ data: 'Country' },
			{ data: 'ID' },
            { data: 'CRRT' },
            { data: 'DP3' }
		]*/
	});
	/*$.ajax({
        type: 'GET',
        dataType: 'json',
        url: 'get_results.php',
        data: {
            filterStatement: "<?php echo $filterStatement?>",
            fields: JSON.stringify(<?php echo json_encode($fullFieldNames)?>)
        },
        success: function(d) {
            $('#results').DataTable({
               // dom: "Bfrtip",
                data: d.data,
                columns: d.columns
            });
        }
    });*/
	/*$("#results").DataTable({
        "ajax": {
            url: "get_results.php",
            type: "GET",
            data: {filterStatement: "<php echo $filterStatement ?>", fields: "<php json_encode($fullFieldNames)?>"}
        }/*,
		"aoColumns": [
			 { "mData": "Actions" },
			 { "mData": 'CompanyName' },
			 { "mData": 'FirstName' },
			 { "mData": 'MiddleInitial' },
			 { "mData": 'LastName' },
			 { "mData": 'Suffix' },
			 { "mData": 'SecondaryName' },
			 { "mData": 'AddressLine1' },
			 { "mData": 'AddressLine2' },
			 { "mData": 'City' },
			 { "mData": 'State' },
			 { "mData": 'Zip' },
			 { "mData": 'Country' },
			 { "mData": 'ID' },
			 { "mData": 'CRRT' },
			 { "mData": 'DP3' }
		 ]*/

</script>