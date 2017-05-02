<?php	
	require('common.php');
   // session_start();

?>

<!DOCTYPE html>

<html>
<head>
<title>Welcome to the New York State Real Property Data Filter</title>
    <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" href="common.css"/>
</head>

<body>
<h1>Welcome to the New York State Real Property Data Filter</h1>

<h4>Please Select A County</h4>
<form id="form">
<select class="selectMenu" style="font-size:0.75em;" name="county" id="chosenCounty">
<?php
	foreach($counties as $c) {
	  $disabled = "";
	  if (in_array($c, $counties_available)) {
		  ?><option value='<?php echo $c ?>'><?php echo ucwords($c) ?></option>
	<?php }} ?>
</select>
    <br><br>
<input class="ui-button" style="width:10%;" type="submit" value="Filter" formmethod="GET" formaction="selectQuery.php"/>
<input class="ui-button" style="width:10%;" type="submit" value="Import" formmethod="GET" formaction="importChooseCounty.php"/>
</form>
</body>

<?php
mysqli_close($link);
?>