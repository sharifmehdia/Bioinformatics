<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>miRNA - MicroRNA and Term Association Database Tool</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <link rel="stylesheet" href="<?php echo base_url('theme/css/bootstrap.min.css')?>">    <link rel="stylesheet" href="<?php echo base_url('theme/css/main.css')?>">

    <script src="<?php echo base_url('theme/js/vendor/modernizr-2.8.3-respond-1.4.2.min.js')?>"></script>
    <!-- cdnjs -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url('theme/cyto/js/cytoscape_web/json2.min.js')?>"></script>
	<script type="text/javascript" src="<?php echo base_url('theme/cyto/js/cytoscape_web/AC_OETags.min.js')?>"></script>
	<script type="text/javascript" src="<?php echo base_url('theme/cyto/js/cytoscape_web/cytoscapeweb.min.js')?>"></script>
	<script type="text/javascript" src="<?php echo base_url('assets/hilitor.js')?>"></script>
	<style>
		.ui-autocomplete {
			height: 300px;
			overflow-y: auto;
			overflow-x: hidden;
		}
		* { margin: 0; padding: 0; font-family: Helvetica, Arial, Verdana, sans-serif; }
			html, body { height: 100%; width: 100%; padding: 0; margin: 0; }
			body { line-height: 1.5; color: #000000; font-size: 14px; }
			/* The Cytoscape Web container must have its dimensions set. */
			#cytoscapeweb { width: 100%; height: 90%; }
			#note h1 {
				padding: 10px;
				background-color : #688eff;
				color: #fff;
				font-size: 16px;
			}
			#note {
				padding: 20px;
				border: 2px solid #688eff;
			}
			p { padding: 0 0.5em; margin: 0; }
			p:first-child { padding-top: 0.5em; }
			p.connection_range {
				margin: 10px;
				padding: 10px;
			}
	</style>
</head>
<body>