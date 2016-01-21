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
					<strong><?php echo $plan->name; ?></strong>
					<!-- <span class="otoPrice" ><?php echo "$" . $plan->price; ?></span> -->
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
			DigitalKickStart &copy; <?php echo date("Y") ?>
		</div>
		
		<br clear="both" />

	</div>
	
</body>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<script src="<?php echo asset('checkoutAssets/js/formance.js'); ?>" /></script>
<script src="<?php echo asset('checkoutAssets/js/awesome_form.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {

	<?php 
		$extra_url = $existing_customer ? '?existing=1' : NULL;

		if($buyer_id) $extra_url .= '&buyer=' . $buyer_id;

		if($paytype AND $extra_url) $extra_url .= '&paytype=' . $paytype;
		if($paytype AND !$extra_url) $extra_url = '?paytype=' . $paytype;

		if($extra_url AND $next_page_url) $extra_url .= '&next_url=' . $next_page_url;
		if(!$extra_url AND $next_page_url) $extra_url = '?next_url=' . $next_page_url;

		$next_page_url = $next_page_url ? $next_page_url : $plan->next_page_url;
	?>
	
	<?php if($gateway == "paypal"): ?>
	// Send AJAX call to make a payment
	var postData = "pay_option=<?php echo $pay_option; ?>&existing_customer=<?php echo $existing_customer; ?>&buyer=<?php echo $buyer_id; ?>&product=<?php echo $product->code; ?>&plan=<?php echo $plan->code; ?>&email=<?php echo urlencode($email); ?>&first_name=<?php echo $first_name; ?>&last_name=<?php echo $last_name; ?>&password=<?php echo $password; ?>&retype_password=<?php echo $retype_password; ?>&ccNum=<?php echo $ccNum; ?>&ccExpire=<?php echo $ccExpire; ?>&ccCSV=<?php echo $ccCSV; ?>";
	$.ajax({
		  type: "POST",
		  url: '<?php echo url("checkout/paypal"); ?>',
		  data: postData,
		  dataType: "JSON",
		  success: function(data){
			if(typeof(data.success) != "undefined") {
				// Redirect to next URL
				window.top.location = data.url;
			} else {
				// Redirect to Order page again
				window.top.location = '<?php echo url("checkout/$product->code/$plan->code" . $extra_url); ?>';
			}
		  }
		});
	<?php elseif($gateway == "stripe"): ?>
	// Send AJAX call to make a payment
	var postData = "pay_option=<?php echo $pay_option; ?>&existing_customer=<?php echo $existing_customer; ?>&buyer=<?php echo $buyer_id; ?>&product=<?php echo $product->code; ?>&plan=<?php echo $plan->code; ?>&email=<?php echo urlencode($email); ?>&first_name=<?php echo $first_name; ?>&last_name=<?php echo $last_name; ?>&password=<?php echo $password; ?>&retype_password=<?php echo $retype_password; ?>&ccNum=<?php echo $ccNum; ?>&ccExpire=<?php echo $ccExpire; ?>&ccCSV=<?php echo $ccCSV; ?>";
	$.ajax({
		  type: "POST",
		  url: '<?php echo url("checkout/stripe"); ?>',
		  data: postData,
		  dataType: "JSON",
		  success: function(data){
			if(typeof(data.success) != "undefined") {
				// Redirect to next URL
				window.top.location = "<?php echo url('checkout/thanks?url=' . $next_page_url . '&code=' . $product->code); ?>";
			} else {
				// Redirect to Order page again
				window.top.location = '<?php echo url("checkout/$product->code/$plan->code" . $extra_url); ?>';
			}
		  }
		});
	<?php endif; ?>

});
</script>

</html>