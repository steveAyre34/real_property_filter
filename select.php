<?php


	//define selection criteria for the Real Property Data Filter. Sends form data to results.php
	require_once('common.php');
	include('connection.php');
	require_once('select_logic.php');
	//sanity check on location
	if(!isset($_GET['county'])){
		die("Error: No county specified. County must be specified as an HTTP Get");
	}
	$county = strtolower($_GET['county']);

	if(!in_array($county, $counties)){
		die("Sorry, " . $county . " county is not in our database");
	}
	$default_width = 60;

	//Variables needed for page caching
    $cache_ext = ".php";
    $cache_folder = "views/{$county}/";
    $cache_file = "";
    $ignore_pages = array('', '');
    $dynamic_url = 'http://' . $_SERVER['HTTP_HOST'] . "gen_{$county}" . $_SERVER['QUERY_STRING'];
    $ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;
    $county_last_updated = 0;
    $filter_last_cached = 0;
    $saved = 0;

    /*
     * Check if county has had an import since the last time the page was cached
     */
    $checkDateStatement = "SELECT last_updated.date AS date, saved_queries.last_cached AS cached, saved_queries.cache_file AS file FROM last_updated, saved_queries WHERE saved_queries.name='gen_{$county}'";
    $checkDateStatement .= " AND last_updated.county='{$county};";
    $checkDateResult = mysqli_query($link, $checkDateStatement);
    if($checkDateResult && $checkDateResult->num_rows == 1) {
        $county_last_updated = $checkDateResult['date'];
        $filter_last_cached = $checkDateResult['cached'];
        $cache_file = $checkDateResult['file'];
        /*echo "<script type='text/javascript'>
            console.log(\'" . strtotime($filter_last_cached) . "\');
            console.log(\'" . strtotime($county_last_updated) . "\');
            </script>";*/
    }

    if(!$ignore && file_exists($cache_file) && (strtotime($filter_last_cached) >= strtotime($county_last_updated))) {
        $saved = 1;
        ob_start('ob_gzhandler');
        readfile($cache_file);
        ob_end_flush();
        exit();
    }
    else {
        $today = date("Y/m/d");
        $cache_file = $cache_folder . md5($dynamic_url) . $cache_ext;
        $saveCacheStatement = "INSERT INTO saved_queries (name, county, last_cached, cache_file) VALUES ('gen_{$county}'";
        $saveCacheStatement .= ", '{$county}' ,'{$today}', '{$cache_file}');";
        $saveCacheResult = mysqli_query($link, $saveCacheStatement);
        if($saveCacheResult === FALSE) {
            print($saveCacheStatement . "<br>");
            print("Error saving query: " . mysqli_error($link));
        }
        ob_start('ob_gzhandler');
    }
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Find Records</title>
    <link rel="stylesheet" type="text/css" href="jquery.multiselect.css">
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>
    <link rel="stylesheet" href="common.css"/>
    <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="jquery.multiselect.js"></script>
	<script src="jquery.multiselect.js"></script>

