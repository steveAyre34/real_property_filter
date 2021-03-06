<?php
	include("connection.php");
	// common functions for Real Property data filter
	$_start = microtime(TRUE);

	//define what counties we have in the database
	$counties = array('albany',
			  'allegany', 
			  'broom', 
			  'cattaraugus', 
			  'cayuga', 
			  'chatauqua', 
			  'chemung', 
			  'chenango',
			  'clinton', 
			  'columbia', 
			  'cortland', 
			  'delaware', 
			  'dutchess', 
			  'erie', 
			  'essex', 
			  'franklin',
			  'fulton',
			  'genesee',
			  'greene',
			  'hamilton',
			  'herkimer',
			  'jefferson',
			  'lewis',
			  'livingston',
			  'madison',
			  'monroe',
			  'montgomery',
			  'nassau_partial',
			  'niagara',
			  'onieda',
			  'onondaga',
			  'ontario',
			  'orange',
			  'orleans',
			  'oswego',
			  'otsego',
			  'putnam',
			  'rensselaer',
			  'rockland',
			  'saratoga',
			  'schenectady',
			  'schoharie',
			  'schuyler',
			  'seneca',
			  'steuben',
			  'st_lawrence',
			  'suffolkpartial',
			  'sullivan',
			  'tioga',
			  'ulster',
			  'warren',
			  'washington',
			  'wayne',
			  'westchester',
			  'wyoming',
			  'yates');

	//define what counties data is updated and validated for - only these are available
	//$counties_available = array('columbia','dutchess','greene','ulster','orange', 'putnam', 'westchester','sullivan');
	$counties_available = array();
	$getCountiesAvailableStatement = "SELECT county FROM last_updated;";
	$getCountiesAvailableResult = mysqli_query($link, $getCountiesAvailableStatement);
	if($getCountiesAvailableResult && $getCountiesAvailableResult->num_rows > 0) {
		while($row = mysqli_fetch_assoc($getCountiesAvailableResult)) {
			array_push($counties_available, $row['county']);
		}
	}
	else {
		array_push($counties_available, 'No county data available.');
	}

	sort($counties);
	//define what fields we have decoded
	#$decoded_fields = array('swis_code', 'sch_code', 'prop_class', 'waterfront_type');
	// connect to database
	$link = mysqli_connect('127.0.0.1', 'root', '', 'rp2_database');
	if (mysqli_connect_errno()){
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	//returns a mysqli connection object
	function db_connect(){
		  $mysqli = new mysqli('127.0.0.1', 'root', '', 'rp2_database');
		  if(mysqli_connect_errno()) {
	        print "Connection Failed: " . mysqli_connect_errno();
			exit();
		}
		return $mysqli;
	}
	
	//escape apostrophes.
	function escape_apos(&$val, $key) {
		$val = str_replace("'", "''", $val);
	}
	
	// update progress bar
	function update_progress($percent, $name='') {
		static $last, $init, $start, $elapsed;
		$barlen = 250;
		$interval = 5;
		$name = 'progress'. $name;

		// initialize static variables
		if (!isset($last[$name])) $last[$name] = FALSE;
		if (!isset($init[$name])) $init[$name] = FALSE;
		if (!isset($start[$name])) $start[$name] = microtime(TRUE);
		if (!isset($elapsed[$name])) {
			$elapsed[$name] = 0;
		} elseif (microtime(TRUE) - $elapsed[$name] < $interval && $percent < 1) {
			# return if we haven't waited long enough
			return;
		} else {
			$elapsed[$name] = microtime(TRUE);
		}

		// determine eta for reporting
		if ($percent > 0) {
			$eta = round(microtime(TRUE) - $start[$name]);
			$eta = ($eta / $percent) - $eta;
		} else {
			$eta = 0;
		}

		// what to do?
		switch (TRUE) {
			case !$init[$name]:
				$cmd = <<<CMDEND
<form name="form_{$name}" style="margin: 0px; padding: 0px;">
<table cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td><table cellspacing="1" cellpadding="0" border="0" bgcolor="#808080"><tr><td width="{$barlen}" bgcolor="#E0E0E0"><img src="/progressbar.gif" id="bar_{$name}" width="1" height="12" /></td></tr></table></td>
		<td>&nbsp;<input name="eta" type="text" size="7" value="--:--:--" title="eta - hrs:min:sec" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 9px; line-height: 10px; color: #333; border: 0px; text-align: left;" /></td>
	</tr>
	<tr>
		<td align="right"><input name="pc" type="text" size="5" value="0%" style="font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 8.5px; line-height: 10px; color: #333; border: 0px; text-align: right;" /></td>
	</tr>
</table>
</form>
CMDEND;
				$init[$name] = TRUE;
				break;

			case $percent == 1:
				$cmd = <<<CMDEND
<script type="text/javascript">
	document.form_{$name}.pc.value = '100%';
	document.form_{$name}.eta.value = '';
	document.getElementById('bar_{$name}').width = {$barlen};
</script>
CMDEND;
				break;

			case $eta != $last[$name]:
				$bar = floor($percent*$barlen);
				$pc = floor($percent*100) .'%';

				switch (TRUE) {
					case $eta > (60*60): # hours
						$eta = sprintf('%02d:%02d:%02d',
							floor($eta/(60*60)),
							floor(($eta%(60*60))/60),
							floor(($eta%(60*60))%60));
						break;
					case $eta > 60: # minutes
						$eta = sprintf('%02d:%02d:%02d',
							0,
							floor($eta/60),
							floor($eta%60));
						break;
					default: # seconds
						$eta = sprintf('%02d:%02d:%02d',
							0,
							0,
							$eta);
						break;
				}

				$cmd = <<<CMDEND
<script type="text/javascript">
	document.form_{$name}.pc.value = '{$pc}';
	document.form_{$name}.eta.value = '{$eta}';
	document.getElementById('bar_{$name}').width = {$bar};
</script>
CMDEND;

				$last[$name] = $eta;
				break;

			default:
				return;
		}

		pp($cmd);
	}

	// output to screen, flush output buffers
	function pp($txt) {
		print $txt;
		ob_flush();
		flush();
	}


	// show debugging info
	function debug($txt, $br=TRUE) {
		if (DEBUG) {
			print $txt;
			if ($br) print '<br />';
			ob_flush();
			flush();
		}
	}

	function dump($d) {
		print '<pre>';
		if (is_string($d) || is_int($d)) {
			print $d;
		} else {
			print_r($d);
		}
		print '</pre>';
		ob_flush();
		flush();
	}


	// output file
	function dlFile($fs, $format="EXPORT_EXCEL") {
		$exportname = 'boe_export';

		switch ($format) {
			// Excel
			case 'EXPORT_EXCEL':
				$exportname .= '.xls';
				header('Content-Type: download/vnd.ms-excel');
				header('Content-Disposition: attachment; filename='. $exportname);
				break;

			// Tab-Delimited Text
			case 'EXPORT_TXT':
				$exportname .= '.txt';
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename='. $exportname);
				break;

			// CSV
			case 'EXPORT_CSV':
				$exportname .= '.csv';
				header('Content-Type: text/csv');
				header('Content-Disposition: attachment; filename='. $exportname);
				break;
		}
		print $fs;
		$fp = fopen($fs, 'r');
		fpassthru($fp);
		fclose($fp);
		exit();
	}


	// decapitalize a string, checking for common abbreviations
	//abandon all hope, ye who enter here.
	function decap(&$str, $check) {
		switch ($check) {
			case 'NAME':
				$str = ucwords(
					preg_replace('/( |\-)(mc|o\')([a-z])/e', "'\\1\\2'. strToUpper('\\3')",
					preg_replace('/ (ii|iii|iv|vi|vii|viii|ix|md|pfc|sfc)$/e', "' '. strToUpper('\\1')",
					preg_replace('/([a-z]+)-([a-z])([a-z]+)/e', "'\\1-'. strToUpper('\\2') .'\\3'",
					strToLower($str)))));
				break;

			case 'ADDRESS':
				$str = str_replace('%', 'c/o ',
					ucwords(
					preg_replace('/^po /', 'PO ',
					preg_replace('/([a-z,]) ([a-z]{2}) ([0-9])/e', "'\\1 '. strToUpper('\\2') .' \\3'",
					preg_replace('/([a-z]+)-([a-z])([a-z]+)/e', "'\\1-'. strToUpper('\\2') .'\\3'",
					strToLower($str))))));
				break;

			case 'CITY':
				$str = ucwords(
					preg_replace('/([a-z]+)-([a-z])([a-z]+)/e', "'\\1-'. strToUpper('\\2') .'\\3'",
					strToLower($str)));
				break;

			case 'STATE':
			case 'ZIP':
				$str = strToUpper($str);
				break;
		}
	}

	// write an XML Excel file
	function writeExcelXML($fp, $row, $mode='row', $rows=FALSE, $cols=FALSE) {
		static $headers;
		switch ($mode) {
			default:
			case 'row':
				$out = "\r\n\t<Row>";
				reset($row);
				$k=0;
				foreach($row as $c=>$v) {
					$k++;
					if (empty($v)) continue;
					$type = 'String';
					$style = 's21';

					// look for dates
					if (isset($headers[$c]) && preg_match('/(^date$|^date\s|\sdate$)/i', $headers[$c])) {
						$type = 'DateTime';
						$style = 's22';
					}

					$out .= "\r\n\t\t".'<Cell ss:Index="'. $k .'" ss:StyleID="'. $style .'"><Data ss:Type="'. $type .'">'. htmlentities($v) .'</Data></Cell>';
				}

				$out .= "\r\n\t</Row>";
				break;

			case 'head':
				// hang on to header row for later
				$headers = $row;

				// setup XML output file
				$xml = array();
				$xml['datetime'] = date('Y-m-d\TH:i:s\Z');
				$xml['rowhead'] = '<Cell ss:StyleID="s24"><Data ss:Type="String">'. implode('</Data></Cell>
		<Cell ss:StyleID="s24"><Data ss:Type="String">', $row) .'</Data></Cell>';
				$xml['rowcount'] = $rows;
				$xml['colcount'] = $cols;
				$out = '<?xml version="1.0"?>
		<?mso-application progid="Excel.Sheet"?>
		<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
		 xmlns:o="urn:schemas-microsoft-com:office:office"
		 xmlns:x="urn:schemas-microsoft-com:office:excel"
		 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
		 xmlns:html="http://www.w3.org/TR/REC-html40">';
				$out .= <<<XMLHEADEND
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <LastAuthor>Cornerstone Services, Inc.</LastAuthor>
  <Created>{$xml['datetime']}</Created>
  <Version>11.8132</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12660</WindowHeight>
  <WindowWidth>21900</WindowWidth>
  <WindowTopX>480</WindowTopX>
  <WindowTopY>135</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s22">
   <NumberFormat ss:Format="[ENG][$-409]mmmm\ d\,\ yyyy;@"/>
  </Style>
  <Style ss:ID="s24">
   <Font x:Family="Swiss" ss:Bold="1"/>
   <Interior ss:Color="#99CCFF" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="@"/>
  </Style>
  <Style ss:ID="s25">
   <Font x:Family="Swiss" ss:Bold="1"/>
   <Interior ss:Color="#99CCFF" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Data">
  <Table x:FullColumns="1"
   x:FullRows="1">
   <Row ss:StyleID="s25">
		{$xml['rowhead']}
   </Row>
XMLHEADEND;
				break;

			case 'foot':
				// finish XML output
				$out = <<<XMLFOOTEND
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <Print>
    <ValidPrinterInfo/>
    <HorizontalResolution>1200</HorizontalResolution>
    <VerticalResolution>1200</VerticalResolution>
   </Print>
   <Selected/>
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>1</SplitHorizontal>
   <TopRowBottomPane>1</TopRowBottomPane>
   <ActivePane>2</ActivePane>
   <Panes>
    <Pane>
     <Number>3</Number>
    </Pane>
    <Pane>
     <Number>2</Number>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
XMLFOOTEND;
				break;
		}	
		
		fwrite($fp, $out);
	}
?>
