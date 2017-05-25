<?php
/**
	This is the server side of a file importer that can be used for the Real Property or Board of Elections File Uploads.
	NOTE: file headers don't need to be in the same ORDER as the database headers, however every file header must have a corresponding column in 
		their designated table.
		This importer checks if this is the case.
*/
require("connection.php");

session_start();
$totalSize = 0;
$currentlyUploadedSize = 0;

/*
 * Array holding field names that should be indexed in the database
 * Includes any fields on general filter screen plus owner id and muni code
 */
$indexes = ['owner_id',
            'muni_code',
            'total_av',
            'land_av',
            'swis',
            'sch_code',
            'loc_zip',
            'full_market_value',
            'acres',
            'sqft',
            'land_value',
            'wf_feet',
            'land_type',
            'waterfront_type',
            'soil_rating',
            'agricultural_properties',
            'residential_properties',
            'residential_vacant_properties',
            'commercial_properties',
            'recreation_entertainment_properties',
            'community_properties',
            'manufacturing_properties',
            'infrastructure_properties',
            'state_owned_properties',
            'zoning_cd',
            'sewer_type',
            'nbhd_rating',
            'air_cond',
            'yr_blt',
            'yr_remodeled',
            'heat_type',
            'fuel_type',
            'overall_cond',
            'ext_wall_material',
            'used_as_cd',
            'pools',
            'farm_barn',
            'mobile_home',
            'sheds',
            'patios',
            'tennis',
            'garages',
            'canopy_roofover',
            'porches',
            'golf',
            'cold_storage'
            ];

function createTable($fileHeaders, $databaseTable) {
	$return = "CREATE TABLE " . $databaseTable . " (primaryID INT NOT NULL AUTO_INCREMENT, ";
	
	foreach($fileHeaders as $f) {
		$return .= strtolower($f) . " VARCHAR(50), ";
	}
	
	$return .= "PRIMARY KEY (primaryID));";
	
	return $return;
}

function addIndexes($fileHeaders, $databaseTable, $indexes, $link) {
    foreach($fileHeaders as $f) {
        if (in_array($f, $indexes)) {
            $createIndexStatement = "CREATE INDEX {$f} ON {$databaseTable} ({$f});";
            $createIndexResult = mysqli_query($link, $createIndexStatement);
        }
    }
}

/**
	Creates the LOAD DATA LOCAL INFILE statement for a file
*/
function createLoadStatement($fileHeaders, $filename, $databaseTable) {
	
	$return = "LOAD DATA LOCAL INFILE '" . $filename . "' INTO TABLE " . $databaseTable . " FIELDS TERMINATED BY '\\t' LINES TERMINATED BY '\\n' IGNORE 1 LINES(";
				
	foreach($fileHeaders as $f) {
		$return .= $f . ", ";
	}
	
	//Above loop leaves a trailing ', ' (comma space) so this removes it
	$return = substr($return, 0, -2);
	
	//Now we specify the values to be inserted
	$return .= ");";

	return $return;
}

/*
	Calculate percentage (as a function of total upload size) of file upload left.
	Also updates currentlyUploadedSize.
*/
function calcUploadPercentage($currentFileSize) {
	$currentlyUploadedSize += $currentFileSize;
	return ($currentlyUploadedSize / $totalSize);
}

/*
	Get total size of files to be uploaded
*/

/*for($i = 0; $i < sizeOf($_FILES['uploadFile']['name']);  ++$i) {
	$totalSize += $_FILES['uploadFile']['size'][$i];
}*/

for($i = 0; $i < sizeOf($_FILES['uploadFile']['name']); ++$i) {
    $filename = $_FILES['uploadFile']['name'][$i];
    $tempPath = $_FILES['uploadFile']['tmp_name'][$i];
    $filesize = $_FILES['uploadFile']['size'][$i];
    $databaseTable = $_POST['county'] . '_' . $filename;
    $databaseTable = substr($databaseTable, 0, -4);


    /*****************
     * Move files into 'data' directory within application
     *****************/
    $upload_dir = 'data/' . ucfirst($_POST['county']) . '/';
    copy(($_FILES['uploadFile']['tmp_name'][$i]), $upload_dir . $_FILES['uploadFile']['name'][$i]);
    $localFile = $upload_dir . $_FILES['uploadFile']['name'][$i];

    //Open file to be uploaded ('countyName_fileName.txt')
    $importFile = fopen(realpath($localFile), "r") or die("Unable to open file.");

    //Check if file has existing table and drop if so
    $checkTable = mysqli_query($link, "SHOW TABLES LIKE '" . $databaseTable . "';");
    if (mysqli_num_rows($checkTable) > 0) {
        $getDatabaseTable = mysqli_query($link, "DROP TABLE " . $databaseTable);
    }

    //Retrieves header layout from first line of file to be uploaded (each field is delimited with a tab)
    $fileHeaders = fgets($importFile);
    $fileHeaders = explode("\t", $fileHeaders);

    //Create corresponding table based on file headers
    $createTableStatement = createTable($fileHeaders, $databaseTable);
    $checkTable = mysqli_query($link, $createTableStatement);
    if (!$checkTable) {
        print "Error creating " . $databaseTable . ".";
    }

    //Add indexes if necessary
    addIndexes($fileHeaders, $databaseTable, $indexes, $link);

    //Create the load data local infile statement
    $loadStatement = createLoadStatement($fileHeaders, $localFile, $databaseTable);
    /*echo "<br><br><br>";
    print "LOAD STATEMENT: " . $loadStatement;
    echo "<br><br><br>";*/

    $failedCount = mysqli_query($link, $loadStatement);
    $checkUpload = "SELECT COUNT(primaryID) FROM " . $databaseTable;
    $uploadCount = mysqli_query($link, $checkUpload) or die(mysqli_error($link));
    $uploadCounter = mysqli_fetch_assoc($uploadCount);


    /*
     * Now need to either add entry to last_updated or, if this county has an entry, change the last_updated date to today
     */
    $checkUpdated = "SELECT * from last_updated WHERE county='{$_POST['county']}';";

    $updateStatement = '';
    $today = date('Y/m/d');
    $result = mysqli_query($link, $checkUpdated);
    if ($result && $result->num_rows > 0) {
        $updateStatement = "UPDATE last_updated SET date='{$today}';";
    } else {
        $updateStatement = "INSERT INTO last_updated VALUES ('{$_POST['county']}', '{$today}');";
    }

    $updateStatementResult = mysqli_query($link, $updateStatement);
    if ($updateStatementResult)
        ;
    else
        print("Error saving last_updated:" . $updateStatementResult->error);
}
//mysqli_close($link);

echo "<script type='text/javascript'>
            window.location.href=\"index.php\";
            </script>";
?>