</head>
    <body>
    <form id="filter" name="frm_fields" action="do_filter.php" method="POST" >
			<h1>Real Property Data Filter for <?php echo ucfirst($county) ?> County:</h1>
        <button type="submit" class="ui-button gen-filter-buttons" value="Get Counts!">Get Counts!</button>
        <button type="reset" class="ui-button gen-filter-buttons" value="Reset Criteria">Reset Criteria</button>

			<div id="assessmentInformation" class="ui-accordion majorSection" style="width:45%;display:inline-block">
				<div id="accordion-header_assessmentInformation" class="ui-accordion-header">
					<h2>Section 1: Assessment Information</h2>
				</div>
				<div id="accordion-content_assessmentInformation" class="ui-accordion-content">
                    <?php
                        $table = $county . '_class';
                    ?>
                    <table><tr>
                    <td style="width:20vw;">
                            <h4>Total Assessment</h4>
                                <p>At least <input type="text" class="inputText" name="<?php echo $table ?>||total_av_min"> Dollars</p>
                                <p>At most <input type="text" class="inputText" name="<?php echo $table ?>||total_av_max"> Dollars</p>
                    </td>
                    <td style="width:20vw;">
                        <h4>Land Assessment</h4>
                        <p>At least <input type="text" class="inputText" name="<?php echo $table ?>||land_av_min"> Dollars</p>
                        <p>At most <input type="text" class="inputText" name="<?php echo $table ?>||land_av_max"> Dollars</p>
                    </td></tr></table>
				</div>
			</div>
        <div id="parcelInformation" class="ui-accordion majorSection" style="height:auto;width:45%;display:inline-block">
            <div id="accordion-header_parcelInformation" class="ui-accordion-header">
                <h2>Section 2: Parcel (Location) Information</h2>
            </div>
            <div id="accordion-content_parcelInformation" class="ui-accordion-content">
                <?php
                    $table = $county . '_assessment';
                ?>
                <table>
                    <tr>
                        <td style="width:20vw">
                            <h4>SWIS Code</h4>
                            <?php
                                print(makeSelectionList($link, $county, 'swis', $table, 'SWIS', 'swis'));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="width:20vw;">
                            <h4>School Code</h4>
                            <?php
                                print(makeSelectionList($link, $county, 'sch_code', $table, 'School Code', 'sch_code'));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <?php
                            $table = $county . '_parcel';
                        ?>
                        <td style="width:20vw;">
                            <h4>ZIP Code</h4>
                            <?php
                                print(makeSelectionList($link, $county, 'loc_zip', $table, 'ZIP Code', 'loc_zip'));
                            ?>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        <div id="landInformation" class="ui-accordion majorSection">
			<div id="accordion-header_landInformation" class="ui-accordion-header">
			    <h2>Section 3: Land Information</h2>
            </div>
            <div id="accordion-content_landInformation" class="ui-accordion-content">
			    <?php
			        $table = $county . '_land';
                ?>
                <!--<table>
                    <tr>
                        <td style="width:33vw;">-->
                    <div id="tabs" class="ui-tabs">
                        <ul>
                            <li><a href="#full_market_value">Market Value</a></li>
                            <li><a href="#acres">Acreage</a></li>
                            <li><a href="#sqft">Square Feet</a></li>
                            <li><a href="#land_value">Land Value</a></li>
                            <li><a href="#wf_feet">Feet from Waterfront</a></li>
                        </ul>
                            <div id="full_market_value" class="ui-tabs-panel">

                                    At least <input type="text" class="inputText" name="<?php echo $table ?>||full_market_value_min"> Dollars <br><br>
                                    At most <input type="text" class="inputText" name="<?php echo $table ?>||full_market_value_max"> Dollars

                            </div>
                            <div id="acres" class="ui-tabs-panel">
                                    At least <input type="text" class="inputText" name="<?php echo $table ?>||acres_min"> Acres <br><br>
                                    At most <input type="text" class="inputText" name="<?php echo $table ?>||acres_max"> Acres
                            </div>
                            <div id="sqft" class="ui-tabs-panel">
                                    At least <input type="text" class="inputText" name="<?php echo $table ?>||sqft_min"> Square Feet <br><br>
                                    At most <input type="text" class="inputText" name="<?php echo $table ?>||sqft_max"> Square Feet
                            </div>

                            <div id="land_value" class="ui-tabs-panel">
                                At least <input type="text" class="inputText" name="<?php echo $table ?>||land_value_min"> Dollars <br><br>
                                At most <input type="text" class="inputText" name="<?php echo $table ?>||land_value_max"> Dollars
                            </div>

                            <div id="wf_feet" class="ui-tabs-panel">
                                    At least <input type="text" class="inputText" name="<?php echo $table ?>||wf_feet_min"> Feet <br><br>
                                    At most <input type="text" class="inputText" name="<?php echo $table ?>||wf_feet_max"> Feet
                            </div>
                    </div>
                    <table>
                    <tr>
                        <td style="width:33vw;">
                            <h4>Land Type</h4>
                            <?php
                            print(makeSelectionList($link, $county, 'land_type', $table, 'Land Type', 'land_type'));
                            ?>
                        </td>
                        <td style="width:33vw;">
                            <h4>Waterfront Type</h4>
                            <?php
                            print(makeSelectionList($link, $county, 'waterfront_type', $table, 'Waterfront Type', 'waterfront_type'));
                            ?>
                        </td>
                        <td style="width:33vw;">
                            <h4>Soil Rating</h4>
                            <?php
                            print(makeSelectionList($link, $county, 'soil_rating', $table, 'Soil Rating', 'soil_rating'));
                            ?>
                        </td>
                    </tr>
                </table>
                <!--(hopefully) temporary hack to increase height of accordion content-->
                <br><br><br><br><br><br>
            </div>
        </div>
        <div id="siteInformation" class="ui-accordion majorSection">
            <div id="accordion-header_siteInformation" class="ui-accordion-header">
                <h2>Section 4: Site Information</h2>
            </div>
            <div id="accordion-content_siteInformation" class="ui-accordion-content">
                <?php
                $table = $county . '_site';
                ?>
                <h4 style="color:#000066;">Property Class</h4>
                    <div>
                        <div class="radioButtons">
                            <label for="all_residential_properties" class="ui-checkboxradio-label">All Residential Properties</label>
                            <input type="radio" class="ui-checkboxradio-disabled" name="all_residential_properties" id="all_residential_properties">
                            <label for="all_commercial_properties" class="ui-checkboxradio-label">All Commercial Properties</label>
                            <input type="radio" class="ui-checkboxradio-disabled" name="all_commercial_properties" id="all_commercial_properties">
                            <label for="both_properties" class="ui-checkboxradio-label">All Commercial & All Residential Properties</label>
                            <input type="radio" class="ui-checkboxradio-disabled" name="both_properties" id="both_properties">
                        </div>
                        <div class="ui-tabs">
                            <ul>
                                <li><a href="#agricultural_properties">Agricultural Properties (100 Series)</a></li>
                                <li><a href="#residential_properties">Residential Properties (200 Series)</a></li>
                                <li><a href="#residential_vacant_properties">Vacant Properties - Residential & Rural (300 Series)</a></li>
                                <li><a href="#commercial_properties">Commercial Properties (400 Series)</a></li>
                                <li><a href="#recreation_entertainment_properties">Recreation & Entertainment Properties (500 Series)</a></li>
                                <li><a href="#community_properties">Community Properties - Schools, Hospitals, etc. (600 Series)</a></li>
                                <li><a href="#manufacturing_properties">Manufacturing Properties (700 Series)</a></li>
                                <li><a href="#infrastructure_properties">Infrastructure Properties - Water, Phones, etc. (800 Series)</a></li>
                                <li><a href="#state_owned_properties">State-Owned Properties - Parks, etc. (900 Series)</a></li>
                            </ul>

                                    <?php
                                    /*
                                     * Generate select menus for each property class (100, 200, etc.)
                                     */
                                    print("<div id='agricultural_properties' class='ui-tabs-panel'>");

                                    //Agricultural (100 Series)
                                    //print("<h4>Agricultural Properties (100 Series)<br><br></h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '1%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //Residential (200 Series)
                                    print("<div id='residential_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Residential Properties (200 Series)<br><br></h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '2%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //Vancant Properties (300 Series)
                                    //Check old filter for category name
                                    print("<div id='residential_vacant_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Vacant Properties - Residential & Rural (300 Series)</h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '3%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");
                                    //print("</tr>");

                                    //print("<tr>");
                                    //Commercial (400 Series)
                                    print("<div id='commercial_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Commercial Properties (400 Series)<br><br><br></h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '4%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //? (500 Series)
                                    print("<div id='recreation_entertainment_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Recreation & Entertainment Properties - Golf, Movie Theaters, etc. (500 Series)<br><br></h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '5%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //Community Properties (600 Series)
                                    print("<div id='community_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Community Properties - Schools, Hospitals, Govt. Buildings, etc. (600 Series)</h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '6%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");
                                    //print("</tr>");

                                    //print("<tr>");
                                    //Manufacturing Properties (700 Series)
                                    print("<div id='manufacturing_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Manufacturing Properties (700 Series)<br><br></h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '7%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //Infrastructure (800 Series)
                                    print("<div id='infrastructure_properties' class='ui-tabs-panel'>");
                                    //print("<h4>Infrastructure Properties - Water Supply, Phones, Sewer, etc. (800 Series)</h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '8%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");

                                    //State Land (900 Series)
                                    print("<div id='state_owned_properties' class='ui-tabs-panel'>");
                                    //print("<h4>State-Owned Properties - Public Parks, Preserves, etc. (900 Series)</h4>");
                                    print("<select name='{$table}||prop_class[]' multiple class='multiple_checkbox selectMenu'>");
                                    $getPropertyClassStatement = "SELECT {$table}.prop_class, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.prop_class WHERE {$table}.prop_class LIKE '9%' AND codes.type='prop_class' GROUP BY codes.meaning ORDER BY {$table}.prop_class;";
                                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                                            $id = $row[0];
                                            $meaning = $row[1];
                                            $count = $row[2];
                                            $txt = "{$id} : {$meaning} ({$count})";
                                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                                        }
                                    }
                                    print("</select>");
                                    print("</div>");
                                    ?>
                        </div>
                <!--(hopefully) temporary hack to increase height of accordion content-->
                    </div>

			<h4 style="color: #000066;">Other Site Information</h4>
            <div class="ui-tabs">
                <ul>
                    <li><a href="#zoning_cd">Zoning Code</a></li>
                    <li><a href="#sewer_type">Sewer Type</a></li>
                    <li><a href="#nbhd_rating">Neighborhood Rating</a></li>
                </ul>
                <div id="zoning_cd" class="ui-tabs-panel">
                    <?php
                    print(makeSelectionList($link, $county, 'zoning_cd', $table, 'Zoning Code', 'zoning_cd'));
                    ?>
                </div>

                <div id="sewer_type" class="ui-tabs-panel">
                    <?php
                    print(makeSelectionList($link, $county, 'sewer_type', $table, 'Sewer Type', 'sewer_type'));
                    ?>
                </div>

                <div id="nbhd_rating" class="ui-tabs-panel">
                    <?php
                    print(makeSelectionList($link, $county, 'nbhd_rating', $table, 'Neighborhood Rating', 'nbhd_rating'));
                    ?>
                </div>
                <br><br><br><br><br><br>
            </div>
        </div>
        </div>

        <!--<button type="submit" class="ui-button gen-filter-buttons">Get Counts!</button>
        <button type="reset" class="ui-button gen-filter-buttons" value="Reset Criteria">Reset Criteria</button>-->

        <div id="building_information" class="ui-accordion majorSection">
            <?php
            $table = $county . '_res_bldg';
            ?>
            <div class="ui-accordion-header">
                <h2>Section 5: Building Information</h2>
            </div>
            <div class="ui-accordion-content">
                <h4 style="color:#000066;">Residential Building Information</h4>
                <div class="radioButtons">
                    <label for="<?php echo $table ?>||air_cond" class="ui-checkboxradio-label">Has Air Conditioning</label>
                    <input type="radio" class="ui-checkboxradio-disabled" name="<?php echo $table ?>||air_cond" id="<?php echo $table ?>||air_cond">
                </div>
                <div class="ui-tabs">
                    <ul>
                        <li><a href="#yr_blt">Year Built</a></li>
                        <li><a href="#yr_remodeled">Year Remodeled</a></li>
                        <li><a href="#heat_type">Heating Type</a></li>
                        <li><a href="#fuel_type">Fuel Type</a></li>
                        <li><a href="#overall_cond">Overall Condition</a></li>
                        <li><a href="#ext_wall_material">Exterior Wall Material</a></li>
                    </ul>
                    <div id="yr_blt" class="ui-tabs-panel">
                        <?php
                        print makeMinMaxSelector($table, 'yr_built', '');
                        ?>
                    </div>
                    <div id="yr_remodeled" class="ui-tabs-panel">
                        <?php
                        print makeMinMaxSelector($table, 'yr_remodeled', '');
                        ?>
                    </div>
                    <div id="heat_type" class="ui-tabs-panel">
                        <?php
                        print makeSelectionList($link, $county, 'heat_type', $table, 'Heat Type', 'heat_type');
                        ?>
                    </div>
                    <div id="fuel_type" class="ui-tabs-panel">
                        <?php
                        print makeSelectionList($link, $county, 'fuel_type', $table, 'Fuel Type', 'fuel_type');
                        ?>
                    </div>
                    <div id="overall_cond" class="ui-tabs-panel">
                        <?php
                        print makeSelectionList($link, $county, 'overall_cond', $table, 'Overall Condition', 'overall_cond');
                        ?>
                    </div>
                    <div id="ext_wall_material" class="ui-tabs-panel">
                        <?php
                        print makeSelectionList($link, $county, 'ext_wall_material', $table, 'Exterior Wall Material', 'ext_wall_material');
                        ?>
                    </div>
                </div>
                <h4 style="color: #000066;">Commercial Building Information</h4>
                <?php
                $table = "{$county}_comm_use";
                ?>

                <div class="radioButtons">
                    <label for="<?php echo $table ?>||air_cond" class="ui-checkboxradio-label">Has Air Conditioning</label>
                    <input type="radio" class="ui-checkboxradio-disabled" name="<?php echo $table ?>||air_cond" id="<?php echo $table ?>||air_cond">
                </div>
                <div class="ui-tabs">
                    <ul>
                        <li><a href="#used_as_cd">Used As</a></li>
                        <li><a href="#yr_blt">Year Built</a></li>
                    </ul>
                    <div id="used_as_cd" class="ui-tabs-panel">
                        <?php
                        print makeSelectionList($link, $county, 'used_as_cd', $table, 'Used As', 'used_as_cd');
                        ?>
                    </div>
                    <?php
                    $table = "{$county}_comm_bldg";
                    ?>
                    <div id="yr_blt" class="ui-tabs-panel">
                        <?php
                        print makeMinMaxSelector($table, 'yr_blt', '');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="improvement_information" class="ui-accordion majorSection">
            <div class="ui-accordion-header">
                <h2>Section 6: Improvement Information</h2>
            </div>
            <div class="ui-accordion-content">
                <?php
                $table = "{$county}_improvement";
                ?>
                <h4 style="color:#000066;">Improvement Structures</h4>
                <div class="ui-tabs">
                    <ul>
                        <li><a href="#pools">Pools (LS)</a></li>
                        <li><a href="#farm_barn">Farms/Barns (FB/FP) </a></li>
                        <li><a href="#mobile_home">Mobile Homes (MH)</a></li>
                        <li><a href="#sheds">Sheds (FC)</a></li>
                        <li><a href="#patios">Patios (LP)</a></li>
                        <li><a href="#tennis">Tennis Courts (TC)</a></li>
                        <li><a href="#garages">Garages (RG)</a></li>
                        <li><a href="#canopy_roofover">Canopies (CP)</a></li>
                        <li><a href="#porches">Porches (RP)</a></li>
                        <li><a href="#golf">Golf Courses (GC)</a></li>
                        <li><a href="#cold_storage">Cold Storage</a></li>
                    </ul>

                    <?php
                    /*
                     * Generate select menus for each property class (100, 200, etc.)
                     */
                    print("<div id='pools' class='ui-tabs-panel'>");

                    //Pools (LS)
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'LS%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Farms/Barns (FP/FB)
                    print("<div id='farm_barn' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE ({$table}.structure_cd LIKE 'FB%' OR {$table}.structure_cd LIKE 'FP%') AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Mobile Homes (MH)
                    print("<div id='mobile_home' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'MH%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Sheds (FC)
                    print("<div id='sheds' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'FC%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Patios (LP)
                    print("<div id='patios' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'LP%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Tennis Courts (TC)
                    print("<div id='tennis' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'TC%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Garages (RG)
                    print("<div id='garages' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'RG%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Canopies (CP)
                    print("<div id='canopy_roofover' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'CP%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Porches (RP)
                    print("<div id='porches' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'RP%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Golf Courses (GC)
                    print("<div id='golf' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'GC%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");

                    //Cold Storage (RN)
                    print("<div id='cold_storage' class='ui-tabs-panel'>");
                    print("<select name='{$table}||structure_cd[]' multiple class='multiple_checkbox selectMenu'>");
                    $getPropertyClassStatement = "SELECT {$table}.structure_cd, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.structure_cd WHERE {$table}.structure_cd LIKE 'RN%' AND codes.type='structure_cd' GROUP BY codes.meaning ORDER BY {$table}.structure_cd;";
                    $getPropertyClassResult = mysqli_query($link, $getPropertyClassStatement);
                    if($getPropertyClassResult && $getPropertyClassResult->num_rows > 0){
                        while($row = mysqli_fetch_array($getPropertyClassResult)) {
                            $id = $row[0];
                            $meaning = $row[1];
                            $count = $row[2];
                            $txt = "{$id} : {$meaning} ({$count})";
                            print("<option class='ms-options-wrap' value='{$id}'>{$txt}</option>");
                        }
                    }
                    print("</select>");
                    print("</div>");
                    ?>
                </div>
                    <h4 style="color:#000066;">Year Built</h4>
                    <div id="inputText">
                    <?php
                    print makeMinMaxSelector($table, 'yr_blt', '');
                    ?>
                    </div>
                    <br><br><br>
            </div>
        </div>
			<?//tell results what we want to do with what county ?>
			<input type="hidden" name="county" value="<?php echo $county ?>" />
        <button type="submit" class="ui-button gen-filter-buttons">Get Counts!</button>
        <button type="reset" class="ui-button gen-filter-buttons" value="Reset Criteria">Reset Criteria</button>
		</form>
	</body>
</html>

<?php
    if(isset($_POST)) {
        $_POST = array_filter($_POST);
        //mysqli_close($link);
    }
?>
<script type="text/javascript">
	$(".majorSection").accordion({
		collapsible: true,
        heightStyle: "content",
        active: false
	});

	$(".minorSection").accordion({
        collapsible: true,
        active: false,
        heightStyle: "content",
        autoWidth: false
    });

	$(".multiple_checkbox").multiselect({
        columns: 1,
        search: true,
        selectAll: true,
        autoWidth: false
    });

	$('.ui-tabs').tabs();

	$('.ui-checkboxradio-disabled').checkboxradio();

</script>
<?php
    if($saved == 0) {
        if(!is_dir($cache_folder)) {
            mkdir($cache_folder);
        }
        if(!$ignore) {
            $fp = fopen($cache_file, 'w');
            fwrite($fp, ob_get_contents());
            fclose($fp);
        }
        ob_end_flush();
    }
?>
