<?php
include_once "includes/permission.php";
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<title>Result-Based Monitoring &amp; Evaluation System</title>

	<!-- Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

	<!-- fontawesome  -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<!-- Favicon-->
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">

	<!-- Bootstrap Core Css -->
	<link href="projtrac-dashboard/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

	<!-- Waves Effect Css -->
	<link href="projtrac-dashboard/plugins/node-waves/waves.css" rel="stylesheet" />

	<!-- Animation Css -->
	<link href="projtrac-dashboard/plugins/animate-css/animate.css" rel="stylesheet" />

	<!--WaitMe Css-->
	<link href="projtrac-dashboard/plugins/waitme/waitMe.css" rel="stylesheet" />

	<!-- Multi Select Css -->
	<link href="projtrac-dashboard/plugins/multi-select/css/multi-select.css" rel="stylesheet">

	<!-- Bootstrap Spinner Css -->
	<link href="projtrac-dashboard/plugins/jquery-spinner/css/bootstrap-spinner.css" rel="stylesheet">

	<!-- Bootstrap Tagsinput Css -->
	<link href="projtrac-dashboard/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css" rel="stylesheet">

	<!-- Bootstrap Select Css -->
	<link href="projtrac-dashboard/plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />

	<!-- JQuery DataTable Css -->
	<link href="projtrac-dashboard/plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">

	<!-- Sweet Alert Css -->
	<link href="projtrac-dashboard/plugins/sweetalert/sweetalert.css" rel="stylesheet" />

	<!-- Custom Css -->
	<link href="projtrac-dashboard/css/style.css" rel="stylesheet">

	<!-- AdminBSB Themes. You can choose a theme from css/themes instead of get all themes -->
	<link href="projtrac-dashboard/css/themes/all-themes.css" rel="stylesheet" />

	<link rel="stylesheet" href="projtrac-dashboard/ajxmenu.css" type="text/css" />

	<!-- Jquery Core Js -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

	<!-- Sweet Alert Css -->
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

	<script src="projtrac-dashboard/ajxmenu.js" type="text/javascript"></script>

	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<!--CUSTOM MAIN STYLES-->
	<link href="assets/css/custom.css" rel="stylesheet" />

	<!-- date-picker -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" rel="stylesheet" />

	<style>
		.modal-lg {
			max-width: 100% !important;
			width: 90%;
		}

		#links a {
			color: #FFFFFF;
			text-decoration: none;
		}

		hr {
			display: block;
			margin-top: 0.5em;
			margin-bottom: 0.5em;
			margin-left: auto;
			margin-right: auto;
			border-style: inset;
			border-width: 1px;
		}

		@media (min-width: 1200px) {
			.modal-lg {
				width: 90%;
			}
		}

		.bootstrap-select .dropdown-menu {
			margin: 15px 0 0;
			padding: 15px;
		}
	</style>
</head>

<body class="theme-blue">
	<!-- Page Loader --
	<div class="page-loader-wrapper">
		<div class="loader">
			<div class="preloader">
				<div class="spinner-layer pl-red">
					<div class="circle-clipper left">
						<div class="circle">...</div>
					</div>
					<div class="circle-clipper right">
						<div class="circle">...</div>
					</div>
				</div>
			</div>
			<p>Please wait...</p>
		</div>
	</div>
	<!-- #END# Page Loader -->

	<!-- Overlay For Sidebars -->
	<div class="overlay"></div>
	<!-- #END# Overlay For Sidebars -->

	<!-- Top Bar -->
	<nav class="navbar" style="height:69px; padding-top:-10px">
		<div class="container-fluid">
			<div class="navbar-header">
				<a href="projects.php" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
				<a href="projects.php" class="bars"></a>
				<a href="projects.php">
					<img src="assets/images/logo.png" alt="logo" width="239" height="39">
				</a>
			</div>
			<?php
			include_once("includes/notifications.php");
			?>
		</div>
	</nav>
	<!-- #Top Bar -->