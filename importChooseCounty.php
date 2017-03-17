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
	<style type="text/css">
	   .progress {
		display: block;
		text-align: center;
		width: 0;
		height: 20px;
		background: red;
		transition: width .3s;
		}
		.progress.hide {
			opacity: 0;
			transition: opacity 1.3s;
		}
   </style>
	
</head>
<body>
<form id="import_form" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
	<input type="file" id="uploadFile" name="uploadFile[]" multiple="multiple"/><br>
	<select name="county" id="county">
		<option value="selected">Choose a county</option>
		<?php foreach($counties as $c) { ?>
			<option value="<?php echo $c ?>"><?php echo ucwords($c) ?></option>
<?php	}	?>
	</select> 
	<button type="submit" id="submit" name="btn btn-submit">Submit</button>
</form>
<div class="progress" id="progress"></div>
</body>
</html>

<script type="text/javascript">	
	$("#import_form").on("submit", function(e) {
		/*e.preventDefault();
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
		});*/
	
		$.ajax({
			/*xhr: function () {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log(percentComplete);
                        $('.progress').css({
                            width: percentComplete * 100 + '%'
                        });
                        if (percentComplete === 1) {
                            $('.progress').addClass('hide');
                        }
                    }
                }, true);
                xhr.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log(percentComplete);
                        $('.progress').css({
                            width: percentComplete * 100 + '%'
                        });
                    }
                }, true);
                return xhr;
            },*/
			  url: "do_import.php",
			  type: "POST",
			  data: JSON.stringify($('#import_form').serialize()),
			  contentType: "application/json",
			  dataType: "json",
			  success: function(result) {
				window.location.assign("importChooseCounty.php");
			  }
		});
	});
</script>

