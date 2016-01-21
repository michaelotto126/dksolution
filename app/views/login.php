<!DOCTYPE html>
<html>
	<head>
		<title>Digital KickStart - Login</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!-- Incude bootstrap -->
		<link href="<?php echo asset('css/bootstrap.min.css'); ?>" rel="stylesheet" media="screen">
		<!-- Custom icons -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
		<!-- Custom fonts -->
		<link href='//fonts.googleapis.com/css?family=Maven+Pro:400,500,700,900' rel='stylesheet' type='text/css'>
		<!-- Main stylesheet -->
		<link href="<?php echo asset('css/kato.css'); ?>" rel="stylesheet">
	</head>
<body class="full-page">
	
  <form class="login-box" method="post" action="<?php echo url("admin/login"); ?>">
		
		<h1><img src="<?php echo asset('img/logo.png'); ?>" /></h1>
		
		<?php echo View::make("admin/common/alerts"); ?>

		<input type="text" name="username" class="form-control" placeholder="Enter username" />

		<input type="password" name="password" class="form-control" placeholder="Enter password" />

		<input class="btn" type="submit" value="Login" />

		<?php /*<a href="#">Forgot password?</a>*/?>

	</form>
	
	<!-- jQuery Framework -->
	<script src="<?php echo asset('js/jquery.js'); ?>"></script>
	<!-- Raphael Framework -->
	<script src="<?php echo asset('js/raphael.min.js'); ?>"></script>
	<!-- Morris.js Graphs -->
	<script src="<?php echo asset('js/morris.min.js'); ?>"></script>
	<!-- Small Graphs -->
	<script src="<?php echo asset('js/sparklines.min.js'); ?>"></script>
	<!-- Knobs -->
	<script src="<?php echo asset('js/knob.js'); ?>"></script>
	<!-- Bootstrap Javascript -->
	<script src="<?php echo asset('js/bootstrap.min.js'); ?>"></script>
	<!-- Full calendar -->
	<script src="<?php echo asset('js/fullcalendar.min.js'); ?>"></script>
	<!-- Kato Javascript -->
	<script src="<?php echo asset('js/theme.js'); ?>"></script>
</body>
</html>