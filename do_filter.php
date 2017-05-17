<?php
	require("connection.php");
   // session_start();

	$county = $_POST['county'];
	$owner = $county . '_owner';
	$countRegular = 0;
	$countDeduped = 0;
	$countHouseholded = 0;

    $_POST = array_filter($_POST);


    $codes = false;
	if(!empty($_SESSION['codeTypes'])) {
        $codeTypes = $_SESSION['codeTypes'];
    }
	else
	    $codeTypes = array();

	if(!empty($_SESSION['definitionCodes']))
	    $definitionCodes = $_SESSION['definitionCodes'];
    else
        $definitionCodes = array();
	/*
	 * Array that contains the field names of the standard fields to be exported
	 * This way if user is filtering by a standard field, it's not appended again to the export
	 * i.e. if user searches by zip code, then zip code column won't appear in export file twice
	 */
	$standardColumns = ["owner_id", "secondary_name", "owner_first_name", "owner_init_name", "owner_last_name",
		                "owner_name_suffix", "owner_secondary_name", "concatenated_address_1", "concatenated_address_2",
		                "mail_city", "owner_mail_state", "mail_zip", "mail_country", 'swis', 'crrt', 'dp3'];

	/*
	 * For some reason only Dutchess County stores the mailing address city and zip as 'owner_mail_city' and 'owner_mail_zip'
	 * All other counties (that I have data available for as of now) store city and zip as 'mail_city', 'mail_zip'
	 */
	if($county == 'dutchess') {
        $filterStatement = "SELECT {$owner}.owner_id AS ID, {$owner}.secondary_name AS CompanyName, {$owner}.owner_first_name AS FirstName, 
                            {$owner}.owner_init_name AS MiddleInitial, {$owner}.owner_last_name AS LastName, {$owner}.owner_name_suffix AS Suffix,
                            {$owner}.secondary_name AS SecondaryName, {$owner}.concatenated_address_1 as AddressLine1,
                            {$owner}.concatenated_address_2 as AddressLine2, {$owner}.owner_mail_city AS City, {$owner}.owner_mail_state AS State, 
                            {$owner}.owner_mail_zip AS Zip, {$owner}.mail_country AS Country, {$owner}.crrt AS CRRT, {$owner}.dp3 AS DP3, ";
    }
    else {
        $filterStatement = "SELECT {$owner}.owner_id AS ID, {$owner}.secondary_name AS CompanyName, {$owner}.owner_first_name AS FirstName,
        {$owner}.owner_init_name AS MiddleInitial, {$owner}.owner_last_name AS LastName, {$owner}.owner_name_suffix AS Suffix,
        {$owner}.secondary_name AS SecondaryName, {$owner}.concatenated_address_1 as AddressLine1, 
        {$owner}.concatenated_address_2 as AddressLine2, {$owner}.mail_city AS City, {$owner}.owner_mail_state AS State, {$owner}.mail_zip AS Zip,
        {$owner}.mail_country AS Country, {$owner}.crrt AS CRRT, {$owner}.dp3 AS DP3, {$county}_assessment.swis AS SWIS, ";
    }

	$householdedStatement = $filterStatement . "COUNT({$owner}.owner_id) AS ID_COUNT_HOUSEHOLD, COUNT({$owner}.owner_first_name) AS FIRSTNAME_COUNT_HOUSEHOLD, ";

	/*
	 * Now add the user-selected query fields to select statement
	 */
	foreach($_POST as $postKey => $postValue) {
	    if($postKey != 'county' && !empty($postValue)) {
            $key = str_replace("||", ".", $postKey);
	        //If field name ends in 'checkbox', remove '_checkbox'
            if(substr($postKey, -8) == 'checkbox') {
                $key = substr($key, 0, -9);
            }

            //If field name ends in 'min' or 'max', remove '_min' or '_max' accordingly
            else if((substr($postKey, -3) == 'min' || substr($postKey, -3) == 'max')) {//} && !empty($postValue)) {
                $key = substr($key, 0, -4);
            }

            $filterStatement .= "{$key}, ";
            $householdedStatement .= "{$key}, ";

            /*
             * Now check if key is a code or a definition
             * If so change key added to filter statement so that we are selecting the code/definition meaning instead of the code/def number
             */
            $field = explode('.', $key);
            $field = $field[1];
            if(in_array($field, $codeTypes)) {
                $key = "codes.meaning AS meaning";
                $filterStatement .= "{$key}, ";
                $householdedStatement .= "{$key}, ";
                $codes = true;
            }
        }
    }
    $filterStatement = substr($filterStatement, 0, -2);
	//$dedupedStatement = substr($dedupedStatement, 0, -2);
	$householdedStatement = substr($householdedStatement, 0, -2);

	if($codes) {
        $filterStatement .= " FROM codes, {$county}_assessment, {$owner} ";//JOIN {$county}_assessment ON ({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) ";
        $householdedStatement .= " FROM codes, {$county}_assessment, {$owner} ";//JOIN {$county}_assessment ON ({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) ";
    }
    else {
        $filterStatement .= " FROM {$county}_assessment, {$owner} ";// JOIN {$county}_assessment ON ({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) ";
        $householdedStatement .= " FROM {$county}_assessment, {$owner} ";// JOIN {$county}_assessment ON ({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) ";
	}

	$tablesAddedToStatement = array();
	$fullFieldNames = array();
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
			if($table != "${county}_owner" && !in_array($table, $tablesAddedToStatement) && strpos($table, 'def') === FALSE
            && strpos($table, 'assessment') === FALSE) {
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

                    $householdedStatement .= "JOIN {$table} ON ({$county}_owner.owner_id={$table}.owner_id AND ";
                    $householdedStatement .= " {$county}_owner.muni_code={$table}.muni_code) ";
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
                        $householdedStatement .= "JOIN {$table} ON ({$county}_owner.muni_code={$table}.muni_code AND {$county}_owner.parcel_id={$table}.parcel_id) ";
                    }
                }
            }
		}
	}
	//Remove trailing space to be neat because I feel like it
	$filterStatement = substr($filterStatement, 0, -1);

	$householdedStatement = substr($householdedStatement, 0, -1);


