<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/12/2017
 * Time: 1:22 PM
 */
    $cache_ext = '.php';
    $cache_folder = 'views/' . _$POST['county'] . '/';
    $ignore_pages = array('', '');
    require_once("Field.php");
    session_start();
    //include("getTableSelect2.php");
    $county = $_POST['county'];


    $template_fields = array();

    foreach($_POST['template_list'] as $template_list) {
        //Get table name from full fiend name
        $table = explode(".", $template_list);
        //Table name will always be first element prior to the period in the full field name
        $table = $table[0];

        for($i = 0; $i < sizeOf($_SESSION['fields'][$table]); ++$i) {
            if(strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) !== FALSE) {
                //print_r($_SESSION['fields'][$table][$i]); echo "<br>";
                if(strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) === 0) {
                    array_push($template_fields, $_SESSION['fields'][$table][$i]);
                    break;
                }
            }
        }
    }

    print($county . "<br>");
   // print_r($_POST['template_list']);
    echo "<br>";

    foreach($template_fields as $temp) {
        print_r($temp);
        echo "<br>";
    }
?>
<html>
    <head>
        <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
        <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
        <script src="jquery.multiselect.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
        <link rel="stylesheet" href="jquery.multiselect.css"/>
    </head>
    <body>
        <?php foreach($template_fields as $fields) { ?>

        }
    </body>
</html>
