<?php 

/*
	This is the input form that will be displayed within the pop-up window.
*/
echo "<html>
<head>
	<link rel='stylesheet' type='text/css' href='import.css'>
	<link rel='stylesheet' href='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css'>
	<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
</head>
<body>
<div id='headerForm' title='Set header fields'>
	 <form>
		 <fieldset>
			<input type='text' name='fieldName' id='fieldName' value='field name'/>Field Name</input>";  
			//print_r(); 
			echo /*"'>field name </>*/"
		</fieldset>
	</form>
</div>
</html>";

?>