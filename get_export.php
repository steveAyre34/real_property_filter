<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/5/2017
 * Time: 4:27 PM
 */
include("connection.php");
$county = $_POST['county'];
$owner = $county . '_owner';
$query =    "SELECT {$owner}.owner_id AS ID, {$owner}.secondary_name AS CompanyName, {$owner}.owner_first_name AS FirstName, ";
$query .=   "{$owner}.owner_init_name AS MiddleInitial, {$owner}.owner_last_name AS LastName, {$owner}.owner_name_suffix AS Suffix, ";
$query .=	"{$owner}.concatenated_address_1 as AddressLine1, {$owner}.concatenated_address_2 as AddressLine2, ";
$query .=	"{$owner}.mail_city AS City, {$owner}.owner_mail_state AS State, {$owner}.mail_zip AS Zip, ";
$query .=   "{$owner}.mail_country AS Country GROUP BY {$owner}.owner_id, {$owner}.muni_code;";

//echo $query . '<br>';

$queryResult = mysqli_query($link, $query);
if($queryResult != false) {
    $dbResult = mysqli_fetch_assoc($queryResult);
}
else
    print_r($queryResult);

