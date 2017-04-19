<?php
    /**
     * Created by PhpStorm.
     * User: crstadmin
     * Date: 4/12/2017
     * Time: 9:17 AM
     */
    require("connection.php");
    require_once("Field.php");
    $county = $_POST['county'];
    session_start();
    $_SESSION['county'] = $_POST['county'];
    $saved = $_POST['saved'];
    $query_name = $_POST['query_name'];


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

    /*
     * Check if the county has any definition tables (has def in the name)
     * If it does, get all distinct values from fields with 'code' in the name
     * Will have to manually exclude muni_code
     */
    $definitionCodes = array();
    $query = "SHOW TABLES LIKE '%def%'";
    if($result = mysqli_query($link, $query)) {
        while($row = $result->fetch_assoc()) {
            foreach($row as $key => $value) {
                //Only need the def file for specified county
                if(strpos($value, $county) == 0) {
                    $innerQuery = "SHOW COLUMNS IN " . $value . " LIKE '%code%';";
                    if($innerResult = mysqli_query($link, $innerQuery)) {
                        while($innerRow = $innerResult->fetch_assoc()) {
                            if($innerRow['Field'] != "muni_code" && !in_array($innerRow['Field'], $definitionCodes)) {
                                array_push($definitionCodes, $innerRow['Field']);
                            }
                        }
                    }
                }
            }
        }
    }


    $_SESSION['codeTypes'] = $codeTypes;
    $_SESSION['definitionCodes'] = $definitionCodes;
    /*
    * Creates a master list of duplicate categories already displayed for this county
    * This way we don't pull duplicate search categories across files
    */
    $alreadyDisplayedFields = array();
    $tableMarker = array();
    $_SESSION['alreadyDisplayedFields'] = $alreadyDisplayedFields;
    $_SESSION['tableMarker'] = $tableMarker;

?>
<html>
	<head>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
		<script src="jquery.multiselect.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link rel="stylesheet" type="text/css" href="jquery.multiselect.css">
		<link rel="stylesheet" type="text/css" href="filter.css">
	</head>

	<body>
		<form id="filter_form" action="createTemplate.php" method="POST">
			<input name="county" type="hidden" value="<?php echo $county ?>"/>
            <input name="saved" type="hidden" value="<?php echo $saved ?>"/>
            <input name="query_name" type="hidden" value="<?php echo $query_name ?>"/>
			<div id="Owner" class="ui-accordion ui-state-disabled">
				<div id="accordion-header_Owner" class="ui-accordion-header">
					<h4>Owner</h4>
				</div>
				<div id="accordion-content_Owner" class="ui-accordion-content">
				</div>
			</div>
		<?php
			foreach($tables as $key => $value) {
                if($value != "Owner") {?>
                    <div id="<?php echo $value ?>" class="ui-accordion ui-state-disabled">
                        <div id="accordion-header_<?php echo $value ?>" class="ui-accordion-header">
                            <h4><?php echo $value ?></h4>
                        </div>
                        <div id="accordion-content_<?php echo $value ?>" class="ui-accordion-content">
                        </div>
                    </div>
                <?php			}
            } ?>
<button type="submit" id="createButton">Create</button>
</form>
</body>
</html>

<script type="text/javascript">
    $(".ui-accordion").accordion({
        heightStyle: "content",
        collapsible: true,
        active: false,
        create: function(event, ui) {
            var table = $(this).attr("id");
            $.ajax({
                type: "GET",
                url: "getTableSelect2.php",
                data: {county: '<?php echo $county ?>', table: table},
                async: true,
                success: function(response) {
                    $("#accordion-content_" + table).html(response);
                },
                complete: function(response) {
                    $("#" + table).removeClass("ui-state-disabled");
                    $("#" + table).addClass("ui-state-enabled");
                }
            });
        }
    });
</script>