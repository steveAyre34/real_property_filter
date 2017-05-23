<?php
	include("common.php");
	$county = $_GET['county'];
?>

<html>
<head>
    <script src="sweetalert2.min.js"></script>
    <link rel="stylesheet" href="sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/core-js/2.4.1/core.js"></script>
    <link rel="stylesheet" type="text/css" href="common.css"/>
    <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
</head>
<body>
<form id="import_form" action="do_import.php" method="POST" enctype="multipart/form-data" accept-charset="utf-8">
	<input class="ui-button" style="height:10%;width:10%;" type="file" id="uploadFile" name="uploadFile[]" multiple="multiple"/><br><br>
	<input type="hidden" name="county" value="<?php echo $county ?>"/>
	<button class="ui-button" style="height:15%;width:15%;" type="submit" id="submit" name="btn btn-submit">Submit</button>
    <img id='loading' src="loader_blue.gif" alt="Uploading...Please be patient!" style="display:none;margin-left:auto; margin-right:auto;vertical-align:middle;">
</form>
</body>
</html>

<script type="text/javascript">
    $('#import_form').submit(function({
        $('#uploadFile').hide();
        $('.ui-button').hide();
        $('#loading').show();
        document.getElementById('loading').style.display = "block";
        /*swal({
            title: 'Upload',
            text: 'Uploading...',
            imageUrl: 'loader_blue.gif',
            imageWidth: 400,
            imageHeight: 200,
            animation: false,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonClass: 'btn btn-danger'
        },
        function(dismiss) {
            if(dismiss = 'cancel') {
                window.location.href('index.php');
            }
        })*/
    });
</script>


