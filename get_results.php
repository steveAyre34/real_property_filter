<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/21/2017
 * Time: 11:20 AM
 */
//session_destroy();
session_start();
require("connection.php");
$dedupe = false;
$household = false;

if(!empty($_GET['dedupe']))
    $dedupe = true;
if(!empty($_GET['household']))
    $household = true;

if(!empty($_SESSION['codeTypes'])) {
    $codeTypes = $_SESSION['codeTypes'];
}
else
    $codeTypes = array();

if(!empty($_SESSION['defintionCodes'])) {
    $definitionCodes = $_SESSION['definitionCodes'];
}
else
    $definitionCodes = array();

$filterStatement = $_GET['filterStatement'];
$filterQuery = mysqli_query($link, $filterStatement);
$fields = json_decode($_GET['fields']);
$datatablesFields = ['Actions', 'CompanyName', 'FirstName', 'MiddleInitial', 'LastName', 'Suffix', 'SecondaryName',
                        'AddressLine1', 'AddressLine2', 'City', 'State', 'Zip', 'Country', 'CRRT', 'DP3', 'SWIS'];


/*$codeMeanings = array();

foreach($fields as $field) {
    if(!empty($codeTypes) && in_array($field, $codeTypes)) {
        $getCodeMeaningStatement = "SELECT meaning FROM codes WHERE type='{$field}' AND code='{$results[$i][$field]}';";
        $getCodeMeaningResult = mysqli_query($link, $getCodeMeaningStatement);
        if($getCodeMeaningResult && $getCodeMeaningResult->num_rows > 0) {
            $innerRow = mysqli_fetch_assoc($getCodeMeaningResult);
            $row["{$field}"] = $innerRow['meaning'];
        }
    }
}*/

$results = array();
$return = array();
if($filterQuery && $filterQuery->num_rows > 0) {
    while($row = mysqli_fetch_assoc($filterQuery)) {
        array_push($results, $row);
    }
}

$return['data'] = array();
$swis = array();
//$return['columns'] = array();

