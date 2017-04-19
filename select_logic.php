<?php
include("connection.php");
require_once('common.php');
session_start();
//functions to create selection criteria form elements for the Real Property Data Filter
	
/********
* returns a checkbox for boolean critiera . 
* (such as has feature, include residential buildings, etc)
* @param:   name of the checkbox
*		value it will set
*		label to write for display
*		algin (default left)
*		checked state (default false)
*
*@return: the html for a checkbox		
********/
function makeCheckBox($name, $value, $label, $align='left', $checked=FALSE){
	$id = preg_replace('/[^A-Za-z0-9\_\-]/', '', $name .'_'. $value);
	//if($name == 'file[]'){
		$button = '<input id="'. $id .'" type="checkbox" name="'. $name .'" value="'. $value .'"'. ($checked ? ' checked="checked"' : '') . '>';
		//$label = '<label for="'. $id .'">'. $label .'</label>';
	//}
	/*else{
		$html = '<select name="' . $id . '[]" id="' . $id . '" multiple class="multiple_checkbox">';
		$html .= '<option value="'. $id .'">'. $label .'</option>';
		$html .= '</select>';
	}
		
	if ($align == 'left') {
		if($name == 'file[]'){
			$html = $button;
		}
		else{
			$html = $button . $label;
		}
	} else {*/
		$html = $button . $label;
	//}
	
	return $html;
}

/**
* returns a greater than / less than selector for numeric critiera . 
* (such as acreage, tax values, etc)
* @param:   name of the selector
*		value it will set(min/max)
*		label to write for display
*		units of the number
*@return: the html for a checkbox		
**/
function makeMinMaxSelector($name, $units, $label, $width){
	$html = '<table>';
	$width = ' style="width: ' . $width . 'ex;"';
	$label = makeCheckbox('bounds[]', $name, $label);
	//$file = makeCheckbox('file[]', $name, '(Include Field in Output)');
	$file = "";
	$html .= '<tr ' . $width . '>';
	$html .= '<div class="dcfieldname">' . $label . '&nbsp;&nbsp;' . $file . '</div>';
	$html .= '</tr><tr>';
	$html .= '<td>At least&nbsp;&nbsp;</td><td><input type="text" name="min_' . $name . '" size="10"/></td><td>&nbsp ' . $units . '</td>';
	$html .= '</tr><tr>';
	$html .= '<td>At most&nbsp;&nbsp;</td><td><input type="text" name="max_' . $name . '" size="10"/></td><td>&nbsp ' . $units . '</td>';
	$html .= '<input type="hidden" id="' . $name . '" name="'. $name .'[]" value="-1" />';
	$html .= "</tr></table>"; 
	return $html;
}
/********
* returns a list for criteria with values to be selected
* @param:    field to get criteria for
*		table to look in
*		county we are talking about
*		label for the list
*		name for the list
*		width to set for the list
*
*@return	the html for the selection list (as a table)
**********/

