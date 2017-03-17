<?php
	$cache_ext = '.php';
	$cache_folder = 'views/' . $_GET['county'] . '/';
	$ignore_pages = array('', '');
	
	$dynamic_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING'];
	$cache_file = $cache_folder . md5($dynamic_url) . $cache_ext;
	$ignore = (in_array($dynamic_url, $ignore_pages)) ? true : false;
	
	if(!$ignore && file_exists($cache_file)) {
		ob_start('ob_gzhandler');
		readfile($cache_file);
		ob_end_flush();
		exit();
	}
	else {
		session_start();
		require('connection.php');
		$county = $_GET['county'];
		
		//Get names of all tables for chosen county 
		$showTables = "SHOW TABLES LIKE '" . $county . "%';";
		$result = mysqli_query($conn, $showTables);	
		while($row = mysqli_fetch_array($result)) {
			$name = $row[0];
			$tables[] = ucwords(trim(preg_replace('/' . $county . '_/', ' ', $name)));
		}
		
		/*
		* Creates a master list of duplicate categories already displayed for this county
		* This way we don't pull duplicate search categories across files 
		*/
		$alreadyDisplayedFields = array();
		$tableMarker = array();
		$_SESSION['alreadyDisplayedFields'] = $alreadyDisplayedFields;
		$_SESSION['tableMarker'] = $tableMarker;
	}
	
	ob_start('ob_gzhandler');
?>


<html>
	<head>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
		<script src="jquery.multiselect.js"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
		<link href="jquery.multiselect.css" rel="stylesheet" type="text/css">
	</head>

	<body>
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
				url: "getTableSelect.php",
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
	
	/*$(".selectMenu").multiselect({
		columns: 2
	});*/
</script>
<?php
	if(!is_dir($cache_folder)) {
		mkdir($cache_folder);
	}
	if(!$ignore) {
		$fp = fopen($cache_file, 'w');
		fwrite($fp, ob_get_contents());
		fclose($fp);
	}
	ob_end_flush();
?>

