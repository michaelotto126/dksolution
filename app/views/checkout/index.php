<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Order Form - <?php echo $product->name;?></title>
	
	<link href='//fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css" rel="stylesheet">
	<link href="<?php echo asset('checkoutAssets/css/gumby.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/dkorder.css'); ?>" rel="stylesheet" type="text/css" />
	<link href="<?php echo asset('checkoutAssets/css/branding_wi.css'); ?>" rel="stylesheet" type="text/css" />

<style>
body {background-image:none;background-color:<?php echo (!empty($colors->background_color) ? $colors->background_color : NULL); ?>}
.bigOrderBTN {background-color:<?php echo (!empty($colors->cta_btn_color) ? $colors->cta_btn_color : NULL); ?>}
.bigOrderBTN:hover {background-color:<?php echo (!empty($colors->cta_btn_hover_color) ? $colors->cta_btn_hover_color : NULL); ?>}

.errors {width:960px;margin:0 auto;padding:5px;margin-bottom:5px;background:red;color:white;}
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

	<form method="post" action="<?php echo url('checkout/processing'); ?>" id="frmOrder" autocomplete="off">
	<input type="hidden" name="product" value="<?php echo $product->code; ?>" />
	<input type="hidden" name="plan" value="<?php echo $plan->code; ?>" />
	<input type="hidden" name="gateway" value="stripe" id="gateway" />
	<input type="hidden" name="existing_customer" value="<?php echo $existing_customer; ?>" />
	<input type="hidden" name="buyer" value="<?php echo $buyer ? $buyer->id : ''; ?>" />
	<input type="hidden" name="next_url" value="<?php echo $next_page_url; ?>" />
	<input type="hidden" name="paytype" value="<?php echo $paytype; ?>" />
	
	<?php if(Session::get('error')): ?>
		<div class="errors"><?php echo Session::get('error'); ?></div>
	<?php endif; ?>
	
	<!-- Order Form Wrapper -->
	<div class="orderWrapper">
		
		<!-- Oder Form - Left Side -->
		<div class="orderLeft">
			
			<!-- Create Account - Only For WL Plugins (One Time Payment) -->
			<div class="createNewAccount">
				
				<div class="createNewAccount_headline">
					<div class="orderSectionHeading orderSectionHeader">
						<?php if(!$existing_customer): ?>
						<span class="orderHeading" >Step #1: Create Your New Account</span>
						<?php else: ?>
						<span class="orderHeading" >Step #1: Your Account</span>
						<?php endif; ?>
						<span class="orderSubHeading" >Enter your basic contact details below...</span>	
					</div>

					<!-- <div class="orderSectionIcon orderSectionNumber">
						<i class="icon-user icon-3x"></i>
					</div> -->
					
					<br clear="both" />
				</div>

				<div class="createNewAccount_form orderSectionInner">

					<div class="field">
						<span class="fieldLabel" >First Name:</span>
						<input class="input" type="text" name="first_name" id="userFirstName" placeholder="Your First Name..." <?php echo ($buyer ? 'readonly' : NULL); ?> value="<?php echo ($buyer ? $buyer->first_name : Session::get('first_name')); ?>" />
					</div>
					<div class="field">
						<span class="fieldLabel" >Last Name:</span>
						<input class="input" type="text" name="last_name" id="userLastName" placeholder="Your Last Name..." <?php echo ($buyer ? 'readonly' : NULL); ?> value="<?php echo ($buyer ? $buyer->last_name : Session::get('last_name')); ?>" />
					</div>
					<div class="field">
						<span class="fieldLabel" >Email Address:</span>
						<input class="input" type="text" name="email" id="userEmail" placeholder="Your Best Email Address..." <?php echo ($buyer ? 'readonly' : NULL); ?> value="<?php echo ($buyer ? $buyer->email : Session::get('email')); ?>" />
					</div>
					
					<?php if(!$existing_customer): ?>
					<div class="field">
						<span class="fieldLabel" >Password:</span>
						<input class="input" type="password" name="password" id="userPassword" placeholder="Secure Password..." />
					</div>
					<div class="field">
						<span class="fieldLabel" >Re-type Password:</span>
						<input class="input" type="password" name="retype_password" id="userRetypePassword" placeholder="Re-type Password..." />
					</div>
					<?php endif; ?>
				</div>

			</div>

			<!-- Payment Details Area -->
			<div class="orderProductArea">
				
				<div class="createNewAccount_headline">
					<div class="orderSectionHeading orderSectionHeader">
						<span class="orderHeading" >Step #2: Enter In Payment Details</span>
						<?php if($product->type == 1): ?><span class="orderSubHeading" >You can either pay with Credit Card Or Paypal...</span><?php endif; ?>
					</div>

					<!-- <div class="orderSectionIcon orderSectionNumber">
						<i class="icon-credit-card icon-3x"></i>
					</div> -->
					
					<br clear="both" />
				</div>

				<div class="createNewAccount_form orderSectionInner">

					<div class="paymentOptions">
						<div class="paymentChoice paymentChoiceActive btnChooseGateway" id="stripe-btn" data-gateway="stripe" style="margin-right: 15px;">
							<img src="<?php echo asset('checkoutAssets/images/visa_straight.png'); ?>" alt="">
							<img src="<?php echo asset('checkoutAssets/images/mastercard_straight.png'); ?>" alt="">
							<img src="<?php echo asset('checkoutAssets/images/american_express_straight.png'); ?>" alt="">
							<img src="<?php echo asset('checkoutAssets/images/discover_straight.png'); ?>" alt="">
						</div>

						<?php
							// If plan is not recurring OR if plan is recurring and PayPal Enabled
							if((!$plan->is_recurring) OR ($plan->is_recurring AND $plan->allow_paypal_sub)):
						?>
						<div class="paymentChoice paymentChoiceNotActive btnChooseGateway" id="paypal-btn" data-gateway="paypal">
							<img src="<?php echo asset('checkoutAssets/images/paypal_straight.png'); ?>" alt="">
						</div>
						<?php endif; ?>

						<br clear="left" />
					</div>

					<div id="stripe-form">
						<div class="field">
							<span class="fieldLabel" >Credit Card Number</span>
							<input class="input" type="text" name="ccNum" id="ccNum" placeholder="Credit Card Number..." value="<?php echo Session::get('ccNum'); ?>" />
						</div>
						
						<div class="ccExpireCopy">
							<span class="fieldLabel" >Expiration Date</span>
						</div>
	
						<div class="field ccExpire">
							<input class="input" type="text" name="ccExpire" id="ccExpire" placeholder="MM / YY" value="<?php echo Session::get('ccExpire'); ?>" />
						</div>
						
						<div class="field ccCSVCopy">
							<span class="fieldLabel" >CVV Code</span>
						</div>
	
						<div class="field ccCSV">
							<input class="input" type="text" name="ccCSV" id="ccCSV" placeholder="CVV" value="<?php echo Session::get('ccCSV'); ?>" />
						</div>
	
						<br clear="left" />
	
						<div class="ccCards">
	
							<img src="<?php echo asset('checkoutAssets/images/powered-by-stripe.png'); ?>" /><i class="icon-lock" style="margin-right: 10px;" ></i> Secure Credit Card Processing
	
							<!-- <img src="http://png.findicons.com/files/icons/2102/credit_card_debit_card/51/visa_straight.png" alt=""> -->
							<!-- <img src="http://png-4.findicons.com/files/icons/2250/payment_icon_set/32/mastercard.png" alt=""> -->
	
						</div>
					</div>
					
					<div id="paypal-form" style="display:none;">
						<div class="ccCards" style="text-align: left;">
							<i class="icon-lock" style="margin-right: 10px;" ></i> You will be sent to PayPal website for secure payment
						</div>
					</div>

					<div class="orderSummary">
						<?php if($plan->has_split_pay): ?>
							<p class="text-center orderSummaryHeading"><strong>Order Summary</strong> <br> Select Product Purchase Plan</p>

							<div style="overflow:hidden;">
								<div style="float:left;">
									<strong><?php echo $product->name . " - " . $plan->name; ?></strong>
								</div>
								<div class="text-right" style="float:right;">
									<strong>Amount</strong>
								</div>
							</div>

							<div id="display_onetime" style="overflow:hidden;margin-top:10px;margin-bottom:10px;">
								<div style="float:left;width:75%;">
									<label><input id="onetimeRad" type="radio" name="pay_option" data-price="<?php echo "$" . $plan->price; ?>" value="full" checked="checked"><?php echo $plan->description; ?></label>
								</div>
								<div class="text-right" style="float:right;">
									<strong><?php echo "$" . $plan->price; ?></strong>
								</div>
							</div>

							<div id="display_split" style="overflow:hidden;margin-top:10px;margin-bottom:20px;">
								<div style="float:left;width:75%;">
									<label><input id="splitRad" type="radio" name="pay_option" data-price="<?php echo "$" . $plan->price_per_installment; ?>" value="split"><?php echo $plan->split_pay_desc; ?></label>
								</div>
								<div class="text-right" style="float:right;">
									<strong><?php echo "$" . $plan->price_per_installment; ?></strong>
								</div>
							</div>

							<div class="paymentSummary" style="overflow:hidden;margin-top:35px;">
								<div style="float:left;width:75%;">
									<strong>Total Amount You Pay Right Now</strong><br>

									<?php if($plan->show_available_plans AND $available_plans AND count($available_plans) > 1): ?>
									<select id="available_plans">
										<?php foreach($available_plans as $available_plan): ?>
											<option value="<?php echo $available_plan->code; ?>" <?php echo $available_plan->code == $plan->code ? 'selected="selected"' : NULL ?>><?php echo $available_plan->name; ?></option>
										<?php endforeach; ?>
									</select>
									<?php else: ?>
										<?php echo $product->name . " - " . $plan->name; ?>
									<?php endif; ?>
								</div>
								<div class="text-right" style="float:right;">
									&nbsp; <br> <span class="total_price"><?php echo "$" . $plan->price; ?></span>
								</div>
							</div>
							
							<div id="total" style="overflow:hidden;">
								<div style="float:left;">
									<strong>Total</strong>
								</div>
								<div class="text-right" style="float:right;">
									<strong class="total_price"><?php echo "$" . $plan->price; ?></strong>
								</div>
							</div>

						<?php else: ?>

							<p class="paymentSummary">
								<strong><?php echo $product->name . " - " . $plan->name; ?></strong><br>
								<?php echo $plan->description; ?>

								<?php if($plan->show_available_plans AND $available_plans AND count($available_plans) > 1): ?>
									<br>
									<a href="javascript:void(0);" id="showAvailablePlans"><small>Change Plan</small></a>

									<select id="available_plans" class="hide">
										<?php foreach($available_plans as $available_plan): ?>
											<option value="<?php echo $available_plan->code; ?>" <?php echo $available_plan->code == $plan->code ? 'selected="selected"' : NULL ?>><?php echo $available_plan->name; ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</p>

							<div style="overflow:hidden;">
								<div style="float:left;">
									<strong>Order Total:</strong>
								</div>
								<div class="text-right" style="float:right;">
									<strong><?php echo "$" . $plan->price; ?></strong>
								</div>
							</div>
						<?php endif; ?>
						
					</div>

				</div>

			</div>

		

		</div>
	
		<!-- Order Form - Right Side -->
		<div class="orderRight">
			
			<!-- Product Order Receipt -->

			<?php echo (!empty($colors->sidebar_text) ? $colors->sidebar_text : NULL); ?>

			<!-- Guarantee Spot	 -->
			<div class="guaranteeSpot">
				<div class="guaranteeIcon">
					<i class="icon-calendar icon-3x"></i>
				</div>
				<div class="guaranteeCopy">
					<span class="guaranteeHeading" >30 Day Money Back Guarantee</span>
					<span class="guaranteeSubHeading">100% No Questions Asked</span>
				</div>
				<br clear="left" />
			</div>
			
			<!-- Support Spot	 -->
			<div class="guaranteeSpot" style="margin-top: 15px;" >
				<div class="guaranteeIcon">
					<i class="icon-heart icon-3x"></i>
				</div>
				<div class="guaranteeCopy">
					<span class="guaranteeHeading" >World Class &amp; Active Support</span>
					<span class="guaranteeSubHeading">12 Hour Response Time </span>
				</div>
				<br clear="left" />
			</div>

		</div>

		<!-- Clear Order Form -->
		<br clear="both" />
	
		<div class="mainOrderBlock">

			<div class="bigOrderBTN">
				<span class="bigOrderHeading" ><?php echo $plan->order_btn_text_1; ?></span>
				<span class="bigOrderSub"><?php echo $plan->order_btn_text_2; ?></span>
			</div>

			<div class="bigOrderFinePrint">
				* Clicking Order Will Charge Your Credit Card - Or Redirect To Paypal - Once order has been placed, you will be emailed your login information... *
			</div>

		</div>

	</div>
	</form>

	<!-- Footer Block Wrapper -->
	<div class="footerWrapper">
		
		<!-- <div class="productLogo">
			<img src="http://digitalkickstart.com/wp-content/uploads/2013/09/logo.png" width="141 " alt="">
		</div> -->

		<div class="footerLinks">
			<i class="icon-lock" style="margin-right: 10px;" ></i> Secure Payments Through Stripe 
			<span>-</span>
			<a href="http://digitalkickstart.com/privacy-policy/" target="_blank">Privacy Policy</a> 
			<span>-</span> 
			<a href="http://digitalkickstart.com/terms-conditions/" target="_blank">Terms Of Service</a>
			<span>-</span>
			<a href="http://support.digitalkickstart.com/" target="_blank">Support Desk</a>
			<span>-</span>
			All Rights Reserved
			<span>-</span>
			DigitalKickStart &copy; <?php echo date("Y") ?>
		</div>
		
		<br clear="both" />

	</div>
	
