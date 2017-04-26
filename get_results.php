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

$filterStatement = $_GET['filterStatement'];
$filterQuery = mysqli_query($link, $filterStatement);
$fields = json_decode($_GET['fields']);
$datatablesFields = ['Actions', 'CompanyName', 'FirstName', 'MiddleInitial', 'LastName', 'Suffix', 'SecondaryName',
                        'AddressLine1', 'AddressLine2', 'City', 'State', 'Zip', 'Country', 'CRRT', 'DP3'];



$results = array();
$return = array();
if($filterQuery && $filterQuery->num_rows > 0) {
    while($row = mysqli_fetch_assoc($filterQuery)) {
        array_push($results, $row);
    }
}

$return['data'] = array();
$return['columns'] = array();

for($i = 0; $i < sizeOf($results); ++$i) {
    $row = array();

    //Handle standard export fields first
    $row['Actions'] = json_encode("<input type='button' value='Action'/>");

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
        $row['CompanyName'] = $results[$i]['CompanyName'];
        $row['FirstName'] = $results[$i]['FirstName'];
        $row['MiddleInitial'] = $results[$i]['MiddleInitial'];
        $row['LastName'] = $results[$i]['LastName'];
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

    //Now add user-selected query fields
    foreach($fields as $field) {
        $row["{$field}"] = $results[$i]["{$field}"];
        if(substr($field, -3) == 'min' || substr($field, -3) == 'max')
            $field = substr($field, 0, -4);
        else if(substr($field, -8) == 'checkbox')
            $field = substr($field, 0, -9);

        if(!in_array($field, $datatablesFields))
            array_push($datatablesFields, $field);

        $row["{$field}"] = $results[$i]["{$field}"];
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