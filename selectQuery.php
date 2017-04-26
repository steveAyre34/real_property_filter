<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/14/2017
 * Time: 9:33 AM
 */
    include("connection.php");

    session_start();
    $county = $_GET['county'];
    $_SESSION['county'] = $county;

    //Get the date of last import for this county
    $importDateStatement = "SELECT date FROM last_updated WHERE county='{$county}';";
    $importDateResult = mysqli_query($link, $importDateStatement);
    if($importDateResult && $importDateResult->num_rows == 1) {
        $row = $importDateResult->fetch_assoc();
        $last_updated = $row['date'];
    }

    //Get all saved queries that exist for this county
    $selectStatement = "SELECT name, cache_file FROM saved_queries WHERE county='{$county}' ";
    $selectStatement .= "AND last_cached >= '{$last_updated}';";
    $savedQueryNames = array();
    $savedQueryFiles = array();
    if($result = mysqli_query($link, $selectStatement)) {
        while($row = $result->fetch_assoc()) {
            array_push($savedQueryNames, $row['name']);
            array_push($savedQueryFiles, $row['cache_file']);
        }
    }

    //Get names of all tables for chosen county
    $showTables = "SHOW TABLES LIKE '" . $county . "%';";
    $result = mysqli_query($link, $showTables);
    while($row = mysqli_fetch_array($result)) {
        $name = $row[0];
        $tables[] = ucwords(trim(preg_replace('/' . $county . '_/', ' ', $name)));
    }

    //Get the codes for this county so their meanings can be displayed where necessary
    //$codes = array();
    $codeTypes = array();
    $query = "SELECT DISTINCT type FROM codes WHERE county='" . ucfirst($county) . "' OR county='all';";
    if($result = mysqli_query($link, $query)) {
        while($row = $result->fetch_assoc()) {
            if(!in_array($row['type'], $codeTypes)) {
                array_push($codeTypes, $row['type']);
            }
        }
    }

    $_SESSION['codeTypes'] = $codeTypes;
    //$_SESSION['definitionCodes'] = $definitionCodes;
?>

<html>
    <head>
        <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
        <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
        <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
        <link rel="stylesheet" href="common.css"/>
    </head>

    <body>
    <h1>General Purpose Filter</h1>
    <form id="gen_purpose">
        <input type="hidden" name="county" value="<?php echo $county ?>"/>
        <input type="submit" class="ui-button" value="Go" formmethod="GET" formaction="select.php"/>
    </form>
    <h1>Saved Queries for <?php echo $county ?></h1>
        <form id="saved">
            <input type="hidden" name="county" value="<?php echo $county ?>"/>
            <input type="hidden" name="saved" value="1"/>
        <?php if(!empty($savedQueryFiles)) { ?>
            <select class="selectMenu" name='query_name' multiple='multiple' class='selectMenu[]'>
                <?php
                for ($i = 0; $i < sizeOf($savedQueryNames); ++$i) {
                    print("<option value='{$savedQueryNames[$i]}'>{$savedQueryNames[$i]}</option>");
                }
                }
             else
                 print("<h4>No saved queries for this county.</h4>");
            ?>
        </select><br>
        <?php if(!empty($savedQueryFiles)) { ?>
            <input class="ui-button" style="width:10.7%;" type="submit" value="Go" formmethod="POST" formaction="createTemplate.php"/>
        <?php  } ?>
        </form>
        <form id="create">
            <h1>Create New Query</h1>
            <input type="hidden" name="county" value="<?php echo $county ?>"/>
            <input type="hidden" name="saved" value="0"/>
            <input type="text" name="query_name" placeholder="Name your query"/>
            <input class="ui-button" type="submit" value="Next" formmethod="POST" formaction="select3.php"/>
        </form>
    </body>
</html>
