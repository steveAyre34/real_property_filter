<?php
/**
 * Created by PhpStorm.
 * User: crstadmin
 * Date: 4/14/2017
 * Time: 9:33 AM
 */
include("connection.php");
$county = $_GET['county'];

//Get all saved queries that exist for this county
$selectStatement = "SELECT name, cache_file FROM saved_queries WHERE county='" . $_GET['county'] . "';";
$savedQueryNames = array();
$savedQueryFiles = array();
if($result = mysqli_query($link, $selectStatement)) {
    while($row = $result->fetch_assoc()) {
        array_push($savedQueryNames, $row['name']);
        array_push($savedQueryFiles, $row['cache_file']);
    }
}


?>

<html>
    <head>
        <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
        <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
    </head>

    <body>
    <h1>General Purpose Filter</h1>
    <form id="gen_purpose" target="_blank">
        <input type="hidden" name="county" value="<?php echo $county ?>"/>
        <input type="submit" value="Go" formmethod="GET" formaction="select.php"/>
    </form>
    <h1>Saved Queries for <?php echo $county ?></h1>
        <form id="saved" target="_blank">
            <input type="hidden" name="county" value="<?php echo $county ?>"/>
            <input type="hidden" name="saved" value="1"/>
        <?php if(!empty($savedQueryFiles)) { ?>
            <select name='saved_queries[]' multiple='multiple' class='selectMenu[]'>
                <?php
                for ($i = 0; $i < sizeOf($savedQueryFiles); ++$i) {
                    print("<option value='{$savedQueryFiles[$i]}'>{$savedQueryNames[$i]}</option>");
                }
                }
             else
                 print("<h4>No saved queries for this county.</h4>");
            ?>
        </select>
        <?php if(!empty($savedQueryFiles)) { ?>
            <input type="submit" value="Go" formmethod="POST" formaction="createTemplate.php"/>
        <?php  } ?>
        </form>
        <form id="create" target="_blank">
            <h1>Create New Query</h1>
            <input type="hidden" name="county" value="<?php echo $county ?>"/>
            <input type="hidden" name="saved" value="0"/>
            <input type="text" name="query_name" placeholder="Name your query"/>
            <input type="submit" value="Next" formmethod="POST" formaction="select3.php"/>
        </form>
    </body>
</html>