for($i = 0; $i < sizeOf($results); ++$i) {
    $row = array();

    //Handle standard export fields first

    /*
     * Filter out company names
     * Even though there is a secondary name category company names are stored in last name field
     * If first name and middle initial then last name is displayed as company name
     */
    if($results[$i]['FirstName'] == '' && $results[$i]['MiddleInitial'] == '') {
        $row['CompanyName'] = $results[$i]['LastName'];
        $row['FirstName'] = '';
        $row['MiddleInitial'] = '';
        $row['LastName'] = '';
    }
    else {
        //If this is a household query, change last name to 'The <LastName> Family'
        if(!empty($results[$i]['ID_COUNT_HOUSEHOLD']) && $results[$i]['ID_COUNT_HOUSEHOLD'] > 1 && $results[$i]['FIRSTNAME_COUNT_HOUSEHOLD'] > 1) {
            $row['CompanyName'] = $results[$i]['CompanyName'];
            $row['FirstName'] = '';
            $row['MiddleInitial'] = '';
            $row['LastName'] = 'The ' . $results[$i]['LastName'] . ' Family';
        }
        else {
            $row['CompanyName'] = $results[$i]['CompanyName'];
            $row['FirstName'] = $results[$i]['FirstName'];
            $row['MiddleInitial'] = $results[$i]['MiddleInitial'];
            $row['LastName'] = $results[$i]['LastName'];
        }
    }
    $row['Suffix'] = $results[$i]['Suffix'];
    $row['SecondaryName'] = $results[$i]['SecondaryName'];
    $row['AddressLine1'] = $results[$i]['AddressLine1'];
    $row['AddressLine2'] = $results[$i]['AddressLine2'];
    $row['City'] = $results[$i]['City'];
    $row['State'] = $results[$i]['State'];
    $row['Zip'] = $results[$i]['Zip'];
    $row['Country'] = $results[$i]['Country'];
    $row['CRRT'] = $results[$i]['CRRT'];
    $row['DP3'] = $results[$i]['DP3'];
    if(!array_key_exists($results[$i]['SWIS'], $swis)) {
        $getSwisMeaningStatement = "SELECT meaning FROM codes WHERE code='{$results[$i]['SWIS']}' AND type='swis';";
        $getSwisMeaningResult = mysqli_query($link, $getSwisMeaningStatement);
        if($getSwisMeaningResult && $getSwisMeaningResult->num_rows == 1) {
            $innerRow = mysqli_fetch_assoc($getSwisMeaningResult);
            $swis["{$results[$i]['SWIS']}"] = $innerRow['meaning'];
            $row['SWIS'] = $innerRow['meaning'];
        }
    }
    else
        $row['SWIS'] = $swis["{$results[$i]['SWIS']}"];

    //Now add user-selected query fields
    foreach($fields as $field) {
        if(!empty($results[$i]["{$field}"])) {
            $row["{$field}"] = $results[$i]["{$field}"];
            if(substr($field, -3) == 'min' || substr($field, -3) == 'max')
                $field = substr($field, 0, -4);
            else if(substr($field, -8) == 'checkbox')
                $field = substr($field, 0, -9);

            if(!in_array($field, $datatablesFields))
                array_push($datatablesFields, $field);


            /*
             * If the current field is a code then substitute it with it's meaning (i.e., substitute SWIS code for SWIS label)
             */
            /*if(!empty($codeTypes) && in_array($field, $codeTypes)) {
                /*$getCodeMeaningStatement = "SELECT meaning FROM codes WHERE type='{$field}' AND code='{$results[$i][$field]}';";
                $getCodeMeaningResult = mysqli_query($link, $getCodeMeaningStatement);
                if($getCodeMeaningResult && $getCodeMeaningResult->num_rows > 0) {
                    $innerRow = mysqli_fetch_assoc($getCodeMeaningResult);
                    $row["{$field}"] = $innerRow['meaning'];
                }
                $row["{$field}"] = $results[$i]['meaning'];
            }*/
            if(!empty($results[$i]['meaning'])) {
                $row["{$field}"] = $results[$i]['meaning'];
            }
            /*
             * If the current field is a definition then do the same thing we did with codes
             */
            /*else if(!empty($definitionCodes) && in_array($field, $definitionCodes)) {
                $query = "SHOW TABLES LIKE '%def%'";
                if($result = mysqli_query($link, $query)) {
                    while($innerRow = $result->fetch_assoc()) {
                        foreach($innerRow as $key => $value) {
                            //Only need the def file for specified county
                            if(strpos($value, $county) == 0) {
                                $innerQuery = "SHOW COLUMNS IN " . $value . " LIKE '%code%';";
                                if($innerResult = mysqli_query($link, $innerQuery)) {
                                    while($innerRowTwo = $innerResult->fetch_assoc()) {
                                        if($innerRowTwo['Field'] != "muni_code" && !in_array($innerRowTwo['Field'], $definitionCodes)) {
                                            $row["{$field}"] = $innerRowTwo['Field'];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }*/
            else {
                $row["{$field}"] = $results[$i]["{$field}"];
            }
        }
        else {
            if(substr($field, -3) == 'min' || substr($field, -3) == 'max')
                $field = substr($field, 0, -4);
            else if(substr($field, -8) == 'checkbox')
                $field = substr($field, 0, -9);

            if(!in_array($field, $datatablesFields))
                array_push($datatablesFields, $field);
        }
    }

    //Check all fields for unnecessary quotation marks
   foreach($row as $key => $value) {
        if(strpos($value, '"') !== FALSE) {
            $row[$key] = str_replace('"', '', $value);
        }
    }
    array_push($return['data'], $row);
}
//$_SESSION['datatablesFields'] = $datatablesFields;
echo json_encode($return);
?>