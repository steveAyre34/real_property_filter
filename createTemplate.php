<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/12/2017
 * Time: 1:22 PM
 */
    require_once("Field.php");
    include("connection.php");
    include("select_logic.php");
    //session_start();

    /*
    * There are three ways to search on a field: multiselect checkbox, min/max input boxes, and a simple checkbox for
    * has/has not relationships (for example, check the box if you want to search on properties that "have AC").
    * In order to keep the filter dynamic that means we will need to check each field name for certain keywords in order
    * to determine which HTML element should be generated to allow the user to search by that category. The following
    * three global arrays contain the keywords for each of those scenarios based on an analysis of the database fields
    * and how they were searchable in the old RP2 filter. Contains wildcard expressions. Any field not in one of these
    * two arrays will generate a multiselect checkbox menu.
    */
    $minMax = [
        '/amt/',
        '/adj/',
        '/price/',
        '/.av/',
        '/value/',
        '/taxable/',
        '/value/',
        '/taxable/',
        '/total/',
        '/yr/',
        '/acres/',
        '/cost/',
        '/perimeter/',
        '/area/',
        '/height/',
        '/nbr/',
        '/sqft/',
        '/feet/',
        '/length/',
        '/width/'
    ];
    $checkbox = ['/pct/', '/percent/'];

    $cache_ext = '.php';
    $cache_folder = "views/" . $_POST['county'] . "/";

    $cache_file = '';
    $ignore_pages = array('', '');
    $saved = $_POST['saved'];
    $county = $_POST['county'];
    $queryName = $_POST['query_name'];


    $template_fields = array();

    $dynamic_url = 'http://' . $_SERVER['HTTP_HOST'] . $queryName . $_SERVER['QUERY_STRING'];
    $ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;

    /*
     * Block to handle saved queries
     * A query marked as saved can still be a 'new' query if it doesn't have an entry in saved_queries
     */
    if($saved == 1) {


            //Get cached filename from saved_queries (includes file extension so no need to append)
            $cachedFilenameStatement = "SELECT cache_file FROM saved_queries WHERE name='{$queryName}' AND county='{$county}';";


            $cachedFilenameResult = mysqli_query($link, $cachedFilenameStatement);
            if($cachedFilenameResult && $cachedFilenameResult->num_rows > 0) {
                $row = $cachedFilenameResult->fetch_assoc();
                $cache_file = $row['cache_file'];
            }

            //Load cached page
            if(!$ignore && file_exists($cache_file)) {
                ob_start('ob_gzhandler');
                readfile($cache_file);
                ob_end_flush();
                exit();
            }
    }
    //Entry in saved_queries does not exist so we are creating a new page from scratch
    else if($saved == 0) {
        $today = date("Y/m/d");
        //Need to concatenate template fields into one string so it can be inserted into database
        $templateFieldsString = '';
        foreach ($_POST['template_list'] as $temp) {
            $templateFieldsString .= "\'{$temp}\', ";
        }
        //Remove trailing comma and space
        $templateFieldsString = substr($templateFieldsString, 0, -2);
        $cache_file = $cache_folder . md5($dynamic_url) . $cache_ext;

        $saveQueryStatement = "INSERT INTO saved_queries (name, county, last_cached, cache_file, filter_categories) VALUES('{$queryName}',";
        $saveQueryStatement .= " '{$county}', '{$today}', '{$cache_file}', '{$templateFieldsString}');";
        $saveResult = mysqli_query($link, $saveQueryStatement);
        if ($saveResult === FALSE) {
            print($saveQueryStatement . "<br>");
            print("Error saving query: " . mysqli_error($link));
        }

        ob_start('ob_gzhandler');

        foreach($_POST['template_list'] as $template_list) {
            //Get table name from full field name
            $table = explode("||", $template_list);
            //Table name will always be first element prior to the period in the full field name
            $table = $table[0];

            for ($i = 0; $i < sizeOf($_SESSION['fields'][$table]); ++$i) {
                if (strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) !== FALSE) {
                    if (strpos($_SESSION['fields'][$table][$i]->fullFieldName, $template_list) === 0) {
                        array_push($template_fields, $_SESSION['fields'][$table][$i]);
                        break;
                    }
                }
            }
        }
    }


