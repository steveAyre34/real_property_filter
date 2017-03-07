<?php
	//include("import_createHeaders.php");
	
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
</head>
<body>
<form id="import_form" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
	<input type="file" id="uploadFile" name="uploadFile[]" multiple="multiple"/><br>
	<select name="county" id="county">
		<option value="selected">Choose a county</option>
		<option value="ulster">Ulster County</option>
	</select> 
	<input type="submit" class="button" id="import_form_submit" name="btn btn-submit"/>
</form>
</body>
</html>

<script type="text/javascript">
	var allFiles = Array();

	$('#import_form_submit').click(function(e) {
		var county = document.getElementById("county").value;
		//var $this = $(this);
		//$('#import_form').preventDefault();
		e.preventDefault();
		$('#uploadFile').each(function(index, element) {
			var file = $(element);
			file = file.val();
			file = file.slice(file.lastIndexOf("\\"));
			file = file.substring(1);
			allFiles.push(file);
			$.ajax({
				type: "GET",
				async: true,
				url: "createHeaders.php",
				data: { county: county, fileName: file } ,
				success: function(response) {
					$('#import_form').hide();
					document.write(response);
				},
				error: function(response) {
					//$('#import_form').hide();
					console.log("ERROR: "+response);
				}
			});
		//});
		});
		//$('#import_form').hide();
		return false;
	});
	
		/*$('.td').attr('contenteditable', 'true');
		var cell;*/
	function submitTable(databaseTable, file) {				
			headers = document.getElementById(databaseTable);
			headers = headers.elements['databaseHeaders[]'];

			/*console.log("FILE: " + file);
			console.log("TABLE: " + databaseTable);
			console.log("HEADERS: " + JSON.stringify($(headers).serializeArray()));*/
			$.ajax ({
				type: 'POST',
				url: 'do_import.php',
				data: { headers: JSON.stringify($(headers).serializeArray()), filename: file, databaseTable: databaseTable}, 
				success: function(response) {
					$('#'+databaseTable).hide();
					var i = allFiles.indexOf(file);
					if(i != -1) {
						allFiles.splice(i, 1);
					}	
					if(allFiles.length == 0) {
						window.location='importChooseCounty.php';
					}
					console.log(response);
				},
				error: function(response) {
					$('#'+databaseTable).hide();
					var i = allFiles.indexOf(file);
					if(i != -1) {
						allFiles.splice(i, 1);
					}	
					if(allFiles.length == 0) {
						window.location='importChooseCounty.php';
					}

					console.log(response);
				}
			});
		}

		/*function highlight() {
			$(arguments).toggleClass('invalid', true);
		}

	function compareHeaders() {
		//Reset style before re-checking
		$('td.invalid').toggleClass('invalid');
		//Get table rows as array of array
		var rows = $('tr').map(function(elem, i) {
			return [$(this).children('td').toArray()];
		}).toArray();

		//Loop through the rows and highlight non-equal
		for(var i = 0; i < rows.length; ++i) {
			cell = {};
			for(var j = 0; j < rows[i].length; ++j) {
				var cellText = $(rows[i][j]).text();
				if(cell[cellText] != $(rows[i][j+1]).text()) {
					highlight(cell[cellText], rows[i][j]);
				}
				else {
					cell[cellText] = rows[i][j];
				}
				if(i < rows.length - 1 && cellText != $(rows[i + 1][j]).text()) {
					highlight(rows[i][j], rows[i + 1][j]);
				}
			}
		}
	}
	$('.td').change(compareHeaders());*/
	
</script>
