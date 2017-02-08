<?php
//TODO - add option to explicitly include state of new york, central hudson, verizon etc.
//TODO - clean this mess up

	//define selection criteria for the Real Property Data Filter. Sends form data to results.php
	require_once('common.php');
	require_once('select_logic.php');
	//sanity check on location
	if(!isset($_GET['county'])){
		die("Error: No county specified. County must be specified as an HTTP Get");
	}
	$county = $_GET['county'];
	if(!in_array($county, $counties)){
		die("Sorry, {$county} county is not in our database");
	}
	$default_width = 60;
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Find Records</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<link rel="stylesheet" href="main.css" type="text/css" />
	<script src="forms.js" type="text/javascript"></script>
</head>

<body>
	<form name="frm_fields" action="results.php" method="post" target="_blank">
			<h1>Real Property Data Filter for <?= $county ?> county:</h1>
			<input type="submit" class="button" value="Get Counts!" onClick="setAction('count');" style="height:75px;width:540px;background-color:#FA8072;font-size:20px" />
            <input type="reset" value="Reset Criteria" style="height:75px;width:540px;background-color:#FA8072;font-size:20px"/>	

			<h2>Section 0: Assessment Information</h2>
			<?
			$table = $county . '_class';
			print('<table><tr><td width = "540px">');
			print(makeMinMaxSelector('total_av', 'Dollars', 'Total Assessment', $default_width));
			print('</td><td width = "540px">');
			print(makeMinMaxSelector('land_av', 'Dollars', 'Land Assessment', $default_width));
			print('</td></tr></table>');
			?>

			<h2>Section 1: Parcel (Location) Information</h2>
			
			<?php
			$table = $county . '_site';
			print("<table><tr><td>");
			print(makeSelectionList($link, $county, 'swis', $table, 'SWIS', 'swis', $default_width)); 
			print("</td><td>");
			//moved field
			print(makeSelectionList($link, $county, 'sch_code', $county . '_assessment', 'School Code', 'sch_code', $default_width)); 
			print("</td><td>");
			$table = $county . '_parcel';
			print(makeSelectionList($link, $county, 'loc_zip', $table, 'ZIP Code', 'loc_zip', $default_width)); 
			print('</td></tr><tr><td>');
			//print(makeMinMaxSelector('grid_east', 'Degrees', 'Grid East', $default_width));
			//print("</td><td>");
			//print(makeMinMaxSelector('grid_north', 'Degrees', 'Grid North', $default_width));
			//print('</td></tr><tr><td>');
			//print(makeCheckBox('second_address', 'second_address', '<strong>Property Owners do not reside at the property <u> EXPERIMENTAL</u></strong>')); 
			//print('</td><td>');
			//print(makeCheckBox('primary_address', 'primary_address', '<strong>Property Owners DO reside at the property <u> EXPERIMENTAL</u></strong>'));
                        //print('</td><td>');
			//print('</td></tr><tr><td>');
			print("</td></tr></table>\n");
			?>
			
			<h2>Section 2: Land Information</h2>
			<?php
			$table = $county . '_land';
			print("<table><tr><td>");
			
			print(makeMinMaxSelector('full_market_value', 'Dollars', 'Market Value', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('acres', 'Acres', 'Acreage', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('sqft', 'ft^2', 'Square Footage', $default_width));
			print("</td></tr><tr><td>");
			print(makeMinMaxSelector('unit_price', 'Dollars', 'Unit Price', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('land_value', 'Dollars', 'Land Value', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('wf_feet', 'ft', 'Waterfront Feet', $default_width));
			print("</td></tr>");
			
			?>
			<h3>Section 2.1: Additional Land Information</h3>
			<?php
			print("<td>");
			print(makeSelectionList($link, $county, 'land_type', $table, 'Land Type', 'land_type', $default_width)); 
			print('</td><td>');
			print(makeSelectionList($link, $county, 'waterfront_type', $table, 'Waterfront Type', 'waterfront_type', $default_width)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'soil_rating', $table, 'Soil Rating', 'soil_rating', $default_width)); 
			print("</td></tr></table>\n");
			?>
			<h2>Section 3: Site Information</h2>
			
			<?php
			$table = $county . '_site';
			print('<table><tr><td>');
			print(makeCheckBox('prop_groups[]', 'all_res', 'All Residential Properties (200 series)'));
			print('</td><td>');
			//print(makeCheckBox('prop_groups[]', 'all_res_exclude', 'All Residential Properties (200 series) (EXCLUDE)'));
			//print('</td><td>');
			print(makeCheckBox('prop_groups[]', 'all_com', 'All Commercial Properties (400 series)'));
			print('</td><td>');
			//print(makeCheckBox('prop_groups[]', 'all_com_exclude', 'All Commercial Properties (400 series) (EXCLUDE)'));
			//print('</td></tr><tr><td>');
			print(makeCheckBox('prop_groups[]', 'all_agri', 'All agriculture'));
			print('</td></tr>');
			print('<tr><td>');
			print(makeCheckBox('prop_groups[]', 'all_vacant', 'Vacant Land, Residential & Rural Vacant(300 Series)'));
			print('</td><td>');
			print(makeCheckBox('prop_groups[]', 'all_recreational', 'Recreation & Entertainment, golf, etc(500 Series)'));
			print('</td><td>');
			print(makeCheckBox('prop_groups[]', 'all_600', 'Education, Schools, Hospitals, Govt. Buildings, Cemetaries (600 Series)'));
			print('</td></tr>');
			print('<tr><td>');
			print(makeCheckBox('prop_groups[]', 'all_manu', 'Manufacturing (700 Series)'));
			print('</td><td>');
			print(makeCheckBox('prop_groups[]', 'all_800', 'Water Supply, Telephone & Cell, Sewer (800 Series)'));
			print('</td><td>');
			print(makeCheckBox('prop_groups[]', 'all_forest', 'Forest, Preserves, State Land, Public Parks  (900 Series)'));
			print("</td></tr></table><table><tr><td>");
			print(makeSelectionList($link, $county, 'prop_class', $table, 'Property Class', 'prop_class', $default_width * 3));
			print("</td></tr></table>");
			print("<table><tr><td>");
			print(makeSelectionList($link, $county, 'zoning_cd', $table, 'Zoning Code', 'zoning_cd', $default_width)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'site_desirability', $table, 'Site Desirability', 'site_desirability', $default_width)); 
			print('</td><td>');
			print(makeSelectionList($link, $county, 'water_supply', $table, 'Water Supply Type', 'water_supply', $default_width)); 
			print("</td></tr><td>");
			print(makeSelectionList($link, $county, 'sewer_type', $table, 'Sewer Type', 'sewer_type', $default_width)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'utilities', $table, 'Utilities', 'utilities', $default_width)); 
			print('</td><td>');
			print(makeSelectionList($link, $county, 'nbhd_rating', $table, 'Nghbrd Rating', 'nbhd_rating', $default_width)); 
			print("</td></tr></table>\n");
			?>
			<br/>
			<input type="submit" class="button" value="Get Counts!" onClick="setAction('count');" style="height:75px;width:540px;background-color:#FA8072;font-size:20px" />
            <input type="reset" value="Reset Criteria" style="height:75px;width:540px;background-color:#FA8072;font-size:20px"/>	
			<br/>
			<h2>Section 4: Building Information</h2>

			<h3>Section 4.1: Residential Building Information</h3>
			<?php 
			$table = $county . '_res_bldg';
			print("<table><tr><td>");
			print('<input type="radio" name="building_set" checked="checked" id="res_buildings" value="res_buildings" title="Filter on residential buildings"/><label for="res_buildings"><strong>Search on Residential Buildings</strong></label>');
			print("</td></tr><tr><td>");
			print(makeMinMaxSelector('sqft_living_area', 'ft^2', 'Square Feet of Living Area', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('yr_blt', '', 'Year Built', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('yr_remodeled', '', 'Year Remodeled', $default_width));
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('nbr_rooms', 'Rooms', 'Number of Rooms', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_bed', 'Bedrooms', 'Number of Bedrooms', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_full_baths', 'Full Bathrooms', 'Number of Full Bathrooms', $default_width));
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('nbr_half_baths', 'Half Bathrooms', 'Number of Half Bathrooms', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_kitchens', 'Kitchens', 'Number of Kitchens', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('nbr_fireplaces', 'Fireplaces', 'Number of Fireplaces', $default_width));
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('nbr_stories', 'Stories', 'Number of Stories', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('first_story', 'ft^2', 'First Story Sqft', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('second_story', 'ft^2', 'Second Story Sqft', $default_width));
			print('</td></tr><tr><td>');
			print(makeMinMaxSelector('finished_attic', 'ft^2', 'Finished Attic Sqft', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('finished_recroom', 'ft^2', 'Finished Recroom Sqft', $default_width));
			print("</td><td>");
			print(makeMinMaxSelector('bsmnt_garage_capacity', 'Cars', 'Garage Capactiy', $default_width));
			print('</td></tr><tr><td>');
			print(makeSelectionList($link, $county, 'bsmnt_type', $table, 'Basement Type', 'bsmnt_type', $default_width+2)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'heat_type', $table, 'Heat Type', 'heat_type', $default_width)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'fuel_type', $table, 'Fuel Type', 'fuel_type', $default_width)); 
			print('</td></tr><tr><td>');
			print(makeSelectionList($link, $county, 'overall_cond', $table, 'Overall Condition', 'overall_cond', $default_width)); 
			print("</td><td>");
			print(makeSelectionList($link, $county, 'ext_wall_material', $table, 'Exterior Wall Material', 'ext_wall_material', $default_width)); 
			print("</td><td>");
			print(makeCheckBox('bools[]', 'has_central_air', 'Has Central Air')); 
			print("</td><td>");
			print("</td></tr></table>\n");
			?>
			
			<h3>Section 4.2: Commercial Building Information</h3>
			
			<?php 
			$table = $county . '_com_use';
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
			?>

			<h2>Section 5: Improvement Information</h2>
			<h3>Section 5.1: Structures (Pools, Sheds, Barns, etc)</h3>
			
			<? 
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
			
					
			<h2>Enter Selection</h2>
						
			<?//tell results what we want to do with what county ?>
			<input type="hidden" name="county" value="<?= $county ?>" />
			<input type="hidden" name="todo" value="counts" />
			<input type="submit" class="button" value="Get Counts!" onClick="setAction('count');" style="height:75px;width:540px;background-color:#FA8072;font-size:20px" />
            <input type="reset" value="Reset Criteria" style="height:75px;width:540px;background-color:#FA8072;font-size:20px"/>	
		</form>
	</body>
</html>
