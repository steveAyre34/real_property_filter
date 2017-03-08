<?php
	include("common.php");
	
	/*if($_SERVER['REQUEST_METHOD'] == 'POST') {
		session_start();
		$_SESSION['county'] = $_POST['county'];
		$_SESSION['filenames'] = $_FILES['uploadFile']['name'];
		foreach($_SESSION['filenames'] as $f) {
			$removeExt = substr($f, 0, -4); 
			$_SESSION['databaseTables'] = $_SESSION['county'] . '_' . $removeExt;
		}
	}*/
?>

<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<script src="jquery-progressTimer-master/src/js/jquery.progressTimer.js"></script>
	<script src="http://malsup.github.com/jquery.form.js"></script> 
	<script src="https://netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="jquery-progressTimer-master/src/css/jquery.progressTimer.css"/>
	<link rel='stylesheet' type='text/css' href='import.css'>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
</head>
<body>
<form id="import_form" action="do_import.php" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
	<input type="file" id="uploadFile" name="uploadFile[]" multiple="multiple"/><br>
	<select name="county" id="county">
		<option value="selected">Choose a county</option>
		<?php foreach($counties as $c) { ?>
			<option value="<?php echo $c ?>"><?php echo ucwords($c) ?></option>
<?php	}	?>
	</select> 
	<button type="submit" id="submit" name="btn btn-submit">Submit</button>
</form>
<div class="loading-progress" id="progressBar"></div>
</body>
</html>

<script type="text/javascript">
	
	/*$("#import_form").on("submit", function() {
		var progress = $(".loading-progress").progressTimer({
			onFinish: function() {
				window.location.assign("index.php");
		}
		});
		/*$("#progressBar").progressTimer({ value: 0 });
		
		var source = new EventSource("do_import.php");
		
		source.addEventListener('message', function(e) {
			var pct = e * 100;
			$("#progressBar").progressBar('option', 'value', pct).children('.ui-progressbar-value').html(pct.toPrecision(3) + '%').css('display', 'block');
		});*/
	
		/*$.ajax({
			url: "do_import.php",
			error: function() {
				progress.progressTimer('error', {
					errorText: 'error',
					onFinish: function() {
						alert('processing error');
					}
				});
			},
			done: function() {
				progress.progressTimer('complete');
			},
			
			progress: function(e) {
				if(e.lengthComputable) {
					var pct = (e.loaded /e.total) * 100;
					$("#progressBar").progressbar('option', 'value', pct).children('.ui-progressbar-value').html(pct.toPrecision(3) + '%').css('display', 'block');	
				}
				else {
					console.warn('didnt work');
				}
			}
		});
	});
		//});
		//});
	//});
			/*function xhr() {
			var xhr = new window.XMLHttpRequest();
			//Upload progress
			xhr.upload.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percentComplete = evt.loaded / evt.total;
				//Do something with upload progress
				console.log(percentComplete);
			  }
			}, false);
			//Download progress
			xhr.addEventListener("progress", function(evt){
			  if (evt.lengthComputable) {
				var percentComplete = evt.loaded / evt.total;
				//Do something with download progress
				console.log(percentComplete);
			  }
			}, false);
			return xhr;
		  }
	});*/
		  
		  /*type: 'POST',
		  url: "do_import.php",
		  data: form_data,
		  success: function(data){
			console.log("success");
		  }*/
	//});
//});
	/*$("#progress").hide();
		
	
	$("#import_form").on("submit", function() {
			$("#progress").show();
			var progressBar = document.getElementById("progress");
	var xhr = new XMLHttpRequest();
			xhr.open("POST", "do_import.php", true);
		
	xhr.upload.onprogress = function(e) {
	if(e.lengthComputable) {
			progressBar.max = e.total;
			progressBar.value = e.loaded;
		}
	}
		
	xhr.upload.onloadstart = function(e) {
		progressBar.value = 0;
	}
		
	xhr.upload.onloadend = function(e) {
		progressBar.value = e.loaded;
	}
	
	xhr.send(new FormData());
	});*/
		
		/*var progressbar = $("#progressbar");
		progressLabel = "Loading...";
		progressLabel = $(progressLabel);
		
		$("#progressbar").progressbar({
			value: false,
			change: function() {
				progressLabel.text(progressbar.progressbar("value") + "%");
			},
			complete: function() {
				progressLabel.text("Import Complete");
			}
		});
		function progress() {
			var val = progressbar.progressbar("value") || 0;
			progressbar.progressbar("value", val + 1);
			
		}*/
		
		
		/*jQuery.ajaxSettings.xhr = function(){
				var xhr = new window.XMLHttpRequest();
				//Upload progress
				xhr.upload.addEventListener("progress", function (evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						console.log('percent uploaded: ' + (percentComplete * 100));
					}
				}, false);
				//Download progress
				xhr.addEventListener("progress", function (evt) {
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with download progress
						console.log('percent downloaded: ' + (percentComplete * 100));
					}
				}, false);
				return xhr;
		}*/
		
		/*$.ajax({
			type: 'POST',
			url: "do_import.php",
			data: {county: county},
			success: function(response) {
				console.log(response);	
				window.location.assign("index.php");
			}
			/*progress: function(xhr) {
				console.log(xhr);
			}
		});*/
	//});
</script>