</body>

<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
<!-- Stripe -->
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script src="<?php echo asset('checkoutAssets/js/formance.js'); ?>" /></script>
<script src="<?php echo asset('checkoutAssets/js/awesome_form.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {

	$('#showAvailablePlans').click(function(e){
		//e.preventDefault();
		$('#available_plans').toggleClass('hide');
	});

	$('#available_plans').change(function(e){
		// Get current URL
		var str = window.location.href;

		// Get new code and replace in current URL
		str = str.replace("<?php echo $plan->code; ?>", $(e.currentTarget).val());

		// Redirect user to new plan
		top.location.href = str;
	});

	$( "input[name='pay_option']" ).click(function(e){
		// Get price
		var price = $(e.currentTarget).attr('data-price');

		$('.total_price').text(price);
	});

	// Validation On Form
	$('#ccNum').formance('format_credit_card_number');
	$('#ccExpire').formance('format_credit_card_expiry');
	$('#ccCSV').formance('format_credit_card_cvc');

	// Submit form
	$(".bigOrderBTN").click(function(e){
		$("#frmOrder").submit();
	});

	// Choose payment Gateway
	$(".btnChooseGateway").click(function(e){
		var t = $(this);
		var chosenGateway = t.attr("data-gateway");
		var otherGateway = null;

		if(chosenGateway == "stripe") otherGateway = "paypal";
		if(chosenGateway == "paypal") otherGateway = "stripe";
		
		$("#"+otherGateway+"-form").hide();
		$("#"+chosenGateway+"-form").show();

		$("#"+otherGateway+"-btn").removeClass("paymentChoiceActive");
		$("#"+otherGateway+"-btn").addClass("paymentChoiceNotActive");
		$("#"+chosenGateway+"-btn").removeClass("paymentChoiceNotActive");
		$("#"+chosenGateway+"-btn").addClass("paymentChoiceActive");

		$("#gateway").val(chosenGateway);
	});

	var display_onetime = <?php echo $paytype == 'full' ? 1 : 0; ?>;
	var display_split = <?php echo $paytype == 'split' ? 1 : 0; ?>;


	if(display_onetime != 'undefined' && display_onetime == 1)
	{
		$('#display_split').hide();
	  	$('#onetimeRad').prop('checked', true);
	  	$('#onetimeRad').hide();
	  	$('.paymentSummary').hide();
	  	$('#total').hide();
	}

	if(display_split != 'undefined' && display_split == 1)
	{
	 	$('#display_onetime').hide();
	  	$('#splitRad').prop('checked', true);
	  	$('#splitRad').attr('checked', 'checked');
	  	$('#splitRad').hide();
	  	$('.paymentSummary').hide();
	  	$('#total').hide();
	}

});
</script>

<script>
$(function() {
	$('.show_price').click(function(e){
		$('.purchaseOptions').removeClass('hide');
	});
});
</script>

</html>