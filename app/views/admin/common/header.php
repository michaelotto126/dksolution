<!DOCTYPE html>
<html>
	<head>
		<title><?php echo (!empty($page_title) ? $page_title : "Digital KickStart"); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<!-- Incude bootstrap -->
		<link href="<?php echo asset('css/bootstrap.min.css'); ?>" rel="stylesheet" media="screen">
		<!-- Custom icons -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
		<!-- Custom fonts -->
		<link href='//fonts.googleapis.com/css?family=Maven+Pro:400,500,700,900' rel='stylesheet' type='text/css'>
		<!-- Plugins stylesheet -->
		<link href="<?php echo asset('css/datepicker.css'); ?>" rel="stylesheet">
		<!-- Main stylesheet -->
		<link href="<?php echo asset('css/kato.css'); ?>" rel="stylesheet">
        <link href="<?php echo asset('css/own.css'); ?>" rel="stylesheet">

        <script>
        var BASE_URL = '<?php echo url(); ?>';
        </script>
	</head>
<body>

  <div id="wrapper">
	
		
		<!-- Content area -->
		<div id="content-wrapper">
			
			<!-- Site header -->
			<header class="main-header">
				
				<!-- Page title -->
				<h3>
					<img src="<?php echo asset('img/logo.png'); ?>" />
				</h3>
				
				<!-- Profile dropdown -->
				<nav class="user-profile">
					<ul class="nav nav-pills">
						<li <?php echo ((!empty($section) AND $section == "transactions") ? 'class="active"' : NULL); ?>><a href="<?php echo url("admin/transactions"); ?>">Transactions</a></li>
                        
                        <?php $support_users = array('support', 'rhoda', 'jedfelices'); ?>
                        <?php if(!in_array(Auth::user()->username, $support_users)): ?>
                        <li <?php echo ((!empty($section) AND $section == "products") ? 'class="active"' : NULL); ?>><a href="<?php echo url("admin/products"); ?>">Products</a></li>
                        <?php endif; ?>

                        <li <?php echo ((!empty($section) AND $section == "licenses") ? 'class="active"' : NULL); ?>><a href="<?php echo url("admin/licenses"); ?>">Licenses</a></li>
                        <li <?php echo ((!empty($section) AND $section == "users") ? 'class="active"' : NULL); ?>><a href="<?php echo url("admin/customers"); ?>">Customers</a></li>
                        <li <?php echo ((!empty($section) AND $section == "utilities") ? 'class="active"' : NULL); ?>><a href="<?php echo url("admin/utilities"); ?>">Utilities</a></li>
                        <li><a href="<?php echo url("admin/logout"); ?>">Logout</a></li>
					</ul>
				</nav>

			</header>
			<!-- /Site header -->