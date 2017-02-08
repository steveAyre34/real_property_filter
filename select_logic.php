<?php
require_once('common.php');

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
	//	$button = '<input id="'. $id .'" type="checkbox" name="'. $name .'" value="'. $value .'"'. ($checked ? ' checked="checked"' : '') .'visibility: hidden'.' />';
		//$label = '<label for="'. $id .'">'. $label .'</label>';
	//}
	//else{
		$button = '<input id="'. $id .'" type="checkbox" name="'. $name .'" value="'. $value .'"'. ($checked ? ' checked="checked"' : '') .' />';
		$label = '<label for="'. $id .'">'. $label .'</label>';
	//}
		
	if ($align == 'left') {
		//if($name == 'file[]'){
		//	$html = $button;
		//}
		//else{
			$html = $button . $label;
		//}
	} else {
		$html = $label .'&nbsp;'. $button;
	}
	
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
	$html = "<table>";
	$width = ' style="width: ' . $width . 'ex;"';
	$label = makeCheckbox('bounds[]', $name, $label);
	//$file = makeCheckbox('file[]', $name, '(Include Field in Output)');
	$file = "";
	$html .= "<tr {$width}>";
	$html .= '<div class="dcfieldname">' . $label . '&nbsp;&nbsp;' . $file . '</div>';
	$html .= "</tr><tr>";
	$html .= "<td>At least&nbsp;&nbsp;</td><td><input type='text' name='min_{$name}' size='10'/></td><td>&nbsp {$units}</td>";
	$html .= "</tr><tr>";
	$html .= "<td>At most&nbsp;&nbsp;</td><td><input type='text' name='max_{$name}' size='10'/></td><td>&nbsp {$units}</td>";
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

function makeSelectionList($link, $county, $field, $table, $label, $name, $width=FALSE){
	//define what fields we have decoded
	$decoded_fields = array('bsmnt_type','overall_cond', 'ext_wall_material', 'structure_cd', 'swis', 'sch_code', 'prop_class', 'waterfront_type', 'land_type', 'soil_rating',
							'site_desirability', 'water_supply', 'utilities', 'sewer_type', 'heat_type', 'fuel_type'
							
	);
	//reset time limit so we do not timeout on long requests
	set_time_limit(30);
	$html = "";
	$length = $width - 4;
	$width = ' style="width: ' . $width . 'ex;"';
	$label = makeCheckbox('cols[]', $name, $label);
	//$exc = makeCheckbox('exclude[]', $name, '(Exclude)');
	//$file = makeCheckbox('file[]', $name, '(Include Field in Output)');
	$file = "";
	$html .= "<table>";
	$html .= "<tr {$width}>";
	$html .= '<div class="dcfieldname">' . $label . '&nbsp;&nbsp;' . $file . '</div>';
	$html .= "</tr><tr>";
	$sql = 'SELECT ' . $field . ', COUNT(*) FROM ' . $table . ' GROUP BY ' . $field . ' ORDER BY ' . $field;
	if(in_array($field, $decoded_fields)){
		$sql = "SELECT {$table}.{$field}, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.{$field} WHERE codes.type = '{$field}' GROUP BY codes.meaning ORDER BY codes.meaning ";
		//We want certain fields to be ordered by code, not by name. In later iterations of the filter this should be done in the ui
		if($field == 'prop_class' | $field == 'struct_code' | $field == 'overall_condition' ){
			$sql = "SELECT {$table}.{$field}, codes.meaning, COUNT(*) FROM {$table} LEFT JOIN codes ON codes.code = {$table}.{$field} WHERE codes.type = '{$field}' GROUP BY codes.meaning ORDER BY {$table}.{$field} ";
		}
	}
	$result = mysqli_query($link, $sql);
	if(!$result){
		print("Error retrieving selection criteria for " . $table . "." . $field);
		printf("Error: %s\n", mysqli_error($link));
		print("<BR>" . $sql . "<BR><BR>");
	}
	$num_rows = mysqli_num_rows($result);
	//temp debug
	#if($field == 'swis_code'){
	#	print $sql;
	#}
	if($num_rows > 1){		
		$html .= '<select name="' . $name . '[]" class="dcinput" multiple="multiple" size="8"' . $width . '>';
		/* function compare($a, $b)
		{
		// Assuming you're sorting on bar field
		return strcmp($a[0], $b[0]);
		}
		for($m = 0; $m < $num_rows ; $m++){
			$row = mysql_fetch_array($result);
			$row = str_replace('"', "", $row);
			$a[$m] = $row;
		}
		usort($a,"compare");
		for ($i = 0; $i < count($a); $i++){
			//$row = mysqli_fetch_array($result);
			//$row = str_replace('"', "", $row); //getting rid of the quotes surrounding the text
			// echo("<script>console.log('".{$row}."');</script>");
			if($i[1] == '1' || $i[1] == '2'){  //removing zip code with just 1 or 2 counts
				continue;
			}
			
			if(in_array($field, $decoded_fields)){
				$id = $i[0];
				$meaning = $i[1];
				$count = $i[2];
			} else {
				$id = $i[0];
				$meaning = '';
				$count = $i[1];
			}
			
			
			$txt = fmtListItem($id, $meaning, $count, $length);
			#$txt = $id . ' : ' . $meaning . ' - ' . $count;
			$html .= '<option value="'. $id .'">'. $txt .'</option>';
		} */
		
		
		for ($i = 0; $i < $num_rows; $i++){
			$row = mysqli_fetch_array($result);
			$row = str_replace('"', "", $row); //getting rid of the quotes surrounding the text
			//echo "<script>console.log('".$row[0]."');</script>";
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
			
			
			$txt = fmtListItem($id, $meaning, $count, $length);
			#$txt = $id . ' : ' . $meaning . ' - ' . $count;
			$html .= '<option value="'. $id .'">'. $txt .'</option>';
		}
		
		$html .= '</select>';
	} else {
		if($num_rows == 1){
			$row = mysqli_fetch_array($result);
			$id = $row[0];
			if(!empty($id)){
				$html .= '<em>' . fmtListItem($id, -1) . ' records</em>';
			} else {
				$html .= '<em>none found</em>';
			}
		} else {
			$html .= '<em>none found</em>';
		}
		$html .= '<input type="hidden" name="'. $name .'[]" value="-1" />';
	}
	$html .= '</table>';
	return $html;
}

//thanks, Justin! though I dislike your use of magic numbers.....
function fmtListItem($id, $name, $num, $length) {
	$idlen = strlen($id) + 1;
	#$idlen = 11;
	$out = trim($id);
	// workaround to print undefined instead of a blank space 
	if($out == ''){
		$out = 'undefined';
		$lame_variable = true;
	}
	
	$charcount = $idlen - strLen($out);
	//check to see if we have a meaning associated with a field
	if(trim($name) != ''){
		$out .= str_repeat('&nbsp;', $charcount) .': '. trim($name);
	} else {
		$out .= str_repeat('&nbsp;', $charcount);
	}
	$charcount = $length - $idlen - 12 - strLen(trim($name)) - strLen(trim($num));
	#ugly hack for property class
	if($length > 70){
		$charcount = $length - $idlen - 35 - strLen(trim($name)) - strLen(trim($num));
	}
	if ($charcount > 0) {
		//workaround for adjusting the numbers for undefined field
		if($lame_variable){
				$out .= str_repeat('&nbsp;', $charcount - 8) . trim($num);
		}
		
		else{
			$out .= str_repeat('&nbsp;', $charcount) . trim($num);
		}
		#print $out;
	} else {
		$out .= ' '. trim($num);
	}
	return $out;
}
	
?>
