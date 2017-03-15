<?php
	require('connection.php');
	$county = $_GET['county'];
	
	//Get names of all tables for chosen county 
	$showTables = "SHOW TABLES LIKE '" . $county . "%';";
	$result = mysqli_query($conn, $showTables);
	
	while($row = mysqli_fetch_array($result)) {
		$name = $row[0];
		$tables[] = ucwords(trim(preg_replace('/' . $county . '_/', ' ', $name)));
	}
?>


<html>
	<head>
		<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
		<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	</head>

	<body>
			<div id="Owner" class="ui-accordion">
				<div id="accordion-header_Owner" class="ui-accordion-header">
					<h4>Owner</h4>
				</div>
				<div id="accordion-content_Owner" class="ui-accordion-content">
				</div>
			</div>
		<?php 
			foreach($tables as $key => $value) { 
				if($value != "Owner") {?>
				<div id="<?php echo $value ?>" class="ui-accordion">
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
	/*********************
		Need js code to activate each accordion
	//.accordion() 
	*********************/
	$(".ui-accordion").accordion({
		heightStyle: "content",
		collapsible: true,
		active: true,
		activate: function(event, ui) {
			var table = $(this).attr("id");
			$.ajax({
				type: "GET",
				url: "getTableSelect.php",
				data: {county: '<?php echo $county ?>', table: table},
				success: function(response) {
					$("#accordion-content_" + table).html(response);
				}
			});
		}
	});
	
	//$("ui-accordion").on("accordionactivate")
	
	
</script>