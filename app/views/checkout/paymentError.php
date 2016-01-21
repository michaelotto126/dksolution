<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Payment Processing - <?php echo $product->name;?></title>
	
	<link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<link href="<?php echo asset('checkoutAssets/css/gumby.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/dkorder.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/oto.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/branding_wi.css'); ?>" rel="stylesheet" type="text/css" />
	
<style>
body {background-image:none;background-color:<?php echo (!empty($colors->background_color) ? $colors->background_color : NULL); ?>}
.bigOrderBTN {background-color:<?php echo (!empty($colors->cta_btn_color) ? $colors->cta_btn_color : NULL); ?>}
.bigOrderBTN:hover {background-color:<?php echo (!empty($colors->cta_btn_hover_color) ? $colors->cta_btn_hover_color : NULL); ?>}
</style>

</head>
<body>

	<!-- Top Area Wrapper -->
	<div class="topWrapper">
		<div class="logoBranding">
			<!-- <img src="images/wilogo.png" alt=""> -->
		</div>
	</div>

	<!-- Headline Area -->
	<div class="topHeadline">
		<div class="productLogo">
			<img src="<?php echo url("checkout/show-secure-image?url=" . urlencode($product->logo_url)); ?>" alt="">
		</div>
		<div class="productLogoTag">
			<i class="icon-lock" style="margin-right: 10px;" ></i> Secure Verified Payment
			<span style="margin-left: 15px;">
				<img src="<?php echo asset('checkoutAssets/images/visa.png'); ?>" alt="" style="margin-bottom:-9px;">
				<img src="<?php echo asset('checkoutAssets/images/mastercard.png'); ?>" alt="" style="margin-bottom:-9px; margin-left:10px;">
				<img src="<?php echo asset('checkoutAssets/images/amex.png'); ?>" alt="" style="margin-bottom:-9px; margin-left:10px;">
				<img src="<?php echo asset('checkoutAssets/images/paypal.png'); ?>" alt="" style="margin-bottom:-9px; margin-left:10px;">
			</span>
		</div>
		<br clear="both" />
	</div>

	<!-- Order Form Wrapper -->
	<div class="orderWrapper">
		
	
		<div class="mainOrderBlock">

			<div class="otoHeadline">
				
				<div class="otoHeading" > Please Wait a Few Seconds - Processing Your Payment... </div>
				<div class="otoSubHeading" >This Page Will Redirect To The Next Step Once Transaction Is Finished...</div>

				<i class="icon-spinner icon-spin icon-4x"></i>

				<div class="receipt">
					<strong><?php echo $product->name;?> - <?php echo $plan->name; ?></strong>
					<span class="otoPrice" ><?php echo "$" . $plan->price; ?></span>
				</div>

				<!-- <i class="icon-spinner icon-spin icon-4x"></i> -->
			</div>

			 

			<div class="bigOrderFinePrint">
				* We are processing your payment with the Credit Card we have on file... <strong>DO NOT REFESH THIS PAGE!</strong> Once the transaction is done you will be emailed your receipt and redirected to the next step... *
			</div>

		</div>

	</div>

	<!-- Footer Block Wrapper -->
	<div class="footerWrapper">
		
		<!-- <div class="productLogo">
			<img src="http://digitalkickstart.com/wp-content/uploads/2013/09/logo.png" width="141 " alt="">
		</div> -->

		<div class="footerLinks">
			<i class="icon-lock" style="margin-right: 10px;" ></i> Secure Payments Through Stripe 
			<span>-</span>
			<!-- <a href="#">Privacy Policy</a> 
			<span>-</span> 
			<a href="#">Terms Of Service</a>
			<span>-</span>
			<a href="#">Support Desk</a>
			<span>-</span> -->
			All Rights Reserved
			<span>-</span>
			DigitalKickStart @ 2013
		</div>
		
		<br clear="both" />

	</div>
	
</body>

</html>