/*
 * Construct the where clauses
 */
	$filterStatement .= " WHERE (({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) AND " ;
	$householdedStatement .= " WHERE (({$owner}.owner_id={$county}_assessment.owner_id AND {$owner}.muni_code={$county}_assessment.muni_code) AND " ;

	foreach($_POST as $postKey => $postValue) {
        $fullField = str_replace('||', '.', $postKey);
        $fieldName = explode('.', $fullField);
		if($postKey != 'county' && !empty($postValue)) {
            $fieldName = $fieldName[1];
            array_push($fullFieldNames, $fieldName);
        }

        if(in_array($fieldName, $codeTypes) && $fieldName != 'swis') {
		    /*if($fieldName == 'swis') {
		        $fieldValue = "0{$postValue[0]}";
            }
            else*/
                $fieldValue = $postValue[0];
		    $filterStatement .= "(codes.code='{$fieldValue}' AND codes.type='{$fieldName}') AND ";
            $householdedStatement .= "(codes.code='{$fieldValue}' AND (codes.county='" . ucfirst($county) . "' OR codes.county='all') AND codes.type='{$fieldName}') AND ";
        }
		if(!empty($postValue) && $postKey != 'county') {
			//Multiple values selected for this field
            if(sizeOf($postValue) > 1) {
            	$filterStatement .= "(";
				foreach($postValue as $value) {
					if($value != '') {
                        $filterStatement .= "{$fullField}='{$value}' OR ";
                        $householdedStatement .= "{$fullField}='{$value}' OR ";
                    }
				}
				//Remove trailing ' OR ' (space OR space = 4 characters)
				$filterStatement = substr($filterStatement, 0, -4);
				$filterStatement .= ") AND ";

                $householdedStatement = substr($householdedStatement, 0, -4);
                $householdedStatement .= ") AND ";
			}
			else {
            	if(!empty($postValue[0])) {
                    //Field is a min field
                    if (substr($fullField, -3) == 'min') {
                        $filterStatement .= "(" . substr($fullField, 0, -4) . ">='{$postValue[0]}||{$postValue[1]}') AND ";
                        $householdedStatement .= "(" . substr($fullField, 0, -4) . ">='{$postValue[0]}') AND ";
                    }
                    else if (substr($fullField, -3) == 'max') {
                        $filterStatement .= "(" . substr($fullField, 0, -4) . "<='{$postValue[0]}') AND ";
                        $householdedStatement .= "(" . substr($fullField, 0, -4) . "<='{$postValue[0]}') AND ";
                    }
                    else {
                        $filterStatement .= "({$fullField}='{$postValue[0]}') AND ";
                        $householdedStatement .= "({$fullField}='{$postValue[0]}') AND ";
                    }
                }
            }
		}
		//Checkbox values won't have a post value but will have a key name containing 'check'
        else {
            if (substr($fullField, -8) == 'checkbox') {
                $filterStatement .= "(" . substr($fullField, 0, -9) . ">'0') AND ";
                $householdedStatement .= "(" . substr($fullField, 0, -9) . ">'0') AND ";
            }
        }
	}

    //If there were no parameters selected then give the standard export for entire county, so need to remove WHERE
    if(empty($fullFieldNames)) {
	    $filterStatement = substr($filterStatement, 0, -7);
        $householdedStatement = substr($householdedStatement, 0, -7);
    }
    else {
        //Remove trailing ' AND ' (space AND space = 5 characters)
        $filterStatement = substr($filterStatement, 0, -5);
        $filterStatement .= ")";

        $householdedStatement = substr($householdedStatement, 0, -5);
        $householdedStatement .= ")";
    }

    //Create deduped and householded statements for possible later use
    $dedupedStatement = "{$filterStatement} GROUP BY FirstName, LastName, CONCAT(AddressLine1, ', ', City, ', ', State, ' ', Zip);";
    $householdedStatement = "{$householdedStatement} GROUP BY LastName, CONCAT(AddressLine1, ', ', City, ', ', State, ' ', Zip);";
    $filterStatement .= ";";

    $countRegularResult = mysqli_query($link, $filterStatement);
    if($countRegularResult) {
        $countRegular = $countRegularResult->num_rows;
    }

    $countDedupeResult = mysqli_query($link, $dedupedStatement);
    if($countDedupeResult && $countDedupeResult->num_rows > 0) {
        $countDeduped = $countDedupeResult->num_rows;
    }

    $countHouseholdResult = mysqli_query($link, $householdedStatement);
    if($countHouseholdResult && $countHouseholdResult->num_rows >0) {
        $countHouseholded = $countHouseholdResult->num_rows;
    }

    $filterStatement = preg_replace('/\t+/', '',$filterStatement);
    //print("{$filterStatement}<br>");
