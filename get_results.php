<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/21/2017
 * Time: 11:20 AM
 */
require("connection.php");
$filterQuery = mysqli_query($link, $_GET['filterStatement']);
$fields = json_decode($_GET['fields']);

$results = array();
$results = array();
if($filterQuery && $filterQuery->num_rows > 0) {
    while($row = mysqli_fetch_assoc($filterQuery)) {
        array_push($results, $row);
    }
}
$mData['mData'] = array();

for($i = 0; $i < sizeOf($results); ++$i) {

}


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