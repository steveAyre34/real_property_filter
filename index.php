<?php	
	require('common.php');
?>

<!DOCTYPE html>

<html>
<head>
<title>Welcome to the New York State Real Property Data Filter</title>
<!--<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">-->
<meta content="text/html;charset=utf-8" http-equiv-"Content-Type">
<meta content="utf-8" http-equiv="encoding">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
</head>

<body bgcolor="#b3d5ff">
<h1>Welcome to the New York State Real Property Data Filter</h1>

<p><strong>Please Select A County:</strong></p>
<form id="form">
<select name="county" id="chosenCounty">
<?php
	foreach($counties as $c) {
	  $disabled = "";
	  if (!in_array($c, $counties_available)) {
		$disabled = "disabled"; 
		
	  ?><option value='<?php echo $c ?>' <?php echo $disabled ?>><?php echo ucwords($c) ?></option><?php  }
	  else {
		  ?><option value='<?php echo $c ?>'><?php echo ucwords($c) ?></option>
	<?php }} ?>
</select>
<input type="submit" value="Go" formmethod="GET" formaction="select2.php"/><br>
<input type="submit" value="Import" formmethod="GET" formaction="importChooseCounty.php"/>
    <input type="submit" value="Export" formmethod="POST" formmaction="do_export.php"/>
</form>