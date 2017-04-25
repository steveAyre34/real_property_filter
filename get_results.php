<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/21/2017
 * Time: 11:20 AM
 */
require("connection.php");
$filterStatement = $_GET['filterStatement'];
$filterQuery = mysqli_query($link, $filterStatement);
$fields = json_decode($_GET['fields']);
//print($_GET['filterStatement'] . "<br>");
//print_r($fields);
//echo "<br>";

//$results = array();
$results = array();
$return = array();
if($filterQuery && $filterQuery->num_rows > 0) {
    while($row = mysqli_fetch_assoc($filterQuery)) {
        array_push($results, $row);
    }
}
/*else {
    print("Error: " . mysqli_error($link));
}*/
//echo json_encode($results['data']);
//print_r($results['data']);
$return['data'] = array();
$return['columns'] = array();

/*$data['columns'] = array();
$data['columns'] = array("title" => "field");
$data['columns']['data'] = array();*/

for($i = 0; $i < sizeOf($results); ++$i) {
    $row = array();

    //Handle standard export fields first
    $row['Actions'] = json_encode("<input type='button' value='Action'/>");

    /*
     * Filter out company names
     * Even though there is a secondary name category company names are stored in last name field
     * If first name and middle initial then last name is displayed as company name
     */
    if($results[$i]['CompanyName'] == '' && $results[$i]['FirstName'] == '' && $results[$i]['MiddleInitial'] == '') {
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
    $row['ID'] = $results[$i]['ID'];
    $row['CRRT'] = $results[$i]['CRRT'];
    $row['DP3'] = $results[$i]['DP3'];

    //Now add user-selected query fields
    foreach($fields as $field) {
        $row["{$field}"] = $results[$i]["{$field}"];
        /*if(!in_array($field, $data['columns']['title'])) {
            array_push($data['columns']['title'], $field);
            array_push($data['columns']['data'], $field);
        }*/
    }
    //$data['columns'] = json_encode(array_combine($data['columns']['title'], $data['columns']['data']));
    //Check all fields for unnecessary quotation marks
    foreach($row as $key => $value) {
        if(strpos($value, '"') !== FALSE) {
            $row[$key] = str_replace('"', '', $value);
        }
    }
    array_push($return['data'], $row);
}
//$data['data'] = json_encode($data['data']);
//Now need to get all the column names so they can be returned and used to build the DataTables result
$columns = array(
    ["title" => "Actions", "data" => "Actions"],
    ["title" => "CompanyName", "data" => "CompanyName"], ["title" => "FirstName","data" => "FirstName"],
    ["title" => "MiddleInitial", "data" => "MiddleInitial"], ["title" => "LastName", "data" => "LastName"],
    ["title" => "Suffix", "data" => "Suffix"], ["title" => "SecondaryName", "data" => "SecondaryName"],
    ["title" => "AddressLine1", "data" => "AddressLine1"], ["title" => "AddressLine2", "data" => "AddressLine2"],
    ["title" => "City", "data" => "City"], ["title" => "State", "data" => "State"], ["title" => "Zip", "data" => "Zip"],
    ["title" => "Country", "data" => "Country"], ["title" => "ID", "data" => "ID"], ["title" => "CRRT", "data" => "CRRT"],
    ["title" => "DP3", "data" => "DP3"]
);

/*foreach($fields as $field) {
    array_push($columns, ["title" => "{$field}", "data" => "{$field}"]);
}
$return['columns'] = $columns;
/*echo json_encode($return['data']);
echo json_encode($return['columns']);*/
echo json_encode($return);
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
}*/
?>