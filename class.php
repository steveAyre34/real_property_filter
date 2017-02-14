<?php
/*Key-value array for class table
	Key = column name
	Value = max length (specified in file_layout_2016)
*/
/*$tableHeaders = array(
	"muni_code" 				=> 		6,
	"parcel_id"					=>		10,
	"tax_cd"					=>		1,
	"apportionment_pct_land"	=>		5,
	"apportionment_pct_total"	=>		5,
	"reval_land_value"			=>		12,
	"reval_total_value"			=>		12,
	"disclosure_total"			=>		12,
	"acres"						=>		7,
	"land_av"					=>		12,
	"total_av"					=>		12,
	"co_taxable"				=>		12,
	"muni_taxable"				=>		12,
	"sch_taxable"				=>		12,
	"vlg_taxable"				=>		12,
	"timestamp"					=>		15
);*/
function getClassFileHeaders($file, $databaseTableHeaders) {
	$classFile = fopen($file, "r") or die("Unable to open " . $classFile);
	$fileHeaders = fgets($classFile);
	$fileHeaders = explode("\t", $fileHeaders);
	
	return $fileHeaders;
	
}

//Appends data from one file entry to the insert statement and is sent back to import, which executes the insert statement
//The number of characters read for each column is reflected in file_layout_2016
function addOneFileLineToDB($insertStatement, $fileLine) {
	
}