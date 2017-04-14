<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/12/2017
 * Time: 1:22 PM
 */
    require_once("Field.php");
    include("connection.php");
    session_start();
    $cache_ext = '.php';
    $cache_folder = "views/" . $_POST['county'] . "/";
    $dynamic_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
    $cache_file = '';//$cache_folder.md5($dynamic_url) . $cache_ext;
    $ignore_pages = array('', '');
    $county = $_POST['county'];
    $queryName = $_POST['query_name'];
    $saved = $_POST['saved'];

    /*
     * Block to handle saved queries
     * A query marked as saved can still be a 'new' query if it doesn't have an entry in saved_queries
     */
    if($saved == 1) {
        /*
         * Check saved queries to see if this is a previously saved query being used, or a new query that needs to be
         * saved for later
         */
        $checkStatement = "SELECT * FROM saved_queries WHERE name='{$queryName}' AND county='{$county}';";
        $saveQueryStatement = '';

        $result = mysqli_query($link, $checkStatement);
        //Entry in saved_queries exists
        if($result && $result->num_rows > 0) {
            $cacheUpdatedStatement = "SELECT last_cached FROM saved_queries WHERE name='{$queryName}' AND county='{$county}';";
            $cacheUpdatedResult = mysqli_query($link, $cacheUpdatedStatement);
            if($cacheUpdatedResult && $cacheUpdatedResult->num_rows > 0) {
                $row = $cacheUpdatedResult->fetch_assoc();
                $cacheUpdated = $row['date'];
            }
            //Get the date of the last import for current county
            $lastUpdatedStatement = "SELECT date FROM last_updated WHERE county='{$county}';";
            $lastUpdatedResult = mysqli_query($link, $lastUpdatedStatement);
            if($lastUpdatedResult && $lastUpdatedResult->num_rows > 0) {
                $row = $lastUpdatedResult->fetch_assoc();
                $lastUpdated = $row['date'];
            }

            //Cached page is up-to-date
            if($cacheUpdated > $lastUpdated) {
                //Get cached filename from saved_queries (includes file extension so no need to append)
                $cachedFilenameStatement = "SELECT cache_file FROM saved_queries WHERE name='{$queryName}' AND county='{$county}';";
                $cachedFilenameResult = mysqli_query($link, $cachedFilenameStatement);
                if($cachedFilenameResult && $cachedFilenameResult->num_rows > 0) {
                    $row = $cachedFilenameResult->fetch_assoc();
                    $cache_file = $row['cache_file'];
                }

                //Load cached page
                $ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;

                if(!$ignore && file_exists($cache_file)) {
                    ob_start('ob_gzhandler');
                    readfile($cache_file);
                    ob_end_flush();
                    exit();
                }
            }
            //Cached page is not up-to-date
            //Create new cached page using the filter categories stored in saved_queries
            else {
                $filterCategoryStatement = "SELECT filter_categories FROM saved_queries WHERE name='{$county}' AND county='{$county}';";
                $filterCategoryResult = mysqli_query($link, $filterCategoryStatement);
                if($filterCategoryResult && $filterCategoryResult->num_rows > 0) {
                    $row = $filterCategoryResult->fetch_assoc();
                    $filterCategoryString = $row['filter_categories'];
                }
                //Filter categories are stored as one string separated by ", " (comma, space)
                //So we will separate them into an array of categories
                $templateFields = explode(", ", $filterCategoryString);

                //Create new cached page name
                $cache_file = $cache_folder . md5($dynamic_url) . $cache_ext;

                //Update last_cached to today's date and cache_file to new filename
                $today = date('Y/m/d');
                $updateLastCachedStatement = "UPDATE saved_queries SET last_cached='{$today}', cache_file='{$cache_file}' WHERE name='{$queryName}' ";
                $updateLastCachedStatement .= "AND county='{$county}';";
                $ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;
                ob_start('ob_gzhandler');
            }
        }
    }
        //Entry in saved_queries does not exist so we are creating a new page from scratch
        else {
            $today = date("Y/m/d");
            //Need to concatenate template fields into one string so it can be inserted into database
            $templateFieldsString = '';
            foreach($template_fields as $temp) {
                $templateFieldsString .= "'{$temp}', ";
            }
            //Remove trailing comma and space
            $templateFieldsString = substr($templateFieldsString, 0, -2);

            $saveQueryStatement = "INSERT INTO saved_queries ('name', 'county', 'last_cached', 'cache_file', 'filter_categories') VALUES('{$queryName}',";
            $saveQueryStatement .= " '{$county}', '{$today}', '{$cache_file}', '{$templateFieldsString}');";
            //print("Insert: " . $saveQueryStatement . "<br>");
        }

        //print("<script type='text/javascript'>console.log(\"Save: {$saveQueryStatement}\");</script>");
        $saveResult = mysqli_query($link, $saveQueryStatement);
        if($saveResult === FALSE)
            print("Error saving query.");

        $template_fields = array();

        foreach($_POST['template_list'] as $template_list) {
            //Get table name from full field name
            $table = explode(".", $template_list);
            //Table name will always be first element prior to the period in the full field name
            $table = $table[0];

            for($i = 0; $i < sizeOf($_SESSION['fields'][$table]); ++$i) {
                if(strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) !== FALSE) {
                    if(strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) === 0) {
                        array_push($template_fields, $_SESSION['fields'][$table][$i]);
                        break;
                    }
                }
            }
        }

        print($county . "<br>");
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
        <?php foreach($template_fields as $fields) {} ?>


    </body>
</html>

<?php
if(!is_dir($cache_folder)) {
    mkdir($cache_folder);
}
if(!$ignore) {
    $fp = fopen($cache_file, 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);
}
ob_end_flush();
?>
