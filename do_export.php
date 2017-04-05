<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/5/2017
 * Time: 4:26 PM
 */
require_once('public/PHPExcel/Classes/PHPExcel.php');
//Set document properties
$objPHPExcel->getProperties()
    ->setCreator("CRSTAdmin")
    ->setLastModifiedBy("CRSTAdmin")
    ->setTitle($county . "_export")
    ->setSubject("RP3 Filter Export for " . ucfirst($county) . " County")
    ->setDescription("This is the RP3 Filter Export for " . ucfirst($county) . " County");