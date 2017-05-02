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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Find Records</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script src="jquery.multiselect.js"></script>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" href="jquery.multiselect.css"/>
    <link rel="stylesheet" href="common.css"/>
	<script src="jquery.multiselect.js"></script>
	<link rel="stylesheet" type="text/css" href="jquery.multiselect.css">
</head>

<body>
	<form name="frm_fields" action="do_filter.php" method="post">
			<h1>Real Property Data Filter for <?php echo $county ?> County:</h1>
        <button type="submit" class="ui-button gen-filter-buttons">Get Counts!</button>
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
                    $table = $county . '_site';
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
                        <?php
                            $table = $county . '_assessment';
                        ?>
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
                <!--(hopefully) temporary hack to increase height of accordion content-->
                <!--<br><br><br><br><br><br><br><br><br><br><br><br>-->
            </div>
        </div>
        <!--</div>-->


            <!--
			//print(makeCheckBox('second_address', 'second_address', '<strong>Property Owners do not reside at the property <u> EXPERIMENTAL</u></strong>')); 
			//print('</td><td>');
			//print(makeCheckBox('primary_address', 'primary_address', '<strong>Property Owners DO reside at the property <u> EXPERIMENTAL</u></strong>'));
                        //print('</td><td>');
			//print('</td></tr><tr><td>');
			print("</td></tr></table>\n");
			?>-->
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
                            <!--<li><a href="#unit_price">Unit Price</a></li>-->
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

                            <!--<div id="unit_price" class="ui-tabs-panel">
                                    At least <input type="text" class="inputText" name=<php echo $table ?>||unit_price_min"> Square Footage <br><br>
                                    At most <input type="text" class="inputText" name=<php echo $table ?>||unit_price_max"> Square Footage
                            </div>-->

                            <div id="land_value" class="ui-tabs-panel">
                                At least <input type="text" class="inputText" name=<?php echo $table ?>||land_value_min"> Dollars <br><br>
                                At most <input type="text" class="inputText" name=<?php echo $table ?>||land_value_max"> Dollars
                            </div>

                            <div id="wf_feet" class="ui-tabs-panel">
                                    At least <input type="text" class="inputText" name=<?php echo $table ?>||wf_feet_min"> Feet <br><br>
                                    At most <input type="text" class="inputText" name=<?php echo $table ?>||wf_feet_max"> Feet
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
                    <!--<li><a href="#site_desirability">Site Desirability</a></li>
                    <li><a href="#water_supply">Water Supply Type</a></li>-->
                    <li><a href="#sewer_type">Sewer Type</a></li>
                    <!--<li><a href="#utilities">Utilities</a></li>-->
                    <li><a href="#nbhd_rating">Neighborhood Rating</a></li>
                </ul>
                <div id="zoning_cd" class="ui-tabs-panel">
                    <?php
                    print(makeSelectionList($link, $county, 'zoning_cd', $table, 'Zoning Code', 'zoning_cd'));
                    ?>
                </div>
                <!--<div id="site_desirability" class="ui-tabs-panel">
                    <php
                    print(makeSelectionList($link, $county, 'site_desirability', $table, 'Site Desirability'));
                    ?>
                </div>
                <div id="water_supply" class="ui-tabs-panel">
                    <php
                    print(makeSelectionList($link, $county, 'water_supply', $table, 'Water Supply Type', 'water_supply'));
                    ?>
                </div>-->
                <div id="sewer_type" class="ui-tabs-panel">
                    <?php
                    print(makeSelectionList($link, $county, 'sewer_type', $table, 'Sewer Type', 'sewer_type'));
                    ?>
                </div>
                <!--<div id="utilities" class="ui-tabs-panel">
                    <php
                    print(makeSelectionList($link, $county, 'utilities', $table, 'Utilities', 'utilities'));
                    ?>
                </div>-->
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
                        <!--<li><a href="#sqft_living_area">Living Area (Sq. Ft.)</a></li>-->
                        <li><a href="#yr_blt">Year Built</a></li>
                        <li><a href="#yr_remodeled">Year Remodeled</a></li>
                        <!--<li><a href="#nbr_rooms">Nbr. Rooms</a></li>
                        <li><a href="#nbr_bed">Nbr. Beds</a></li>
                        <li><a href="#nbr_full_baths">Nbr. Full Baths</a></li>
                        <li><a href="#nbr_half_baths">Nbr. 1/2 Baths</a></li>
                        <li><a href="#nbr_kitchens">Nbr. Kitchens</a></li>
                        <li><a href="#nbr_fireplaces">Nbr. Fireplaces</a></li>
                        <li><a href="#nbr_stories">Nbr. Stories</a></li>
                        <li><a href="#first_story">1st Story Sq. Ft.</a></li>
                        <li><a href="#second_story">2nd Story Sq. Ft.</a></li>
                        <li><a href="#finished_attic">Finished Attic Sq. Ft.</a></li>
                        <li><a href="#finished_recroom">Finished Recroom Sq. Ft.</a></li>
                        <li><a href="#bsmnt_garage_capacity">Garage Capacity</a></li>
                        <li><a href="#bsmnt_type">Basement Type</a></li>-->
                        <li><a href="#heat_type">Heating Type</a></li>
                        <li><a href="#fuel_type">Fuel Type</a></li>
                        <li><a href="#overall_cond">Overall Condition</a></li>
                        <li><a href="#ext_wall_material">Exterior Wall Material</a></li>
                    </ul>
                    <!--<div id="sqft_living_area">
                        <php
                        print makeMinMaxSelector($table, 'sqft_living_area', 'Square Feet');
                        ?>
                    </div>-->
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
                    <!--<div id="nbr_rooms">
                        <php
                        print makeMinMaxSelector($table, 'nbr_rooms', 'Rooms');
                        ?>
                    </div>
                    <div id="nbr_bed">
                        ?php
                        print makeMinMaxSelector($table, 'nbr_bed', 'Beds');
                        ?>
                    </div>
                    <div id="nbr_full_baths">
                        <php
                        print makeMinMaxSelector($table, 'nbr_ful_baths', 'Full Baths');
                        ?>
                    </div>
                    <div id="nbr_half_baths">
                        <php
                        print makeMinMaxSelector($table, 'nbr_half_baths', 'Half Baths');
                        ?>
                    </div>
                    <div id="nbr_kitchens">
                        <php
                        print makeMinMaxSelector($table, 'nbr_kitchens', 'Kitchens');
                        ?>
                    </div>
                    <div id="nbr_fireplaces">
                        <php
                        print makeMinMaxSelector($table, 'nbr_fireplaces', 'Fireplaces');
                        ?>
                    </div>
                    <div id="nbr_stories">
                        <php
                        print makeMinMaxSelector($table, 'nbr_stories', 'Stories');
                        ?>
                    </div>
                    <div id="first_story">
                        <php
                        print makeMinMaxSelector($table, 'first_story', 'Square Feet');
                        ?>
                    </div>
                    <div id="second_story">
                        <php
                        print makeMinMaxSelector($table, 'second_story', 'Square Feet');
                        ?>
                    </div>
                    <div id="finished_attic">
                        <php
                        print makeMinMaxSelector($table, 'finished_attic', 'Square Feet');
                        ?>
                    </div>
                    <div id="finished_recroom">
                        <php
                        print makeMinMaxSelector($table, 'finished_recroom', 'Square Feet');
                        ?>
                    </div>
                    <div id="bsmnt_garage_capacity">
                        <php
                        print makeMinMaxSelector($table, 'bsmnt_garage_capacity', 'Cars');
                        ?>
                    </div>
                    <div id="bsmnt_type">
                        <php
                        print makeSelectionList($link, $county, 'bsmnt_type', $table, 'Basement Type', 'bsmnt_type');
                        ?>
                    </div>-->
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
			
			<!--<h3>Section 4.2: Commercial Building Information</h3>
			
			<php
			$table = $county . '_comm_use';
			print("<table><tr><td>");
			print('<input type="radio" name="building_set" id="com_buildings" value="com_buildings" title="Filter on commerical buildings"/><label for="com_buildings"><strong>Filter on Commercial Buildings</strong></label>');
			print("</td></tr><tr><td>");
			print(makeSelectionList($link, $county, 'used_as_cd', $table, 'Used As', 'used_as_cd', $default_width)); 
			print("</td></tr><tr><td>");
			print(makeCheckBox('bools[]', 'has_air_cond', 'Has Air Conditioning')); 
			print("</td><td>");
			print(makeCheckBox('bools[]', 'has_sprinklers', 'Has Sprinklers')); 
			print("</td><td>");
			print(makeCheckBox('bools[]', 'has_alarm', 'Has Alarm System')); 
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('nbr_1bed_apts', 'Apts', 'Number of 1BR Apts', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_2bed_apts', 'Apts', 'Number of 2BR Apts', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_3bed_apts', 'Apts', 'Number of 3BR Apts', $default_width));
			print("</td></tr>");
			$table = $county . '_comm_ind_util_bldg';
			print('<tr><td width="31%">');
			print(makeMinMaxSelector('bldg_perimeter', 'ft', 'Perimeter', $default_width));
			print('</td><td width="31%">');
			print(makeMinMaxSelector('gross_floor_area', 'ft^2', 'Floor Area', $default_width));
			print('</td><td width="31%">');
			print(makeMinMaxSelector('nbr_elevators', 'Elevators', 'Number of Elevators', $default_width));
			print('</td></tr>');
			print('<tr><td>');
			print(makeMinMaxSelector('bsmnt_perimeter', 'ft', 'Basement Perimeter', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_stories', 'Stories', 'Number of Stories', $default_width));
			#print("</td><td>");
			#print(makeMinMaxSelector('year_built', '', 'Year Built', $default_width));
			#print("Year Built temporarily disabled");
			print('</td></tr>');
			
			print("</table>\n");
			?>-->
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
            <!--
            <h2>Section 5: Improvement Information</h2>
			<h3>Section 5.1: Structures (Pools, Sheds, Barns, etc)</h3>
			
			<php
			$table = $county . '_improvement';
			print('<table><tr><td>');
			print(makeCheckBox('improvements', 'structs', 'Include Improvements In Filter'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_pools', 'All Pools (LS series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_farm_barn', 'All Farm/Barn (FB/FP Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_mobile', 'All Mobile Home Additions (MH Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_sheds', 'All Sheds(FC Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_patio', 'All Patio(LP Series)'));
			print('</td></tr>');
			print('<tr><td>');
			print(makeCheckBox('improvements_all[]', 'all_tennis', 'All Tennis (TC Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_garage', 'All garage (RG Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_canopy', 'All Canopy/Roofover (CP Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_porches', 'All Porches  (RP Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_golf', 'All Golf (GC Series)'));
			print('</td><td>');
			print(makeCheckBox('improvements_all[]', 'all_cold_storage', 'All Cold Storage'));
			print('</td></tr>');
			print('</table>');
			print('<table><tr><td>');
			print(makeSelectionList($link, $county, 'structure_cd', $table, 'Structure Type', 'structure_cd', $default_width * 3)); 
			print('</td></tr></table>');
			print('<table><tr><td width="540px">');
			print(makeMinMaxSelector('imp_replace_cost_new', 'Dollars', 'Replacement Cost (New)', $default_width));
			print('</td><td width="540px">');
			print(makeMinMaxSelector('imp_replace_cost_less_depr', 'Dollars', 'Replacement Cost (Less Deprecation)', $default_width));
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('yr_built', '', 'Year Built', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('imp_sqft', 'feet^2', 'Square Feet', $default_width));
			print("</td></tr></table>\n");
			?>
			
			-->
			<?//tell results what we want to do with what county ?>
			<input type="hidden" name="county" value="<?php echo $county ?>" />
        <button type="submit" class="ui-button gen-filter-buttons">Get Counts!</button>
        <button type="reset" class="ui-button gen-filter-buttons" value="Reset Criteria">Reset Criteria</button>
		</form>
	</body>
</html>
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