?>
<html>
    <head>
        <!--<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>-->
        <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
        <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
        <script src="jquery.multiselect.js"></script>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
        <link rel="stylesheet" href="jquery.multiselect.css"/>
        <link rel="stylesheet" href="common.css"/>
    </head>
    <body>
        <form id="query_form" method="POST" action="do_filter.php">
            <input type="hidden" name="county" value="<?php echo $county ?>"/>
            <div class="ui-accordion">
                <div class="ui-accordion-header">
                    <h2>Checkboxes</h2>
                </div>
                <div class="ui-accordion-content">
                    <table>
                    <td>
                        <?php
                        $gridCount = 0;
                        foreach($template_fields as $fields) {
                            if($gridCount == 0) {
                                echo "<tr>";
                            }
                            if($gridCount < 3) {
                                echo "<td width='360px'>";
                            }
                            if($fields->generateType == 2) {
                        ?>
                                <span><input type="checkbox" name="<?php echo $fields->fullFieldName ?>_checkbox" value="<?php $fields->fieldName ?>"><?php echo $fields->fieldName ?></span>
                            </td>
                        <?php
                                if($gridCount == 2) {
                                    echo "</tr>";
                                    $gridCount = 0;
                                }
                                else {
                                    ++$gridCount;
                                }
                            }
                        }
                        $gridCount = 0; ?>
                    </table>
                </div>
            </div>
            <div class="ui-accordion">
                <div class="ui-accordion-header">
                    <h2>Min/Max Categories</h2>
                </div>
                <div class="ui-accordion-content">
                    <table>
                    <?php
                    $gridCount = 0;
                    foreach($template_fields as $fields) {
                            if($gridCount == 0) {
                                echo "<tr>";
                            }
                            if($gridCount < 3) {
                                echo "<td width='360px'>";
                            }
                            if($fields->generateType == 0) { ?>
                            <div id="<?php echo $fields->fullFieldName ?>" class="ui-accordion">
                                <div id="accordion-header" class="ui-accordion-header">
                                    <h4><?php echo $fields->fieldName ?></h4>
                                </div>
                                <div id="accordion-content" class="ui-accordion-content">
                                        <i>At least </i><input type="text" name="<?php echo $fields->fullFieldName ?>_min"><br><br>
                                        <i>At most </i><input type="text" name="<?php echo $fields->fullFieldName?>_max">
                                    <?php } ?>
                                </div>
                            </div>
                    <?php
                        if($gridCount == 2) {
                            echo "</td></tr>";
                            $gridCount = 0;
                        }
                        else {
                            echo "</td>";
                            ++$gridCount;
                        }
                    }
                    $gridCount = 0;
                    ?>
                    </table>
                </div>
            </div>
            <div class="ui-accordion">
                <div class="ui-accordion-header">
                    <h2>Select Menus</h2>
                </div>
                <div class="ui-accordion-content">
                    <table>
                        <?php
                        $gridCount = 0;
                        foreach($template_fields as $fields) {
                            $table = explode("||", $fields->fullFieldName);
                            $table = $table[0];
                            if($gridCount == 0) {
                                echo "<tr>";
                            }
                            if($gridCount < 3) {
                                echo "<td width='360px'>";
                            }
                            if($fields->generateType == 1) {
                                echo "<h4>{$fields->fieldName}</h4>";
                                print(makeSelectionList($link, $county, $fields->fieldName, $table, $fields->fieldName, $fields->fieldName));
                            }
                            echo "</td>";
                            if($gridCount == 2) {
                                echo "</tr>";
                                $gridCount = 0;
                            }
                            else {
                                ++$gridCount;
                            }
                        }
                        $gridCount = 0;
                        ?>
                    </table>
                    <!--(hopefully) temporary hack to increase height of accordion content-->
                    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
                </div>
            </div>
            <button type="submit">Go</button><br>
        </form>
    </body>
</html>

<script type="text/javascript">
    $(".ui-accordion").accordion({
        collapsible: true,
        heightStyle: "content",
        active: false
    });

    $(".multiple_checkbox").multiselect({
        columns: 1,
        search: true,
        selectAll: true
    });
</script>

<?php
    if($saved == 0) {
        if (!is_dir($cache_folder)) {
            mkdir($cache_folder);
        }
        if (!$ignore) {
            $fp = fopen($cache_file, 'w');
            fwrite($fp, ob_get_contents());
            fclose($fp);
        }
        ob_end_flush();
    }
?>

