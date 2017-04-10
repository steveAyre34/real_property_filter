<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/5/2017
 * Time: 4:27 PM
 */
include("connection.php");
$county = $_GET['county'];
$owner = $county . '_owner';
$query =    "SELECT {$owner}.owner_id AS ID, {$owner}.secondary_name AS CompanyName, {$owner}.owner_first_name AS FirstName, ";
$query .=   "{$owner}.owner_init_name AS MiddleInitial, {$owner}.owner_last_name AS LastName, {$owner}.owner_name_suffix AS Suffix, ";
$query .=   "{$owner}.secondary_name AS SecondaryName, ";
$query .=	"{$owner}.concatenated_address_1 as AddressLine1, {$owner}.concatenated_address_2 as AddressLine2, ";
$query .=	"{$owner}.mail_city AS City, {$owner}.owner_mail_state AS State, {$owner}.mail_zip AS Zip, ";
$query .=   "{$owner}.mail_country AS Country";

$query .= " FROM {$owner} GROUP BY {$owner}.owner_id, {$owner}.muni_code;";



$queryResult = mysqli_query($link, $query);

if($queryResult != false) {
    require_once('public/PHPExcel/Classes/PHPExcel.php');
    $objPHPExcel = new PHPExcel();
    //Set document properties
    $objPHPExcel->getProperties()
        ->setCreator("CRSTAdmin")
        ->setLastModifiedBy("CRSTAdmin")
        ->setTitle($county . "_export")
        ->setSubject("RP3 Filter Export for " . ucfirst($county) . " County")
        ->setDescription("This is the RP3 Filter Export for " . ucfirst($county) . " County");

    //Set default export headers
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'Owner ID')
                ->setCellValue('B1', 'Company Name')
                ->setCellValue('C1', 'First Name')
                ->setCellValue('D1', 'Middle Initial')
                ->setCellValue('E1', 'Last Name')
                ->setCellValue('F1', 'Suffix')
                ->setCellValue('G1', 'Secondary Name')
                ->setCellValue('H1', 'Address Line 1')
                ->setCellValue('I1', 'Address Line 2')
                ->setCellValue('J1', 'City')
                ->setCellValue('K1', 'State')
                ->setCellValue('L1', 'ZIP Code')
                ->setCellValue('M1', 'Country');

    //Counter to keep track of next row where to write data (first line of data is on A2, B2, etc., second line A3, B3, etc., etc.)
    $rowCounter = 2;
    while($dbResult = mysqli_fetch_assoc($queryResult)) {
        //First set everything but name fields
        //This is because company names are in last name field so we must account for this later
        $objPHPExcel->getActiveSheet()
                    //Owner ID
                    ->setCellValue('A' . $rowCounter, $dbResult['ID'])
                    //Secondary name (holds ATTNs, etc. if applicable
                    ->setCellValue('G' . $rowCounter, $dbResult['SecondaryName'])
                    //Suffix (Jr, Sr, etc.)
                    ->setCellValue('F' . $rowCounter, $dbResult['Suffix'])
                    //Concatenated Address 1
                    ->setCellValue('H' . $rowCounter, $dbResult['AddressLine1'])
                    //Concatenated Address 2
                    ->setCellValue('I' . $rowCounter, $dbResult['AddressLine2'])
                    //Mail City
                    ->setCellValue('J' . $rowCounter, $dbResult['City'])
                    //Mail State
                    ->setCellValue('K' . $rowCounter, $dbResult['State'])
                    //Mail Zip
                    ->setCellValue('L' . $rowCounter, $dbResult['Zip'])
                    //Mail Country
                    ->setCellValue('M' . $rowCounter, $dbResult['Country']);

        //Now we will filter out company names
        //Company names are stored in LastName field
        //If FirstName and MiddleInitial are blank, then LastName becomes CompanyName and remaining name fields are left blank
        if($dbResult['FirstName'] == '' && $dbResult['MiddleInitial'] == '') {
            $objPHPExcel->getActiveSheet()
                        //Company Name
                        ->setCellValue('B' . $rowCounter, $dbResult['LastName'])
                        //First Name
                        ->setCellValue('C' . $rowCounter, '')
                        //Middle Initial
                        ->setCellValue('D' . $rowCounter, '')
                        //Last Name (blank, because company name is filled)
                        ->setCellValue('E' . $rowCounter, '');
        }
        //FirstName or MiddleInitial (or both) are not blank, so leave as-is
        else {
            $objPHPExcel->getActiveSheet()
                //Company Name
                ->setCellValue('B' . $rowCounter, $dbResult['CompanyName'])
                ->setCellValue('C' . $rowCounter, $dbResult['FirstName'])
                ->setCellValue('D' . $rowCounter, $dbResult['MiddleInitial'])
                ->setCellValue('E' . $rowCounter, $dbResult['LastName']);
        }
        ++$rowCounter;
    }
    //Save as Excel 2007
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save(str_replace('.php', '.xlsx', $county . '_export.php'));
}
else
    print("0 records match your query.");
