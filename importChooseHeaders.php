<?php
	require("connection.php");
	
	$filenames = array();
	$county = $_POST['county'];
	foreach($_FILES['uploadFile']['name'] as $f) {
		array_push($filenames, $f);
	}
	
	function getFileHeaders($filename) {
		$upload_dir = 'C:\xampp\mysql\data\rp2_database\\' . $_POST['county'] . '\\'; 
		$tmp_name = $upload_dir . $filename;
		$name = 'Y:\RP 2017 Data Files\\' . $_POST['county'] . '\\' . $filename;
		copy($name, $tmp_name);
			
		//Open file to be uploaded ('countyName_fileName.txt')
		$importFile = fopen($upload_dir . $filename, "r") or die("Unable to open file.");
		
		$fileHeaders = fgets($importFile);
		$fileHeaders = explode("\t", $fileHeaders);
		
		return $fileHeaders;
	}	
?>

<!--
	Displays each file as a button. When user clicks a button they will see a pop-up input form to set the fields for the corresponding table.
-->
<html>
<head>
	<link rel='stylesheet' type='text/css' href='import.css'>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>

<body>
	<p>These are the files for <?php echo $county ?> that you have selected to upload.</p><br><br>
	<p>Click any file to set the headers for its corresponding database table.</p><br><br>
	<?php foreach($filenames as $f) { ?>
		<button type="button" class="button" onclick="setHeaders('<?php echo $f ?>')" value="<?php echo $f; ?>"> <?php echo $f; ?> </button><br><br><br>
	<?php 
	} 
	?>
</body>
</html>


<script type="text/javascript">
	function setHeaders(filename) {
		var myData = { functionname: 'getFileHeaders', arguments: filename };
		$.ajax({
			type: "GET",
			url: "headerForm.php",
			data: {myData},
			dataType: 'php',
			success: function(response) {
				returnVal = window.showModalDialog(response);
			}	
		});
	}
</script>