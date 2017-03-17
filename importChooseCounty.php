<?php
	include("common.php");
?>

<html>
<head>
	<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
	<script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css"/>
	<script src="sweetalert2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="sweetalert2.css">
	
</head>
<body>
<form id="import_form"  method="POST" enctype="multipart/form-data" accept-charset="utf-8">
	<input type="file" id="uploadFile" name="uploadFile[]" multiple="multiple"/><br>
	<select name="county" id="county">
		<option value="selected">Choose a county</option>
		<?php foreach($counties as $c) { ?>
			<option value="<?php echo $c ?>"><?php echo ucwords($c) ?></option>
<?php	}	?>
	</select> 
	<button type="submit" id="submit" name="btn btn-submit">Submit</button>
</form>
</body>
</html>

<script type="text/javascript">	
	$("#import_form").on("submit", function(e) {
		e.preventDefault();
		swal({
			title: "Please be patient!",
			text: "Some counties can contain multiple hundreds of thousands of records - it may take a few minutes!",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Submit",
			showLoaderOnConfirm: true,
			allowOutsideClick: false,
			preConfirm: function() {
				return new Promise(function(resolve) {
					$.ajax({
					type: "POST",
					url: "do_import.php",
					data: $('#import_form').serialize(),
					success:function(){
						swal("Done!", "Records imported successfully!", "success");
						resolve();
					}
					})
				})
			}
		});
	});
</script>

