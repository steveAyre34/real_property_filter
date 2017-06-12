<?php

/**
 * Created by PhpStorm.
 * User: yarn23
 * Date: 4/16/17
 * Time: 4:33 PM
 */
include("connection.php");
/*
 * Class to hold county codes, type and meaning
 * Type = database field name (ex: sch_code)
 */
class Code {
    public $type;
    public $code;
    public $meaning;

    public function __construct($county, $type, $code, $meaning) {
        /*$this->type = $type;
        $this->code = $code;
        $this->meaning = $meaning;*/
        $this->type = $type;
        $this->code = $code;

        //Get the code meaning
        $getCodeMeaningStatement = "SELECT meaning FROM codes WHERE code='{$code}' AND (county='{$county}' OR county='all');";
        $getCodeMeaningResult = mysqli_query($link, $getCodeMeaningResult);
        if($getCodeMeaningResult && $getCodeMeaningResult->num_rows == 1) {
            $this->meaning = $getCodeMeaningResult['meaning'];
        }
    }
}