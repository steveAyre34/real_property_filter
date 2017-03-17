

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
						<div id="Assessment" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Assessment" class="ui-accordion-header">
						<h4>Assessment</h4>
					</div>
					<div id="accordion-content_Assessment" class="ui-accordion-content">
					</div>
				</div>
				<div id="Comm_bldg" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Comm_bldg" class="ui-accordion-header">
						<h4>Comm_bldg</h4>
					</div>
					<div id="accordion-content_Comm_bldg" class="ui-accordion-content">
					</div>
				</div>
				<div id="Comm_use" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Comm_use" class="ui-accordion-header">
						<h4>Comm_use</h4>
					</div>
					<div id="accordion-content_Comm_use" class="ui-accordion-content">
					</div>
				</div>
				<div id="Exempt" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Exempt" class="ui-accordion-header">
						<h4>Exempt</h4>
					</div>
					<div id="accordion-content_Exempt" class="ui-accordion-content">
					</div>
				</div>
				<div id="Improvement" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Improvement" class="ui-accordion-header">
						<h4>Improvement</h4>
					</div>
					<div id="accordion-content_Improvement" class="ui-accordion-content">
					</div>
				</div>
				<div id="Land" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Land" class="ui-accordion-header">
						<h4>Land</h4>
					</div>
					<div id="accordion-content_Land" class="ui-accordion-content">
					</div>
				</div>
				<div id="Mobile_home" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Mobile_home" class="ui-accordion-header">
						<h4>Mobile_home</h4>
					</div>
					<div id="accordion-content_Mobile_home" class="ui-accordion-content">
					</div>
				</div>
				<div id="Parcel" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Parcel" class="ui-accordion-header">
						<h4>Parcel</h4>
					</div>
					<div id="accordion-content_Parcel" class="ui-accordion-content">
					</div>
				</div>
				<div id="Parcel_to_owner" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Parcel_to_owner" class="ui-accordion-header">
						<h4>Parcel_to_owner</h4>
					</div>
					<div id="accordion-content_Parcel_to_owner" class="ui-accordion-content">
					</div>
				</div>
				<div id="Res_bldg" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Res_bldg" class="ui-accordion-header">
						<h4>Res_bldg</h4>
					</div>
					<div id="accordion-content_Res_bldg" class="ui-accordion-content">
					</div>
				</div>
				<div id="Site" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Site" class="ui-accordion-header">
						<h4>Site</h4>
					</div>
					<div id="accordion-content_Site" class="ui-accordion-content">
					</div>
				</div>
				<div id="Spec_dist" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Spec_dist" class="ui-accordion-header">
						<h4>Spec_dist</h4>
					</div>
					<div id="accordion-content_Spec_dist" class="ui-accordion-content">
					</div>
				</div>
				<div id="Specdist_def" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Specdist_def" class="ui-accordion-header">
						<h4>Specdist_def</h4>
					</div>
					<div id="accordion-content_Specdist_def" class="ui-accordion-content">
					</div>
				</div>
				<div id="Valuation" class="ui-accordion ui-state-disabled">
					<div id="accordion-header_Valuation" class="ui-accordion-header">
						<h4>Valuation</h4>
					</div>
					<div id="accordion-content_Valuation" class="ui-accordion-content">
					</div>
				</div>
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
				data: {county: 'columbia', table: table},
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
