<?php
/**
 * Created by PhpStorm.
 * User: yarn23
 * Date: 4/26/17
 * Time: 12:42 AM
 */
?>
<html>
<head>
    <script src="jquery-ui-1.12.1.custom/external/jquery/jquery.js"></script>
    <script src="jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.min.css"/>
    <link rel="stylesheet" href="jquery-ui-1.12.1.custom/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" href="common.css"/>
</head>
<body>
    <h1>Welcome to the NYS Mailing Information Filter!</h1>
    <h3>Choose your filter</h3>
    <button class="ui-button" type="submit" onclick="window.location.href='indexRP.php'" style="width:25%; height:20%;font-size:1.5em;">Real Property</button>
    <button class="ui-button" type="submit" onclick="window.location.href='../boeFilter/index.php'" style="width:25%; height:20%;font-size:1.5em;">BOE (Under Construction)</button>
    <button class="ui-button" type="submit" style="width:50%; height:20%;font-size: 1.5em;">Both (Under Construction)</button>