<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>DigitalKickstart - Update Card Details</title>
	
	<link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<link href="<?php echo asset('checkoutAssets/css/gumby.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/dkorder.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/oto.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/branding_wi.css'); ?>" rel="stylesheet" type="text/css" />

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
			<img src="<?php echo asset('img/logo.png'); ?>" alt="DigitalKickstart" style="margin-top:9px;">
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

			<?php if(empty($urlError) AND !Session::has('success')): ?>

			<form method="post" id="frmUpdateCard">
				<input type="hidden" name="email" value="<?php echo $email; ?>">
				<input type="hidden" name="code" value="<?php echo $product->code; ?>">

				<div class="otoHeadline">
					
					<?php if(Session::has('error')): ?>
						<div style="color:red;"><?php echo Session::get('error'); ?></div>
					<?php endif; ?>

					<div class="otoHeading"><?php echo $product->name; ?> - Update Card</div>
					<div class="otoSubHeading">Provide new card details for "<strong><?php echo $email; ?></strong>" account</div>

					<div id="stripe-form" style="margin:0 auto;width:500px;">
						<div class="field">
							<span class="fieldLabel">Credit Card Number</span>
							<input class="input" type="text" name="ccNum" id="ccNum" placeholder="Credit Card Number..." value="<?php echo Session::get('ccNum'); ?>" />
						</div>
						
						<div class="ccExpireCopy">
							<span class="fieldLabel">Expiration Date</span>
						</div>

						<div class="field ccExpire">
							<input class="input" type="text" name="ccExpire" id="ccExpire" placeholder="MM / YY" value="<?php echo Session::get('ccExpire'); ?>" />
						</div>
						
						<div class="field ccCSVCopy">
							<span class="fieldLabel">CVV Code</span>
						</div>

						<div class="field ccCSV">
							<input class="input" type="text" name="ccCVC" id="ccCVC" placeholder="CVV" value="<?php echo Session::get('ccCVC'); ?>" />
						</div>

						<br clear="left" />

						<div class="ccCards">
							<img src="<?php echo asset('checkoutAssets/images/powered-by-stripe.png'); ?>" /><i class="icon-lock" style="margin-right: 10px;" ></i> Secure Credit Card Processing
						</div>
					</div>

					<div class="mainOrderBlock" style="border:none;margin:0 auto;width:500px;">
						<div class="bigOrderBTN" style="padding:10px;">
							<span class="bigOrderHeading">Update My Card</span>
						</div>
					</div>
				</div>
			</form>

			<div class="bigOrderFinePrint">
				* Your new card will be used for all future payments of your subscription.
			</div>

			<?php elseif(Session::has('success')): ?>
				<p style="text-align: center;color:green;"><strong><?php echo Session::get('success'); ?></strong></p>
			<?php else: ?>
				<p style="text-align: center;color:red;"><strong><?php echo $urlError; ?></strong></p>
			<?php endif; ?>

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
			DigitalKickStart &copy; <?php echo date("Y") ?>
		</div>
		
		<br clear="both" />

	</div>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<!-- Stripe -->
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="<?php echo asset('checkoutAssets/js/formance.js'); ?>" /></script>
<script src="<?php echo asset('checkoutAssets/js/awesome_form.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {

	// Validation On Form
	$('#ccNum').formance('format_credit_card_number');
	$('#ccExpire').formance('format_credit_card_expiry');
	$('#ccCVC').formance('format_credit_card_cvc');

	// Submit form
	$(".bigOrderBTN").click(function(e){
		$("#frmUpdateCard").submit();
	});

});
</script>
	
</body>

</html>