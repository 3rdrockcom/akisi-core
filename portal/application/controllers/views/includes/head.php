<?php 

//initilize the page
require_once 'init.php';

//ribbon breadcrumbs config
$breadcrumbs = array(
	//"Home" => APP_URL
);

$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
/*---------------- PHP Custom Scripts ---------

YOU CAN SET CONFIGURATION VARIABLES HERE BEFORE IT GOES TO NAV, RIBBON, ETC.
E.G. $page_title = "Custom Title" */

#$page_title = "Dashboard"
/* ---------------- END PHP Custom Scripts ------------- */


//you can add your custom css in $page_css array.
//Note: all css files are inside css/ folder
$page_css[] = "your_style.css";
include("inc/header.php");


include("inc/nav.php");



?>
<!-- ==========================CONTENT STARTS HERE ========================== -->
<!-- MAIN PANEL -->
<div id="main" role="main">
	<?php
		//configure ribbon (breadcrumbs) array("name"=>"url"), leave url empty if no url
		//$breadcrumbs["New Crumb"] => "http://url.com"
		include("inc/ribbon.php");
		//include required scripts
		include("inc/scripts.php"); 
		include("confirmation.php"); 
	?>

	<!-- MAIN CONTENT -->
	<div id="content">