function makeSelectionList($link, $county, $field, $table, $label, $name){
	//define what fields we have decoded
	/*$decoded_fields = array('bsmnt_type','overall_cond', 'ext_wall_material', 'structure_cd', 'swis', 'sch_code', 'prop_class', 'waterfront_type', 'land_type', 'soil_rating',
							'site_desirability', 'water_supply', 'utilities', 'sewer_type', 'heat_type', 'fuel_type'
							
	);*/
	//Get any codes in the database
	$decoded_fields = $_SESSION['codeTypes'];
	/*$codesQuery = "SELECT DISTINCT type FROM codes ORDER BY type";
	$codesResult = mysqli_query($link, $codesQuery);
	while($codes = mysqli_fetch_array($codesResult)) {
		array_push($decoded_fields, $codes['type']);
	}*/

    /*
     * Check if the county has any definition tables (has def in the name)
     * If it does, get all distinct values from fields with 'code' in the name
     * Will have to manually exclude muni_code
     */
    $definitionCodes = $_SESSION['definitionCodes'];
    /*$query = "SHOW TABLES LIKE '%def%'";
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
    }*/

	$html = "";

	$sql = 'SELECT ' . $field . ', COUNT(*) FROM ' . $table . ' GROUP BY ' . $field . ' ORDER BY ' . $field;
	if(in_array($field, $decoded_fields)){
		$sql = "SELECT {$table}.{$field}, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.{$field} WHERE codes.type = '{$field}' GROUP BY codes.code ORDER BY codes.meaning ";
		//We want certain fields to be ordered by code, not by name. In later iterations of the filter this should be done in the ui
		if($field == 'prop_class' | $field == 'struct_code' | $field == 'overall_condition' ){
			$sql = "SELECT {$table}.{$field}, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.{$field} WHERE codes.type = '{$field}' GROUP BY codes.meaning ORDER BY {$table}.{$field} ";
		}
	}
	$result = mysqli_query($link, $sql);
	if(!$result){
		print("Error retrieving selection criteria for " . $table . "||" . $field);
		printf("Error: %s\n", mysqli_error($link));
		print("<BR>" . $sql . "<BR><BR>");
	}
	$num_rows = mysqli_num_rows($result);

	if($num_rows > 1){
		$html .= '<select name="' . $table . '||' . $name . '[]" multiple class="multiple_checkbox" id="' . $name . '">';
		
		for ($i = 0; $i < $num_rows; $i++){
			$row = mysqli_fetch_array($result);
			$row = str_replace('"', "", $row); //getting rid of the quotes surrounding the text
			if($row[1] == '1' || $row[1] == '2'){  //removing zip code with just 1 or 2 counts
				continue;
			}
			
			if(in_array($field, $decoded_fields)){
				$id = $row[0];
				$meaning = $row[1];
				$count = $row[2];
			} else {
				$id = $row[0];
				$meaning = '';
				$count = $row[1];
			}

			//For SWIS, need to filter village vs towns
			//If description has () in it
			if(strpos($meaning, '(') != FALSE) {
				//Town outside village
				//Change TOV to town
				if(strpos($meaning, 'TOV') != FALSE) {
					$meaning = substr($meaning, 0, strpos($meaning, 'TOV'));
					$meaning .= 'Town)';
				}
				else {
					$endParenIndex = strpos($meaning, ')');
					$meaning = substr($meaning, 0, $endParenIndex);
					$meaning .= ' - Village)';
				}
			}
			$txt = $id . ' : ' . $meaning . ' (' . $count . ')';
			$html .= '<option value="'. $id .'">'. $txt .'</option>';
		}
		
		$html .= '</select>';
	} else {
		if($num_rows == 1){
			$row = mysqli_fetch_array($result);
			$id = $row[0];
			if(!empty($id)){
				$html .= '<em>' . $id . ' records</em>';
			} else {
				$html .= '<em>none found</em>';
			}
		} else {
            $html .= '<em>none found</em>';
        }
	}
	return $html;
}

//thanks, Justin! though I dislike your use of magic numbers.....
function fmtListItem($id, $name, $num, $length) {
	$idlen = strlen($id) + 1;
	#$idlen = 11;
	$out = trim($id);
	$lame_variable = false;
	$newOut = trim($id);
	// workaround to print undefined instead of a blank space 
	if($out == ''){
		$newOut = 'None provided';
		$lame_variable = true;
	}
	
	$charcount = $idlen - strLen($out);
	
	//check to see if we have a meaning associated with a field
	if(trim($name) != ''){
		$newOut .= str_repeat('&nbsp;', $charcount) .': '. trim($name);
	} else {
		$newOut .= str_repeat('', $charcount);
	}
	$charcount = $length - $idlen - 12 - strLen(trim($name)) - strLen(trim($num));
	#ugly hack for property class
	if($length > 70){
		$charcount = $length - $idlen - 35 - strLen(trim($name)) - strLen(trim($num));
	}
	if ($charcount > 0) {
		//workaround for adjusting the numbers for undefined field
		if($lame_variable){
				$newOut .= str_repeat('&nbsp', $charcount - 8) . trim($num);
		}
		
		else{
			$newOut .= str_repeat('&nbsp;', $charcount) . trim($num);
		}
		#print $out;
	} else {
		$newOut .= ' '. trim($num);
	}
	return $newOut;
}
	
?>