?>

<html>
	<head>
        <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.15/css/jquery.dataTables.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css"/>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.3.1/css/buttons.dataTables.min.css"/>
        <link rel="stylesheet" type="text/css" href="common.css"/>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
		<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.15/js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
        <script src='pdfmake-master/pdfmake-master/build/pdfmake.min.js'></script>
        <script src='pdfmake-master/pdfmake-master/build/vfs_fonts.js'></script>
        <script src='jszip/Stuk-jszip-ab3829a/dist/jszip.min.js'></script>
	</head>
	<body>
    <h4 style="display:inline-block">Standard Count: <?php echo $countRegular ?> &nbsp</h4><button id="standard" style="display:inline-block" class="ui-button" onclick="standardResults()">Results</button><br><br>
    <h4 style="display:inline-block">Dedupded Count: <?php echo $countDeduped ?> &nbsp</h4><button id="dedupe" style="display:inline-block" class="ui-button" onclick="dedupeResults()">Results</button><br><br>
    <h4 style="display:inline-block">Householded Count: <?php echo $countHouseholded ?> &nbsp</h4><button id="household" style="display:inline-block" class="ui-button" onclick="householdResults()">Results</button><br><br>
		<table id="results" class="resultsTable">
			<thead>
				<tr>
					<!--Headers for the standard export fields-->
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
					<th>CRRT</th>
					<th>DP3</th>
                    <th>SWIS</th>
					<!--Now headers for any selected fields that aren't a standard export field -->
					<?php foreach($fullFieldNames as $fields) {
   		 					if (!in_array($fields, $standardColumns)) {
                                if (substr($fields, -3) == 'min' || substr($fields, -3) == 'max') {
                                    $temp = substr($fields, 0, -3);
                                    print("<th>{$temp}</th>");
                                } else if (substr($fields, -8) == 'checkbox') {
                                    $temp = substr($fields, 0, -8);
                                    print("<th>{$temp}</th>");
                                } else {
                                    print("<th>{$fields}</th>");
                                }
                            }
					} ?>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</body>
