<?php 
$classes = "{$setup['smart-styles']} {$setup['topmenu']} {$setup['fixed-header']} {$setup['fixed-navigation']} {$setup['fixed-ribbon']} {$setup['fixed-footer']} {$setup['fixed-container']} {$setup['rtl']} {$setup['topmenu']} {$setup['colorblind-friendly']}";
?>
<!DOCTYPE html>
<html lang="en-us" <?php echo implode(' ', array_map(function($prop, $value) {
			return $prop.'="'.$value.'"';
		}, array_keys($page_html_prop), $page_html_prop)) ;?>>
	<head>
		<meta charset="utf-8">
		<!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

		<title> <?php echo $page_title != "" ? $page_title." - " : ""; ?>PRONET </title>
		<meta name="description" content="">
		<meta name="author" content="">

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<!-- Basic Styles -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/font-awesome.min.css">

		<!-- SmartAdmin Styles : Caution! DO NOT change the order -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/smartadmin-production-plugins.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/smartadmin-production.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/smartadmin-skins.min.css">
		<?php

			if ($page_css) {
				foreach ($page_css as $css) {
					echo '<link rel="stylesheet" type="text/css" media="screen" href="'.base_url().'assets/css/'.$css.'">';
				}
			}
		?>

	</head>
	<body class='<?=$classes?>' <?php echo implode(' ', array_map(function($prop, $value) {
			return $prop.'="'.$value.'"';
		}, array_keys($page_body_prop), $page_body_prop)) ;?> id='bodyurl' url='<?=base_url()?>'  siteenc="<?=site_url('main/encrypt_url')?>" >

		<!-- POSSIBLE CLASSES: minified, fixed-ribbon, fixed-header, fixed-width
			 You can also add different skin classes such as "smart-skin-1", "smart-skin-2" etc...-->
		<?php
			if (!$no_main_header) {

		?>
				<!-- HEADER -->
				<header id="header">
					<div id="logo-group">
						<!-- PLACE YOUR LOGO HERE -->
						<!--<span id="logo"> <img src="<?php echo base_url(); ?>page_assets/img/logo.png" alt="EPoint" width="246px" height="52px" > </span> -->
						<!-- END LOGO PLACEHOLDER -->
					</div>

					<!-- pulled right: nav area -->
					<div class="pull-right">

						<!-- collapse menu button -->
						<div id="hide-menu" class="btn-header pull-right">
							<span> <a href="javascript:void(0);" title="Collapse Menu" data-action="toggleMenu"><i class="fa fa-reorder"></i></a> </span>
						</div>
						<!-- end collapse menu -->

						<!-- #MOBILE -->
						<!-- Top menu profile link : this shows only when top menu is active -->
						<ul id="mobile-profile-img" class="header-dropdown-list hidden-xs padding-5">
							<li class="">
								<a href="#" class="dropdown-toggle no-margin userdropdown" data-toggle="dropdown"> 
									<img src="<?=base_url();?>assets/img/avatars/sunny.png" alt="" class="online" />
								</a>
								<ul class="dropdown-menu pull-right">
									<li>
										<a href="#" class="padding-10 padding-top-0 padding-bottom-0" title="Change Password" ><i class="fa fa-cog"></i> <u>C</u>hange Password</a>
									</li>
									<li class="divider"></li>
									<li>
										<a href="#ajax/profile.php" class="padding-10 padding-top-0 padding-bottom-0"> <i class="fa fa-user"></i> <u>P</u>rofile</a>
									</li>
									<li class="divider"></li>
									<li>
										<a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0" data-action="launchFullscreen"><i class="fa fa-arrows-alt"></i> Full <u>S</u>creen</a>
									</li>
									<li class="divider"></li>
									<li>
										<a href="<?=site_url("main/logout")?>" class="padding-10 padding-top-5 padding-bottom-5" data-action="userLogout"><i class="fa fa-sign-out fa-lg"></i> <strong><u>L</u>ogout</strong></a>
									</li>
								</ul>
							</li>
						</ul>

						<!-- Welcome -->
						<div class="project-context hidden-xs pull-right">

							<span class="label">Welcome</span>
							<span style="text-color:#646E75 !important; font-size:13px"  class="" ><?php echo $this->session->userdata('fullname');?></span>
						</div>
						<!-- end Welcome -->

						<!-- logout button -->
						<div id="logout" class="btn-header transparent pull-right">
							<span> <a href="<?=site_url("main/logout")?>" title="Sign Out" data-action="userLogout" data-logout-msg="You can improve your security further after logging out by closing this opened browser"><i class="fa fa-sign-out"></i></a> </span>
						</div>
						<!-- end logout button -->

						<!-- fullscreen button -->
						<!--
						<div id="fullscreen" class="btn-header transparent pull-right">
							<span> <a href="javascript:void(0);" title="Full Screen" data-action="launchFullscreen"><i class="fa fa-arrows-alt"></i></a> </span>
						</div>
						-->
						<!-- end fullscreen button -->
					</div>
					<!-- end pulled right: nav area -->

				</header>
				<!-- END HEADER -->
		<?php
			}
		?>