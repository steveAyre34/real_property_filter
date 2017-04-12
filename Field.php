<?php

/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/12/2017
 * Time: 2:00 PM
 */
/*
    * Class to hold field names along with values for the field's dropdown select menu
    * Seems like this is an easier way to link the dropdown menu values with a field name rather than using an associative array
    */
class Field
{
    //Matches database field name exactly
    public $fieldName;

    //Field name for querying purposes (includes table name)
    //Format: county_tableName.fieldName
    public $fullFieldName;
    //For template maker to later determine what HTML element to generate to search on this field
    //0 == min/max input boxes, 1 == multiSelectCheckBox, 2 == checkbox
    public $generateType = '';

    public function __construct($field, $table, $conn, $minMax, $checkbox) {
        $this->fieldName = $field;
        $this->fullFieldName = $table . "." . $field;
        $this->generateType = -1;

        /*
         * Check if the field is a code or from a definition table
         * If it is, no need to check generateType b/c these are always multiselect checkbox menus
         */
        if(in_array($field, $_SESSION['codeTypes']) || in_array($field, $_SESSION['definitionCodes'])) {
            $this->generateType = 1;
        }
        /*
         * If the field is not from a code or definiton table, determine if generateType should be checkbox or min/max
         * using keywords in global arrays (top of file)
         */
        else {
            //First preg_match to see if field matches a keyword for min/max
            for($i = 0; $i < sizeOf($minMax); ++$i) {
                //Matches, set generateType = 0 for min/max
                if(preg_match($minMax[$i], $field)) {
                    $this->generateType = 0;
                    break;
                }
            }
            //If didn't match min/max, generateType still equals ''
            if($this->generateType == -1) {
                //Preg_match to see if field matches a keyword for checkbox
                for($i = 0; $i < sizeOf($checkbox); ++$i) {
                    //Matches, set generateType = 2 for checkbox
                    if(preg_match($checkbox[$i], $field)) {
                        $this->generateType = 2;
                        break;
                    }
                }
            }
        }

        //If generateType is still '', then field isn't a code/definition
        //Also doesn't match a min/max or checkbox keyword
        //So generateType should be multiselect checkbox menu (1)
        if($this->generateType == -1) {
            $this->generateType = 1;
        }
    }
}