</html>

<script type="text/javascript">
    var columns = [
        { data: 'CompanyName' },
        { data: 'FirstName' },
        { data: 'MiddleInitial' },
        { data: 'LastName' },
        { data: 'Suffix' },
        { data: 'SecondaryName'},
        { data: 'AddressLine1' },
        { data: 'AddressLine2' },
        { data: 'City' },
        { data: 'State' },
        { data: 'Zip' },
        { data: 'Country' },
        { data: 'CRRT' },
        { data: 'DP3' },
        { data: 'SWIS' }
    ];
    //$("#results").hide();

    <?php foreach($fullFieldNames as $fields) {
        if(!in_array($fields, $standardColumns)) {
            if(substr($fields, -3) == 'min' || substr($fields, -3) == 'max') {?>
            columns.push({data: '<?php echo substr($fields, 0, -4) ?>'});
            <?php }
            else if(substr($fields, -8) == 'checkbox') {
            ?>
            columns.push({data: '<?php echo substr($fields, 0, -9) ?>'});
            <?php }
            else { ?>
            columns.push({data: '<?php echo $fields ?>'});
            <?php }
        }
    }?>

	function standardResults() {
        if($.fn.DataTable.isDataTable('#results')) {
            $('#results').DataTable().destroy();
        }
	    $('#results').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": 0,
            "ajax" : {
                url : "get_results.php",
                type: "GET",
                data: {filterStatement: "<?php echo $filterStatement ?>", fields: JSON.stringify(<?php echo json_encode($fullFieldNames) ?>)}
            },
            columns: columns,
            dom: 'Bfrtip',
            buttons: [
                //{ extend: 'copyHtml5', exportOptions: { columns: 'contains("Office")'}},
                { extend: 'excelHtml5', title: '<?php echo $county ?>_export' }]
                //{ extend: 'csvHtml5', title: '<php echo $county ?>_export' },
                //{ extend: 'pdfHtml5', title: '<php echo $county ?>_export' }]
        });
	    $('#results').show();
    }

	function dedupeResults() {
        if($.fn.DataTable.isDataTable('#results')) {
            $('#results').DataTable().destroy();
        }

            $('#results').DataTable({
                "processing": true,
                "serverSide": true,
                "deferLoading": 0,
                ajax: {
                    url: "get_results.php",
                    type: "GET",
                    data: {
                        filterStatement: "<?php echo $dedupedStatement ?>",
                        fields: JSON.stringify(<?php echo json_encode($fullFieldNames) ?>)
                        //dedupe: true
                    }
                },
                columns: columns,
                dom: 'Bfrtip',
                buttons: [
                    //{ extend: 'copyHtml5', exportOptions: { columns: 'contains("Office")'}},
                    {extend: 'excelHtml5', title: '<?php echo $county ?>_export'}]
                //{ extend: 'csvHtml5', title: '<php echo $county ?>_export' },
                //{ extend: 'pdfHtml5', title: '<php echo $county ?>_export' }]
            });
            $('#results').show();
    }

    function householdResults() {
        if($.fn.DataTable.isDataTable('#results')) {
            $('#results').DataTable().destroy();
        }

            $('#results').DataTable({
                "processing": true,
                "serverSide": true,
                "deferLoading": 0,
                ajax: {
                    url: "get_results.php",
                    type: "GET",
                    data: {
                        filterStatement: "<?php echo $householdedStatement ?>",
                        fields: JSON.stringify(<?php echo json_encode($fullFieldNames) ?>)
                        //household: true
                    }
                },
                columns: columns,
                dom: 'Bfrtip',
                buttons: [
                    //{ extend: 'copyHtml5', exportOptions: { columns: 'contains("Office")'}},
                    {extend: 'excelHtml5', title: '<?php echo $county ?>_export'}]
                //{ extend: 'csvHtml5', title: '<php echo $county ?>_export' },
                //{ extend: 'pdfHtml5', title: '<php echo $county ?>_export' }]
            });
            $('#results').show();
    }
</script>
