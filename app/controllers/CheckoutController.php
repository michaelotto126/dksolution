<?php

use PayPal\CoreComponentTypes\BasicAmountType;
use PayPal\EBLBaseComponents\ActivationDetailsType;
use PayPal\EBLBaseComponents\AddressType;
use PayPal\EBLBaseComponents\BillingPeriodDetailsType;
use PayPal\EBLBaseComponents\CreateRecurringPaymentsProfileRequestDetailsType;
use PayPal\EBLBaseComponents\CreditCardDetailsType;
use PayPal\EBLBaseComponents\RecurringPaymentsProfileDetailsType;
use PayPal\EBLBaseComponents\ScheduleDetailsType;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileReq;
use PayPal\PayPalAPI\CreateRecurringPaymentsProfileRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class CheckoutController extends BaseController {

	/**
	 * Properties
	 */
	protected $_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_data = array();
	}

	public function getTest()
	{
		// Y-m-d\TH:i:sP
		$time_after_30_days = time() + (86400 * 30);

		$billing_start_date = date(DATE_ATOM, $time_after_30_days);
	}
	
	/**
	 * PayPal confirm URL
	 */
	public function getPaypalConfirm()
	{		
		// Add third party libraries
		require_once(app_path() . "/libraries/Curl/Curl.php"); // Add Curl library

		$product = Session::get('_product');
		$plan = Session::get('_plan');
		$email = Session::get('_email');
		$first_name = Session::get('_first_name');
		$last_name = Session::get('_last_name');
		$password = Session::get('_password');
		$pay_option = Session::get('_pay_option');

		$is_split_pay = $pay_option == "split" ? TRUE : FALSE;

		// Get product and plan data
		$product = Product::where('id', '=', $product)->first();
		$plan = Plan::where('id', '=', $plan)->first();
		
		$config = array( 
		    'mode' => Config::get('project.paypal_mode'), 
		    'acct1.UserName' => Config::get('project.paypal_api_username'), 
		    'acct1.Password' => Config::get('project.paypal_api_password'),
		    'acct1.Signature' => Config::get('project.paypal_api_signature')
		); 











		// Payment is charged, now create recurring profile if it is recurring product
		if($plan->is_recurring OR $is_split_pay)
		{
			$recurring_freq = $plan->recurring_freq;
			$plan_price = $plan->price;

			if($is_split_pay)
			{
				$recurring_freq = 1;
				$plan_price = $plan->price_per_installment;
			}

			$currencyCode = "USD";

			/*
			 *  You can include up to 10 recurring payments profiles per request. The
			order of the profile details must match the order of the billing
			agreement details specified in the SetExpressCheckout request which
			takes mandatory argument:

			* `billing start date` - The date when billing for this profile begins.
			`Note:
			The profile may take up to 24 hours for activation.`
			*/
			$RPProfileDetails = new RecurringPaymentsProfileDetailsType();
			$RPProfileDetails->SubscriberName = $first_name . ' ' . $last_name; //$_REQUEST['subscriberName'];

			// Y-m-d\TH:i:sP
			$time_after_30_days = time() + (86400 * 30 * $recurring_freq);
			//$time_after_30_days = time();

			$billing_start_date = date(DATE_ATOM, $time_after_30_days);

			$RPProfileDetails->BillingStartDate = $billing_start_date;//$_REQUEST['billingStartDate'];
			//$RPProfileDetails->SubscriberShippingAddress  = $shippingAddress;

			$activationDetails = new ActivationDetailsType();

			/*
			 * (Optional) Initial non-recurring payment amount due immediately upon profile creation. Use an initial amount for enrolment or set-up fees.
			 */
			//$activationDetails->InitialAmount = new BasicAmountType($currencyCode, $plan->setup_fee); //$_REQUEST['initialAmount']
			
			/*
			 *  (Optional) Action you can specify when a payment fails. It is one of the following values:

			    ContinueOnFailure – By default, PayPal suspends the pending profile in the event that the initial payment amount fails. You can override this default behavior by setting this field to ContinueOnFailure. Then, if the initial payment amount fails, PayPal adds the failed payment amount to the outstanding balance for this recurring payment profile.

			    When you specify ContinueOnFailure, a success code is returned to you in the CreateRecurringPaymentsProfile response and the recurring payments profile is activated for scheduled billing immediately. You should check your IPN messages or PayPal account for updates of the payment status.

			    CancelOnFailure – If this field is not set or you set it to CancelOnFailure, PayPal creates the recurring payment profile, but places it into a pending status until the initial payment completes. If the initial payment clears, PayPal notifies you by IPN that the pending profile has been activated. If the payment fails, PayPal notifies you by IPN that the pending profile has been canceled.

			 */
			//$activationDetails->FailedInitialAmountAction = $_REQUEST['failedInitialAmountAction'];

			/*
			 *  Regular payment period for this schedule which takes mandatory
			params:

			* `Billing Period` - Unit for billing during this subscription period. It is one of the
			following values:
			* Day
			* Week
			* SemiMonth
			* Month
			* Year
			For SemiMonth, billing is done on the 1st and 15th of each month.
			`Note:
			The combination of BillingPeriod and BillingFrequency cannot exceed
			one year.`
			* `Billing Frequency` - Number of billing periods that make up one billing cycle.
			The combination of billing frequency and billing period must be less
			than or equal to one year. For example, if the billing cycle is
			Month, the maximum value for billing frequency is 12. Similarly, if
			the billing cycle is Week, the maximum value for billing frequency is
			52.
			`Note:
			If the billing period is SemiMonth, the billing frequency must be 1.`
			* `Billing Amount`
			*/
			$paymentBillingPeriod =  new BillingPeriodDetailsType();
			$paymentBillingPeriod->BillingFrequency = $recurring_freq; //$_REQUEST['billingFrequency'];
			$paymentBillingPeriod->BillingPeriod = 'Month'; //$_REQUEST['billingPeriod'];
			//$paymentBillingPeriod->TotalBillingCycles = $_REQUEST['totalBillingCycles'];
			$paymentBillingPeriod->Amount = new BasicAmountType($currencyCode, $plan_price); //$_REQUEST['paymentAmount']
			//$paymentBillingPeriod->ShippingAmount = new BasicAmountType($currencyCode, $_REQUEST['paymentShippingAmount']);
			//$paymentBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, $_REQUEST['paymentTaxAmount']);

			/*
			 * 	 Describes the recurring payments schedule, including the regular
			payment period, whether there is a trial period, and the number of
			payments that can fail before a profile is suspended which takes
			mandatory params:

			* `Description` - Description of the recurring payment.
			`Note:
			You must ensure that this field matches the corresponding billing
			agreement description included in the SetExpressCheckout request.`
			* `Payment Period`
			*/
			$scheduleDetails = new ScheduleDetailsType();
			$scheduleDetails->Description = $product->name . " - " . $plan->name;//$_REQUEST['profileDescription'];
			$scheduleDetails->ActivationDetails = $activationDetails;

			// if( $_REQUEST['trialBillingFrequency'] != "" && $_REQUEST['trialAmount'] != "") {
			// 	$trialBillingPeriod =  new BillingPeriodDetailsType();
			// 	$trialBillingPeriod->BillingFrequency = $_REQUEST['trialBillingFrequency'];
			// 	$trialBillingPeriod->BillingPeriod = $_REQUEST['trialBillingPeriod'];
			// 	$trialBillingPeriod->TotalBillingCycles = $_REQUEST['trialBillingCycles'];
			// 	$trialBillingPeriod->Amount = new BasicAmountType($currencyCode, $_REQUEST['trialAmount']);
			// 	$trialBillingPeriod->ShippingAmount = new BasicAmountType($currencyCode, $_REQUEST['trialShippingAmount']);
			// 	$trialBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, $_REQUEST['trialTaxAmount']);
			// 	$scheduleDetails->TrialPeriod  = $trialBillingPeriod;
			// }

			$scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
			// $scheduleDetails->MaxFailedPayments =  $_REQUEST['maxFailedPayments'];
			// $scheduleDetails->AutoBillOutstandingAmount = $_REQUEST['autoBillOutstandingAmount'];

			/*
			 * 	 `CreateRecurringPaymentsProfileRequestDetailsType` which takes
			mandatory params:

			* `Recurring Payments Profile Details`
			* `Schedule Details`
			*/
			$createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType();
			if(trim($_REQUEST['token']) != "") {
				$createRPProfileRequestDetail->Token  = $_REQUEST['token'];
			}

			$createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
			$createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;
			$createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
			$createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;


			$createRPProfileReq =  new CreateRecurringPaymentsProfileReq();
			$createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;

			/*
			 *  ## Creating service wrapper object
			Creating service wrapper object to make API call and loading
			Configuration::getAcctAndConfig() returns array that contains credential and config parameters
			*/
			$paypalService = new PayPalAPIInterfaceServiceService($config);
			try {
				/* wrap API method calls on the service object with a try catch */
				$createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);
			} catch (Exception $ex) {
				echo "Error occured while charging for PayPal. Please try again later";
				exit;
			}

			if(isset($createRPProfileResponse)) {
				/*echo "<table>";
				echo "<tr><td>Ack :</td><td><div id='Ack'>$createRPProfileResponse->Ack</div> </td></tr>";
				echo "<tr><td>ProfileID :</td><td><div id='ProfileID'>".$createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID ."</div> </td></tr>";
				echo "</table>";

				echo "<pre>";
				print_r($createRPProfileResponse);
				echo "</pre>";*/

				if(!empty($createRPProfileResponse->Ack) AND $createRPProfileResponse->Ack == 'Success')
				{
					if($createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus == 'ActiveProfile')
					{
						$paypal_sub_id = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
					}
					else
					{
						echo "Error occured while charging for PayPal. Please try again later";
		        		exit;
					}
				}
				else
				{
					echo "Error occured while charging for PayPal. Please try again later";
		        	exit;
				}
			}
			else
			{
				echo "Error occured while charging for PayPal. Please try again later";
		        exit;
			}
		}


		
		












		
		/*
		 * The DoExpressCheckoutPayment API operation completes an Express Checkout transaction. If you set up a billing agreement in your SetExpressCheckout API call, the billing agreement is created when you call the DoExpressCheckoutPayment API operatio
		 */
		
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		 * For digital goods, the following must be true:
		 * total cost > 0
		 * total cost <= total cost passed in the call to SetExpressCheckout
		*/
		$token =urlencode( $_REQUEST['token']);
		
		/*
		 *  Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails response
		*/
		$payerId=urlencode(  $_REQUEST['PayerID']);
		$paymentAction = "Sale"; //urlencode(  $_REQUEST['paymentAction']);
		
		// ------------------------------------------------------------------
		// this section is optional if parameters required for DoExpressCheckout is retrieved from your database
		$getExpressCheckoutDetailsRequest = new PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
		$getExpressCheckoutReq = new PayPal\PayPalAPI\GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		
		// ------------------------------------------------------------------
		// this section get checkout data from PayPal
		$getExpressCheckoutDetailsRequest = new PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType($token);

		$getExpressCheckoutReq = new PayPal\PayPalAPI\GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		
		/*
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPal\Service\PayPalAPIInterfaceServiceService($config);
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		//----------------------------------------------------------------------------
		
		try {
	        	/* wrap API method calls on the service object with a try catch */
	        	$getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		if(isset($getECResponse)) {
		        $amount = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->value;
		}
		
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		*/
		$orderTotal = new PayPal\CoreComponentTypes\BasicAmountType();
		$orderTotal->currencyID = 'USD';
		$orderTotal->value = $amount; //$_REQUEST['amt'];
		
		$paymentDetails= new PayPal\EBLBaseComponents\PaymentDetailsType();
		$paymentDetails->OrderTotal = $orderTotal;
		$paymentDetails->OrderDescription = $product->name . " - " . $plan->name;
		
		/*
		 * Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
		 */
		$paymentDetails->NotifyURL = Config::get('project.paypal_ipn_url');
		
		$DoECRequestDetails = new PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
		$DoECRequestDetails->PayerID = $payerId;
		$DoECRequestDetails->Token = $token;
		$DoECRequestDetails->PaymentAction = $paymentAction;
		$DoECRequestDetails->PaymentDetails[0] = $paymentDetails;
		
		$DoECRequest = new PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType();
		$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
		
		
		$DoECReq = new PayPal\PayPalAPI\DoExpressCheckoutPaymentReq();
		$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
		
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		if(isset($DoECResponse)) {


				// Get Transaction ID
				if(!empty($DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID))
				{
					$transaction_id = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
				}
				else
				{
					Log::info('PayPal EC Confirm failed', array('email'=>$email, 'product'=>$product->id, 'plan'=>$plan->id, 'COData'=>$DoECResponse));
					
					// Redirect back them to PayPal with token
					return Redirect::to(Config::get('project.paypal_api_url') . 'cgi-bin/webscr?cmd=_express-checkout&token=' . trim($_REQUEST['token']));
				}

				// Get Affiliate ID from Cookie
				$affiliate_id = Cookie::get('_dks_isa');
				
				// Create Buyer Account
				$buyer = Buyer::where('email', '=', $email)->first();
				
				// Add new buyer in DK System
				if(!$buyer)
				{
					$buyer = new Buyer;
	
					$buyer->email 			= $email;
					$buyer->first_name 		= $first_name;
					$buyer->last_name 		= $last_name;
					$buyer->affiliate_id 	= $affiliate_id;
					
					$buyer->save();

					// Add user into Aweber account
					$this->_add_to_aweber($buyer, $product->aweber_list_id);
				}
				else
				{
					if($affiliate_id AND $affiliate_id != $buyer->affiliate_id)
					{
						// Update new Affiliate ID
						$buyer->affiliate_id = $affiliate_id;
						$buyer->save();
					}
					elseif(!$affiliate_id)
					{
						$affiliate_id = $buyer->affiliate_id;
					}
				}

				// Update Buyer IP
				Buyer::updateLastIP($buyer);
				
				// Create Purchase in DB
				$purchase = new Purchase();
								
				$purchase->buyer_id = $buyer->id;
				$purchase->product_id = $product->id;
				$purchase->plan_id = $plan->id;
				$purchase->stripe_token = NULL;
				$purchase->pay_method = 2;
				$purchase->affiliate_id = $affiliate_id;

				// If we successfully get the recurring ID
				if(!empty($paypal_sub_id))
				{
					$purchase->paypal_sub_id = $paypal_sub_id;
				}
				
				$purchase->save();
				
				// Push data using own IPN
				$ipn_url = Config::get('project.paypal_ipn_url');
				
				$data_curl = array(
					"dk_new_user" => TRUE,
					"transaction_id" => $transaction_id,
					"email" => $email,
					"first_name" => $first_name,
					"last_name" => $last_name,
					"password" => $password,
					"plan_id" => $plan->id,
					"product_id" => $product->id,
					"affiliate_id" => $affiliate_id,
					"amount" => $amount
				);
				
				$curl = New Curl;
				$curl->simple_post($ipn_url, $data_curl, array(CURLOPT_BUFFERSIZE => 10));
				
				
				// Everything is ok, now remove session data
				// for security purpose
				Session::forget('_product');
				Session::forget('_plan');
				Session::forget('_email');
				Session::forget('_password');
				
				
				// Create a cookie with email
				// Cookie Name should have product code
				$cookieName = "_" . $product->code . "_email";
				$cookie = Cookie::forever($cookieName, $email);

				// Check if custom next url is available
				$next_page_url = $plan->next_page_url;

				if(Session::get('next_page_url_' . $plan->id))
				{
					$next_page_url = Session::get('next_page_url_' . $plan->id);
				}

				Session::forget('next_page_url_' . $plan->id);
				
				// ->withCookie($cookie)
				$url = url('checkout/thanks?url=' . $next_page_url . '&code=' . $product->code); 
				return Redirect::to($url)->withCookie($cookie);
				//return Redirect::to($plan->next_page_url)->withCookie($cookie);
			
				/*echo "<table>";
		        echo "<tr><td>Ack :</td><td><div id='Ack'>$DoECResponse->Ack</div> </td></tr>";
		        if(isset($DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo)) {
		                echo "<tr><td>TransactionID :</td><td><div id='TransactionID'>". $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID."</div> </td></tr>";
		        }
		        echo "</table>";
		        echo "<pre>";
		        print_r($DoECResponse);
		        echo "</pre>";*/
		}
	}
	
	/**
	 * PayPal confirm OTO URL
	 */
	public function getPaypalConfirmOto()
	{		
		// Add third party libraries
		require_once(app_path() . "/libraries/Curl/Curl.php"); // Add Curl library

		$product = Session::get('_product');
		$plan = Session::get('_plan');
		$buyer = Session::get('_buyer');

		// Create Buyer Account
		$buyer = Buyer::where('id', '=', $buyer)->first();

		$email = $buyer->email;
		$first_name = $buyer->first_name;
		$last_name = $buyer->last_name;
		
		// Get product and plan data
		$product = Product::where('id', '=', $product)->first();
		$plan = Plan::where('id', '=', $plan)->first();
		
		$config = array( 
		    'mode' => Config::get('project.paypal_mode'), 
		    'acct1.UserName' => Config::get('project.paypal_api_username'), 
		    'acct1.Password' => Config::get('project.paypal_api_password'),
		    'acct1.Signature' => Config::get('project.paypal_api_signature')
		); 













		// Payment is charged, now create recurring profile if it is recurring product
		if($plan->is_recurring)
		{
			$currencyCode = "USD";

			/*
			 *  You can include up to 10 recurring payments profiles per request. The
			order of the profile details must match the order of the billing
			agreement details specified in the SetExpressCheckout request which
			takes mandatory argument:

			* `billing start date` - The date when billing for this profile begins.
			`Note:
			The profile may take up to 24 hours for activation.`
			*/
			$RPProfileDetails = new RecurringPaymentsProfileDetailsType();
			$RPProfileDetails->SubscriberName = $first_name . ' ' . $last_name; //$_REQUEST['subscriberName'];

			// Y-m-d\TH:i:sP
			$time_after_30_days = time() + (86400 * 30 * $plan->recurring_freq);
			//$time_after_30_days = time();

			$billing_start_date = date(DATE_ATOM, $time_after_30_days);

			$RPProfileDetails->BillingStartDate = $billing_start_date;//$_REQUEST['billingStartDate'];
			//$RPProfileDetails->SubscriberShippingAddress  = $shippingAddress;

			$activationDetails = new ActivationDetailsType();

			/*
			 * (Optional) Initial non-recurring payment amount due immediately upon profile creation. Use an initial amount for enrolment or set-up fees.
			 */
			//$activationDetails->InitialAmount = new BasicAmountType($currencyCode, $plan->setup_fee); //$_REQUEST['initialAmount']
			
			/*
			 *  (Optional) Action you can specify when a payment fails. It is one of the following values:

			    ContinueOnFailure – By default, PayPal suspends the pending profile in the event that the initial payment amount fails. You can override this default behavior by setting this field to ContinueOnFailure. Then, if the initial payment amount fails, PayPal adds the failed payment amount to the outstanding balance for this recurring payment profile.

			    When you specify ContinueOnFailure, a success code is returned to you in the CreateRecurringPaymentsProfile response and the recurring payments profile is activated for scheduled billing immediately. You should check your IPN messages or PayPal account for updates of the payment status.

			    CancelOnFailure – If this field is not set or you set it to CancelOnFailure, PayPal creates the recurring payment profile, but places it into a pending status until the initial payment completes. If the initial payment clears, PayPal notifies you by IPN that the pending profile has been activated. If the payment fails, PayPal notifies you by IPN that the pending profile has been canceled.

			 */
			//$activationDetails->FailedInitialAmountAction = $_REQUEST['failedInitialAmountAction'];

			/*
			 *  Regular payment period for this schedule which takes mandatory
			params:

			* `Billing Period` - Unit for billing during this subscription period. It is one of the
			following values:
			* Day
			* Week
			* SemiMonth
			* Month
			* Year
			For SemiMonth, billing is done on the 1st and 15th of each month.
			`Note:
			The combination of BillingPeriod and BillingFrequency cannot exceed
			one year.`
			* `Billing Frequency` - Number of billing periods that make up one billing cycle.
			The combination of billing frequency and billing period must be less
			than or equal to one year. For example, if the billing cycle is
			Month, the maximum value for billing frequency is 12. Similarly, if
			the billing cycle is Week, the maximum value for billing frequency is
			52.
			`Note:
			If the billing period is SemiMonth, the billing frequency must be 1.`
			* `Billing Amount`
			*/
			$paymentBillingPeriod =  new BillingPeriodDetailsType();
			$paymentBillingPeriod->BillingFrequency = $plan->recurring_freq; //$_REQUEST['billingFrequency'];
			$paymentBillingPeriod->BillingPeriod = 'Month'; //$_REQUEST['billingPeriod'];
			//$paymentBillingPeriod->TotalBillingCycles = $_REQUEST['totalBillingCycles'];
			$paymentBillingPeriod->Amount = new BasicAmountType($currencyCode, $plan->price); //$_REQUEST['paymentAmount']
			//$paymentBillingPeriod->ShippingAmount = new BasicAmountType($currencyCode, $_REQUEST['paymentShippingAmount']);
			//$paymentBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, $_REQUEST['paymentTaxAmount']);

			/*
			 * 	 Describes the recurring payments schedule, including the regular
			payment period, whether there is a trial period, and the number of
			payments that can fail before a profile is suspended which takes
			mandatory params:

			* `Description` - Description of the recurring payment.
			`Note:
			You must ensure that this field matches the corresponding billing
			agreement description included in the SetExpressCheckout request.`
			* `Payment Period`
			*/
			$scheduleDetails = new ScheduleDetailsType();
			$scheduleDetails->Description = $product->name . " - " . $plan->name;//$_REQUEST['profileDescription'];
			$scheduleDetails->ActivationDetails = $activationDetails;

			// if( $_REQUEST['trialBillingFrequency'] != "" && $_REQUEST['trialAmount'] != "") {
			// 	$trialBillingPeriod =  new BillingPeriodDetailsType();
			// 	$trialBillingPeriod->BillingFrequency = $_REQUEST['trialBillingFrequency'];
			// 	$trialBillingPeriod->BillingPeriod = $_REQUEST['trialBillingPeriod'];
			// 	$trialBillingPeriod->TotalBillingCycles = $_REQUEST['trialBillingCycles'];
			// 	$trialBillingPeriod->Amount = new BasicAmountType($currencyCode, $_REQUEST['trialAmount']);
			// 	$trialBillingPeriod->ShippingAmount = new BasicAmountType($currencyCode, $_REQUEST['trialShippingAmount']);
			// 	$trialBillingPeriod->TaxAmount = new BasicAmountType($currencyCode, $_REQUEST['trialTaxAmount']);
			// 	$scheduleDetails->TrialPeriod  = $trialBillingPeriod;
			// }

			$scheduleDetails->PaymentPeriod = $paymentBillingPeriod;
			// $scheduleDetails->MaxFailedPayments =  $_REQUEST['maxFailedPayments'];
			// $scheduleDetails->AutoBillOutstandingAmount = $_REQUEST['autoBillOutstandingAmount'];

			/*
			 * 	 `CreateRecurringPaymentsProfileRequestDetailsType` which takes
			mandatory params:

			* `Recurring Payments Profile Details`
			* `Schedule Details`
			*/
			$createRPProfileRequestDetail = new CreateRecurringPaymentsProfileRequestDetailsType();
			if(trim($_REQUEST['token']) != "") {
				$createRPProfileRequestDetail->Token  = $_REQUEST['token'];
			}

			$createRPProfileRequestDetail->ScheduleDetails = $scheduleDetails;
			$createRPProfileRequestDetail->RecurringPaymentsProfileDetails = $RPProfileDetails;
			$createRPProfileRequest = new CreateRecurringPaymentsProfileRequestType();
			$createRPProfileRequest->CreateRecurringPaymentsProfileRequestDetails = $createRPProfileRequestDetail;


			$createRPProfileReq =  new CreateRecurringPaymentsProfileReq();
			$createRPProfileReq->CreateRecurringPaymentsProfileRequest = $createRPProfileRequest;

			/*
			 *  ## Creating service wrapper object
			Creating service wrapper object to make API call and loading
			Configuration::getAcctAndConfig() returns array that contains credential and config parameters
			*/
			$paypalService = new PayPalAPIInterfaceServiceService($config);
			try {
				/* wrap API method calls on the service object with a try catch */
				$createRPProfileResponse = $paypalService->CreateRecurringPaymentsProfile($createRPProfileReq);
			} catch (Exception $ex) {
				echo "Error occured while charging for PayPal. Please try again later";
				exit;
			}

			if(isset($createRPProfileResponse)) {
				/*echo "<table>";
				echo "<tr><td>Ack :</td><td><div id='Ack'>$createRPProfileResponse->Ack</div> </td></tr>";
				echo "<tr><td>ProfileID :</td><td><div id='ProfileID'>".$createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID ."</div> </td></tr>";
				echo "</table>";

				echo "<pre>";
				print_r($createRPProfileResponse);
				echo "</pre>";*/

				if(!empty($createRPProfileResponse->Ack) AND $createRPProfileResponse->Ack == 'Success')
				{
					if($createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileStatus == 'ActiveProfile')
					{
						$paypal_sub_id = $createRPProfileResponse->CreateRecurringPaymentsProfileResponseDetails->ProfileID;
					}
					else
					{
						echo "Error occured while charging for PayPal. Please try again later";
		        		exit;
					}
				}
				else
				{
					echo "Error occured while charging for PayPal. Please try again later";
		        	exit;
				}
			}
			else
			{
				echo "Error occured while charging for PayPal. Please try again later";
		        exit;
			}
		}












		
		/*
		 * The DoExpressCheckoutPayment API operation completes an Express Checkout transaction. If you set up a billing agreement in your SetExpressCheckout API call, the billing agreement is created when you call the DoExpressCheckoutPayment API operatio
		 */
		
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		 * For digital goods, the following must be true:
		 * total cost > 0
		 * total cost <= total cost passed in the call to SetExpressCheckout
		*/
		$token =urlencode( $_REQUEST['token']);
		
		/*
		 *  Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails response
		*/
		$payerId=urlencode(  $_REQUEST['PayerID']);
		$paymentAction = "Sale"; //urlencode(  $_REQUEST['paymentAction']);
		
		// ------------------------------------------------------------------
		// this section is optional if parameters required for DoExpressCheckout is retrieved from your database
		$getExpressCheckoutDetailsRequest = new PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType($token);
		$getExpressCheckoutReq = new PayPal\PayPalAPI\GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		
		// ------------------------------------------------------------------
		// this section get checkout data from PayPal
		$getExpressCheckoutDetailsRequest = new PayPal\PayPalAPI\GetExpressCheckoutDetailsRequestType($token);

		$getExpressCheckoutReq = new PayPal\PayPalAPI\GetExpressCheckoutDetailsReq();
		$getExpressCheckoutReq->GetExpressCheckoutDetailsRequest = $getExpressCheckoutDetailsRequest;
		
		/*
		Configuration::getAcctAndConfig() returns array that contains credential and config parameters
		*/
		$paypalService = new PayPal\Service\PayPalAPIInterfaceServiceService($config);
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		//----------------------------------------------------------------------------
		
		try {
	        	/* wrap API method calls on the service object with a try catch */
	        	$getECResponse = $paypalService->GetExpressCheckoutDetails($getExpressCheckoutReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		if(isset($getECResponse)) {
		        $amount = $getECResponse->GetExpressCheckoutDetailsResponseDetails->PaymentDetails[0]->OrderTotal->value;
		}
		
		/*
		 * The total cost of the transaction to the buyer. If shipping cost (not applicable to digital goods) and tax charges are known, include them in this value. If not, this value should be the current sub-total of the order. If the transaction includes one or more one-time purchases, this field must be equal to the sum of the purchases. Set this field to 0 if the transaction does not include a one-time purchase such as when you set up a billing agreement for a recurring payment that is not immediately charged. When the field is set to 0, purchase-specific fields are ignored.
		*/
		$orderTotal = new PayPal\CoreComponentTypes\BasicAmountType();
		$orderTotal->currencyID = 'USD';
		$orderTotal->value = $amount; //$_REQUEST['amt'];
		
		$paymentDetails= new PayPal\EBLBaseComponents\PaymentDetailsType();
		$paymentDetails->OrderTotal = $orderTotal;
		
		/*
		 * Your URL for receiving Instant Payment Notification (IPN) about this transaction. If you do not specify this value in the request, the notification URL from your Merchant Profile is used, if one exists.
		 */
		$paymentDetails->NotifyURL = Config::get('project.paypal_ipn_url');
		
		$DoECRequestDetails = new PayPal\EBLBaseComponents\DoExpressCheckoutPaymentRequestDetailsType();
		$DoECRequestDetails->PayerID = $payerId;
		$DoECRequestDetails->Token = $token;
		$DoECRequestDetails->PaymentAction = $paymentAction;
		$DoECRequestDetails->PaymentDetails[0] = $paymentDetails;
		
		$DoECRequest = new PayPal\PayPalAPI\DoExpressCheckoutPaymentRequestType();
		$DoECRequest->DoExpressCheckoutPaymentRequestDetails = $DoECRequestDetails;
		
		
		$DoECReq = new PayPal\PayPalAPI\DoExpressCheckoutPaymentReq();
		$DoECReq->DoExpressCheckoutPaymentRequest = $DoECRequest;
		
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $DoECResponse = $paypalService->DoExpressCheckoutPayment($DoECReq);
		} catch (Exception $ex) {
		        echo "Error occured while charging for PayPal";
		        exit;
		}
		if(isset($DoECResponse)) {
				
				// Get Transaction ID
				if(!empty($DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID))
				{
					$transaction_id = $DoECResponse->DoExpressCheckoutPaymentResponseDetails->PaymentInfo[0]->TransactionID;
				}
				else
				{
					Log::info('PayPal EC Confirm failed', array('buyer'=>$buyer, 'product'=>$product->id, 'plan'=>$plan->id, 'COData'=>$DoECResponse));
					
					// Redirect back them to PayPal with token
					return Redirect::to(Config::get('project.paypal_api_url') . 'cgi-bin/webscr?cmd=_express-checkout&token=' . trim($_REQUEST['token']));
				}

				// Update Buyer IP
				Buyer::updateLastIP($buyer);

				// Get Purchase ID
				$purchase = Purchase::where('product_id', '=', $product->id)->where('buyer_id', '=', $buyer->id)->first();

				// If we successfully get the recurring ID
				if(!empty($paypal_sub_id))
				{
					$purchase->paypal_sub_id = $paypal_sub_id;
					$purchase->save();
				}

				// Push data using own IPN
				$ipn_url = Config::get('project.paypal_ipn_url');
				
				$data_curl = array(
					"dk_new_charge" => TRUE,
					"transaction_id" => $transaction_id,
					"buyer_id" => $buyer->id,
					"plan_id" => $plan->id,
					"product_id" => $product->id,
					"amount" => $amount
				);
				
				$curl = New Curl;
				$curl->simple_post($ipn_url, $data_curl, array(CURLOPT_BUFFERSIZE => 10));
				
				// Everything is ok, now remove session data
				// for security purpose
				Session::forget('_product');
				Session::forget('_plan');
				Session::forget('_buyer');
				
				// Redirect
				$url = url('checkout/thanks?url=' . $plan->next_page_url . '&code=' . $product->code); 
				return Redirect::to($url);
				//return Redirect::to($plan->next_page_url);
		}
	}

	/**
	 * PayPal SetExpressCheckout
	 */
	private function _paypal_setExpressCheckout()
	{
		
	}

	/**
	 * PayPal DoExpressCheckout
	 */
	private function _paypal_doExpressCheckout()
	{
		
	}

	/*
	 * PayPal CreateRecurringProfile
	 */
	private function _paypal_createRecurringProfile()
	{
		
	}
	
	/**
	 * Payal Cancel URL
	 */
	public function getPaypalCancel()
	{
		$product = Session::get('_product');
		
		// Get product and plan data
		$product = Product::where('id', '=', $product)->first();
		
		// Everything is ok, now remove session data
		// for security purpose
		Session::forget('_product');
		Session::forget('_plan');
		Session::forget('_email');
		Session::forget('_password');
		
		return Redirect::to($product->landing_url);
	}
	
	/**
	 * Default controller method
	 *
	 * @return Void
	 */
	public function getIndex()
	{
		// Redirect to website
		return $this->redirectToWebsite();
	}
	
	/**
	 * Processing payment
	 *
	 * @return Void
	 */
	public function postProcessing()
	{
		$product = Input::get("product");
		$plan = Input::get("plan");
		
		// Get product data
		if(!$product = Product::where('code', '=', $product)->first())
		{
			// Invalid product, Redirect to website
			return $this->redirectToWebsite();
		}
		
		// Get plan data
		if(!$plan = Plan::where('code', '=', $plan)->where('product_id', '=', $product->id)->first())
		{
			// Invalid plan, Redirect to website
			return $this->redirectToWebsite();
		}
		
		// Create data for view
		$this->_data['product'] = $product;
		$this->_data['colors'] = json_decode($product->colors);
		$this->_data['plan'] = $plan;
		$this->_data['pay_option'] = Input::get("pay_option");
		$this->_data['paytype'] = Input::get("paytype");
		
		$this->_data['gateway'] = Input::get("gateway");
		$this->_data['email'] = Input::get("email");
		$this->_data['first_name'] = trim(Input::get("first_name"));
		$this->_data['last_name'] = trim(Input::get("last_name"));
		$this->_data['password'] = Input::get("password");
		$this->_data['retype_password'] = Input::get("retype_password");
		$this->_data['ccNum'] = Input::get('ccNum');
		$this->_data['ccExpire'] = Input::get('ccExpire');
		$this->_data['ccCSV'] = Input::get('ccCSV');

		// If user has already purchased the product,
		// redirect to next page
		$this->_check_already_purchase($this->_data['email'], $product, $plan);
		
		$this->_data['existing_customer'] = Input::get("existing_customer");
		$this->_data['buyer_id'] = Input::get("buyer");
		$this->_data['next_page_url'] = Input::get('next_url');
		
		return View::make('checkout.frontProcess', $this->_data);
	}
	
	/**
	 * Process payment using PayPal
	 *
	 * @return Void
	 */
	public function postPaypal()
	{
		// Add third party libraries
		require_once(app_path() . "/libraries/Curl/Curl.php"); // Add Curl library
		
		// Get Product and Plan Data
		$product = Input::get("product");
		$plan = Input::get("plan");
		$email = Input::get("email");
		$first_name = Input::get("first_name");
		$last_name = Input::get("last_name");
		$password = Input::get("password");
		$pay_option = Input::get("pay_option");

		$is_split_pay = $pay_option == "split" ? TRUE : FALSE;
		
		$existing_customer = Input::get('existing_customer');
		
		// Put every detail in Flash session to show again,
		// if an error occurs
		Session::flash('email', $email);
		Session::flash('first_name', $first_name);
		Session::flash('last_name', $last_name);
		Session::flash('password', $password);
		
		// Get product data
		if(!$product = Product::where('code', '=', $product)->first())
		{
			// Invalid product, Redirect to website
			Session::flash('error', "Product was not found");
			return json_encode(array("error"=>true, "message"=>"Product was not found"));
		}
		
		// Get plan data
		if(!$plan = Plan::where('code', '=', $plan)->where('product_id', '=', $product->id)->first())
		{
			// Invalid plan, Redirect to website
			Session::flash('error', "Plan was not found");
			return json_encode(array("error"=>true, "message"=>"Plan was not found"));
		}
		
		// 0. Check if user entered valid email address and password
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'password' => 'required|min:6',
			'retype_password' => 'required|same:password'
		);
		
		if($existing_customer)
		{
			unset($rules['password']);
			unset($rules['retype_password']);
		}

		$validator = Validator::make(Input::all(), $rules);
		if($validator->fails())
	    {
	    	$messages = $validator->messages();
	    	$messages_txt = '';
		    foreach ($messages->all() as $message)
			{
			    $messages_txt .= "<p>$message</p>";
			}
	        // Error
	        Session::flash('error', $messages_txt);
			return json_encode(array("error"=>true, "message"=>$messages_txt/*"Please use a valid email address"*/));
	    }
		
		// 1. Check if email already exists in app (Nm etc) DB
		// Yes, Error - Stop further process
	    if(!$existing_customer)
		{
			$curl = New Curl;
			
			$api_url = $product->api_url . "?email=$email";
			$reponse = json_decode($curl->simple_get($api_url));
			if(!empty($reponse->user_exists) AND $reponse->user_exists == true)
			{
				// Error
				Session::flash('error', "User with this email already exists");
				return json_encode(array("error"=>true, "message"=>"User with this email already exists"));
			}
		}



		$returnUrl = url("checkout/paypal-confirm"); //"http://secure.digitalkickstart.com/checkout/paypal-confirm";
		$cancelUrl = url("checkout/paypal-cancel"); //"http://secure.digitalkickstart.com/checkout/paypal-cancel";
		
		$currencyCode = 'USD';
		$orderTotal = $plan->price + $plan->setup_fee;


		// If recurring then process manually without PayPal Merchant SDK
		if($plan->is_recurring OR $is_split_pay)
		{
			$recurring_freq = $plan->recurring_freq;

			// For Split
			if($is_split_pay)
			{
				$orderTotal = $plan->price_per_installment + $plan->setup_fee;
				$recurring_freq = 1;
			}

			// For Recurring

   			$ecRequestParams = array(
   				'USER' => Config::get('project.paypal_api_username'),
			    'PWD' => Config::get('project.paypal_api_password'),
			    'SIGNATURE' => Config::get('project.paypal_api_signature'),
   				'VERSION' => '109.0',
			    'METHOD' => 'SetExpressCheckout',
			    'RETURNURL' => $returnUrl,
			    'CANCELURL' => $cancelUrl,
			    'PAYMENTREQUEST_0_AMT' => $orderTotal,
			    'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
			    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			    'REQCONFIRMSHIPPING' => 0,
			    'NOSHIPPING' => 1,

			    //'INITAMT' => $orderTotal,
			    'PROFILESTARTDATE' => date(DATE_ATOM, (time() + (86400 * 30 * $recurring_freq))),

			    // Trial
			    // 'TRIALBILLINGPERIOD' => '',
			    // 'TRIALBILLINGFREQUENCY' => '',
			    // 'TRIALAMT' => '',
			    // 'TRIALTOTALBILLINGCYCLES' => '',

			    //'NOTIFYURL' => Config::get('project.paypal_ipn_url'),

			    'L_BILLINGTYPE0' => 'RecurringPayments',
    			'L_BILLINGAGREEMENTDESCRIPTION0' => $product->name . " - " . $plan->name,
    			'PAYMENTREQUEST_0_CUSTOM' => 'RecurringPayment',

			    // 'BILLINGTYPE' => 'MerchantInitiatedBilling',
			    // 'PAYMENTREQUEST_0_CUSTOM' => 'BillingAgreement'
   			);

   			$ecRequestParams = http_build_query($ecRequestParams);

   			$curl = New Curl;

   			$api_url = "https://api-3t.paypal.com/nvp?";

   			if(Config::get('project.paypal_mode') == 'sandbox') $api_url = "https://api-3t.sandbox.paypal.com/nvp?";

   			$api_url = $api_url . $ecRequestParams;
			
			$curl->ssl(false);
			$response = $curl->simple_get($api_url);

			$data = array();
			$key = explode('&',$response);

			if(is_array($key))
			{
				foreach($key as $temp)
				{
					$keyval = explode('=',$temp);
					if(isset($keyval[1]))
						$data[$keyval[0]] = $keyval[1];
				}
			}
			
			if(!empty($data['TOKEN']))
			{
				$token = $data['TOKEN'];

				$payPalURL = Config::get('project.paypal_api_url') . "webscr?cmd=_express-checkout&token=$token&useraction=commit";

				
				// Store Data in session
                Session::put('_product', $product->id);
				Session::put('_plan', $plan->id);
				Session::put('_email', $email);
				Session::put('_first_name', $first_name);
				Session::put('_last_name', $last_name);
				Session::put('_password', $password);
				Session::put('_pay_option', $pay_option);

				return Response::make(json_encode(array("success"=>true, "url"=>$payPalURL)));
			}

			Session::flash('error', "Payment processing error");
			return json_encode(array("error"=>true, "message"=>"Payment processing error"));
		}










		
		// PayPal SetExpressCheckout
	
		$config = array( 
		    'mode' => Config::get('project.paypal_mode'), 
		    'acct1.UserName' => Config::get('project.paypal_api_username'), 
		    'acct1.Password' => Config::get('project.paypal_api_password'),
		    'acct1.Signature' => Config::get('project.paypal_api_signature')
		); 
		
		/*
		 * The SetExpressCheckout API operation initiates an Express Checkout transaction
		 * This sample code uses Merchant PHP SDK to make API call
		 */
		
		
		// details about payment
		$paymentDetails = new PayPal\EBLBaseComponents\PaymentDetailsType();
		
		// total order amount
		$paymentDetails->OrderTotal = new PayPal\CoreComponentTypes\BasicAmountType($currencyCode, $orderTotal);
		$paymentDetails->PaymentAction = 'Sale';
		$paymentDetails->Custom = json_encode(array('email'=>$email, 'plan'=>$plan->id, 'first_name'=>$first_name, 'last_name'=>$first_name, 'password'=>$password));
		$paymentDetails->OrderDescription = $product->name . " - " . $plan->name;

		$itemDetails = new PayPal\EBLBaseComponents\PaymentDetailsItemType();
		$itemDetails->Name = $product->name . " - " . $plan->name;
		$itemDetails->Amount = $plan->price;

		$paymentDetails->PaymentDetailsItem[0] = $itemDetails;

		if($plan->setup_fee AND $is_split_pay)
		{
			$itemDetails = new PayPal\EBLBaseComponents\PaymentDetailsItemType();
			$itemDetails->Name = "One Time";
			$itemDetails->Amount = $plan->setup_fee;

			$paymentDetails->PaymentDetailsItem[1] = $itemDetails;
		}
		
		$setECReqDetails = new PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;

		/*
		 * (Required) URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay you. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->CancelURL = $cancelUrl;
		/*
		 * (Required) URL to which the buyer's browser is returned after choosing to pay with PayPal. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->ReturnURL = $returnUrl;
		
		$setECReqDetails->NoShipping = TRUE;
		
		$setECReqType = new PayPal\PayPalAPI\SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new PayPal\PayPalAPI\SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;

		$paypalService = new PayPal\Service\PayPalAPIInterfaceServiceService($config);
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $setECResponse = $paypalService->SetExpressCheckout($setECReq);
		} catch (Exception $ex) {
		        
				Session::flash('error', "Payment processing error");
				return json_encode(array("error"=>true, "message"=>"Payment processing error"));
		}
		
		if(isset($setECResponse)) {
			if($setECResponse->Ack =='Success') {
	                $token = $setECResponse->Token;
	                // Redirect to paypal.com here
	                $payPalURL = Config::get('project.paypal_api_url') . "webscr?cmd=_express-checkout&token=$token&useraction=commit";
	                
	                // Store Data in session
	                Session::put('_product', $product->id);
					Session::put('_plan', $plan->id);
					Session::put('_email', $email);
					Session::put('_first_name', $first_name);
					Session::put('_last_name', $last_name);
					Session::put('_password', $password);
	                
	                return Response::make(json_encode(array("success"=>true, "url"=>$payPalURL)));
	        }
		}
	}
	
	/**
	 * Process payment using Stripe
	 *
	 * @return Void
	 */
	public function postStripe()
	{
		// Add third party libraries
		require_once(app_path() . "/libraries/Curl/Curl.php"); // Add Curl library
		require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
		require_once(app_path() . "/libraries/infusionsoft/isdk.php"); // Add InfusionSoft Library
		
		// Get Affiliate ID from Cookie
		$affiliate_id = Cookie::get('_dks_isa');
		
		// Get Product and Plan Data
		$product = Input::get("product");
		$plan = Input::get("plan");
		$email = Input::get("email");
		$first_name = Input::get("first_name");
		$last_name = Input::get("last_name");
		$password = Input::get("password");
		$ccNum = Input::get('ccNum');
		$ccExpire = Input::get('ccExpire');
		$ccCSV = Input::get('ccCSV');

		$is_split_pay = Input::get("pay_option") == "split" ? TRUE : FALSE;
		
		$existing_customer = Input::get('existing_customer');
		
		// Put every detail in Flash session to show again,
		// if an error occurs
		Session::flash('email', $email);
		Session::flash('first_name', $first_name);
		Session::flash('last_name', $last_name);
		Session::flash('password', $password);
		Session::flash('ccNum', $ccNum);
		Session::flash('ccExpire', $ccExpire);
		Session::flash('ccCSV', $ccCSV);
		
		// Get product data
		if(!$product = Product::where('code', '=', $product)->first())
		{
			// Invalid product, Redirect to website
			Session::flash('error', "Product was not found");
			return json_encode(array("error"=>true, "message"=>"Product was not found"));
		}
		
		// Get plan data
		if(!$plan = Plan::where('code', '=', $plan)->where('product_id', '=', $product->id)->first())
		{
			// Invalid plan, Redirect to website
			Session::flash('error', "Plan was not found");
			return json_encode(array("error"=>true, "message"=>"Plan was not found"));
		}
		
		// 0. Check if user entered valid email address and password
		$rules = array(
			'first_name' => 'required',
			'last_name' => 'required',
			'email' => 'required|email',
			'password' => 'required|min:6',
			'retype_password' => 'required|same:password',
			'ccNum' => 'required',
			'ccExpire' => 'required',
			'ccCSV' => 'required'
		);
		
		if($existing_customer)
		{
			unset($rules['password']);
			unset($rules['retype_password']);
		}
		
		$custom_messages = array(
		    'ccNum.required' => 'Credit Card number is required',
			'ccExpire.required' => 'Credit Card expiry date is required',
			'ccCSV.required' => 'Credit Card CSV number is required'
		);

		$validator = Validator::make(Input::all(), $rules, $custom_messages);
		if($validator->fails())
	    {
	    	$messages = $validator->messages();
	    	$messages_txt = '';
		    foreach ($messages->all() as $message)
			{
			    $messages_txt .= "<p>$message</p>";
			}
	        // Error
	        Session::flash('error', $messages_txt);
			return json_encode(array("error"=>true, "message"=>$messages_txt/*"Please use a valid email address"*/));
	    }
		
		// 1. Check if email already exists in app (Nm etc) DB
		// Yes, Error - Stop further process
		if(!$existing_customer)
		{
			$curl = New Curl;
			
			$api_url = $product->api_url . "?email=$email";
			$reponse = json_decode($curl->simple_get($api_url));
			if(!empty($reponse->user_exists) AND $reponse->user_exists == true)
			{
				// Error
				Session::flash('error', "User with this email already exists");
				return json_encode(array("error"=>true, "message"=>"User with this email already exists"));
			}
		}
		
		// 2. Charge Stripe Card
		// Error - Stop
		Stripe::setApiKey(Config::get('project.stripe_secret_key'));
		
		$ccExpire = explode("/", $ccExpire);
		
		$card = array(
    		"number" => $ccNum,
    		"exp_month" => !empty($ccExpire[0]) ? trim($ccExpire[0]) : NULL,
	    	"exp_year" => !empty($ccExpire[1]) ? trim($ccExpire[1]) : NULL,
	    	"cvc" => $ccCSV
    	);
    	
    	$affiliate_id = Cookie::get('_dks_isa');
    	
    	$data = array(
					"description" => "$product->name - $plan->name",
					"card" => $card,
					"email" => $email,
					//"plan" => $plan->stripe_id,
    				"metadata" => array("email"=>$email, "first_name"=>$first_name, "last_name"=>$last_name, "password"=>$password, "affiliate_id"=>$affiliate_id, "product_id"=>$product->id, "plan_id"=>$plan->id)
				);

    	// If plan is recurring
    	if($plan->is_recurring)
    	{
    		$data['plan'] = $plan->stripe_id;
    		$data['account_balance'] = $plan->setup_fee * 100;
    	}

    	// If plan is split payment
    	if($is_split_pay)
    	{
    		$data['plan'] = $plan->stripe_id . "_split";
    		$data['account_balance'] = $plan->setup_fee * 100;
    	}
				
		try {
			
			$customer = Stripe_Customer::create($data);
			
			// If plan is not recurring
			if(!$plan->is_recurring AND !$is_split_pay)
			{
				Stripe_Charge::create(array(
				  "amount" => $plan->price * 100,
				  "currency" => "usd",
				  "customer" => $customer->id,
				  "description" => "Charge for $plan->name ($email)",
				  "metadata" => array("plan_id"=>$plan->id)
				));
			}

		} catch (Stripe_Error $e) {
			// Error
			Session::flash('error', $e->getMessage());
			return json_encode(array("error"=>true, "message"=>$e->getMessage()));
			
		} catch (Exception $e) {
		  	// Something else happened, completely unrelated to Stripe
		  	Session::flash('error', $e->getMessage()/*"An undefined error occured. Please contact to support."*/);
		  	return json_encode(array("error"=>true, "message"=>$e->getMessage()/*"An undefined error occured. Please contact to support."*/));
		}
		
		// No error happened, and we charged user card
		// Create Buyer Account
		$buyer = Buyer::where('email', '=', $email)->first();
		
		// Add new buyer in DK System
		if(!$buyer)
		{
			$buyer = new Buyer;

			$buyer->email 			= $email;
			$buyer->first_name 		= $first_name;
			$buyer->last_name 		= $last_name;
			$buyer->affiliate_id 	= $affiliate_id;
			
			$buyer->save();

			// Add user into Aweber account
			$this->_add_to_aweber($buyer, $product->aweber_list_id);
		}
		else
		{
			if($affiliate_id AND $affiliate_id != $buyer->affiliate_id)
			{
				// Update new Affiliate ID
				$buyer->affiliate_id = $affiliate_id;
				$buyer->save();
			}
			elseif(!$affiliate_id)
			{
				$affiliate_id = $buyer->affiliate_id;
			}
		}

		// Update Buyer IP
		Buyer::updateLastIP($buyer);
		
		// Create Purchase in DB
		$purchase = new Purchase();
						
		$purchase->buyer_id = $buyer->id;
		$purchase->product_id = $product->id;
		$purchase->plan_id = $plan->id;
		$purchase->stripe_token = $customer->id;
		$purchase->pay_method = 1;
		$purchase->affiliate_id = $affiliate_id;

		// If plan is recurring
    	// if($plan->is_recurring)
    	// {
    	// 	$purchase->stripe_sub_token = $customer->id;
    	// }
		
		$purchase->save();
		
		
		// Everything is ok, now remove session data
		// for security purpose
		Session::flash('ccNum', NULL);
		Session::flash('ccExpire', NULL);
		Session::flash('ccCSV', NULL);
		
		
		// Create a cookie with email
		// Cookie Name should have product code
		$cookieName = "_" . $product->code . "_email";
		$cookie = Cookie::forever($cookieName, $email);
		
		// ->withCookie($cookie)
		return Response::make(json_encode(array("success"=>true)))->withCookie($cookie);
		
		//return json_encode(array("success"=>true));
		
		
		
		
		
		
		// 3. Check if email exists in InfusionSoft
				// Yes - Get contactId
				// No - Create new contact and get contactId
		// 4. Create Blank Invoice in InfusionSoft
		// 5. Add selected productId (InfusionSoft), order item
		// 6. Get affiliate ID from cookies
				// If Cookie not exists - Get old affiliate ID from DB
		// 7. Add manual amount with affiliate id (if exists)
		
		
		// 8. Check if buyer email exists in DK DB
				// Yes - update affiliate ID
				// No - Add user in DB with Affiliate ID
				
		// 9. Record purchase and transaction with Stripe token ($customer->id) 
		//    and Infusion Invoice ID ($newOrder)
				
		// 10. Ping product IPN URL with this new purchase
	}
	
	/**
	 * Every request come to this method and it
	 * decides the destination method, based on
	 * parameters
	 *
	 * @return Response
	 */
	public function missingMethod($parameters)
	{
		if(count($parameters) == 2)
		{
			return $this->showOrderForm($parameters[0], $parameters[1]);
		}	
		elseif(count($parameters) == 3)
		{
			return $this->showOrderForm($parameters[0], $parameters[1], $parameters[2]);
			//return $this->showPaymentProcess($parameters[0], $parameters[1], $parameters[2]);
		}
		else 
		{
			$method = isset($parameters[0]) ? 'show' . ucwords($parameters[0]) : NULL;

			if(method_exists($this, $method))
			{
				return $this->{$method}();
			}
			else
			{
				// 404 not found, redirect to website
				return $this->redirectToWebsite();
			}
		}
	}
	
	/**
	 * Display Order Form for given product
	 *
	 * @param $product int
	 * @param $plan int
	 * 
	 * @return Void
	 */
	protected function showOrderForm($product, $plan, $token=NULL)
	{
		// Get product data
		if(!$product = Product::where('code', '=', $product)->first())
		{
			// Invalid product, Redirect to website
			return $this->redirectToWebsite();
		}
		
		// Get plan data
		if(!$plan = Plan::where('code', '=', $plan)->where('product_id', '=', $product->id)->first())
		{
			// Invalid plan, Redirect to website
			return $this->redirectToWebsite();
		}

		// If plan is not available anymore
		if(!$plan->status)
		{
			return Redirect::to($product->landing_url);
		}

		// If next_url parameter exists
		if($next_page_url = Input::get('next_url'))
		{
			Session::put('next_page_url_' . $plan->id, $next_page_url);
		}
		
		// Create data for view
		$this->_data['product'] = $product;
		$this->_data['colors'] = json_decode($product->colors);
		$this->_data['plan'] = $plan;

		$this->_data['available_plans'] = Plan::getAvailableFEPlans($product->id);

		
		// If existing customer
		$this->_data['buyer'] = FALSE;
		$this->_data['existing_customer'] = Input::get('existing');

		// Payment Option
		$this->_data['paytype'] = Input::get('paytype');

		// If overriding Next Page URL
		$this->_data['next_page_url'] = Input::get('next_url');

		// If Buyer ID is given
		if(Input::get('buyer'))
		{
			if($buyer = Buyer::where('id', '=', Input::get('buyer'))->first())
			{
				$this->_data['existing_customer'] = TRUE;
				$this->_data['buyer'] = $buyer;
			}
		}

		// If token is given
		if($token)
		{
			// Get Token from DB
			if($tokenData = Token::where('token', '=', $token)->first())
			{
				$this->_data['existing_customer'] = TRUE;
				$this->_data['buyer'] = $tokenData;
			}
		}
		
		return View::make('checkout.index', $this->_data);
	}
	
	/**
	 * Process payment for an OTO
	 *
	 * @param $product int
	 * @param $plan int
	 * @param $token string
	 * 
	 * @return Void
	 */
	public function getOto($product, $plan, $token=NULL)
	{
		// Get product data
		if(!$product = Product::where('code', '=', $product)->first())
		{
			// Invalid product, Redirect to website
			return $this->redirectToWebsite();
		}
		
		// Get plan data
		if(!$plan = Plan::where('code', '=', $plan)->where('product_id', '=', $product->id)->first())
		{
			// Invalid plan, Redirect to website
			return $this->redirectToWebsite();
		}

		// If plan is not available anymore
		if(!$plan->status)
		{
			return Redirect::to($product->landing_url);
		}
		
		// Get User email address
		if($token)
		{
			// Get Token from DB
			if($tokenData = Token::where('token', '=', $token)->first())
			{
				$email = $tokenData->email;
			}
		}
		else 
		{
			$cookie = "_".$product->code."_email";
			$email = Cookie::get($cookie);
		}
		
		// If no email was found
		if(empty($email))
		{
			return Redirect::to("checkout/$product->code/$plan->code?existing=1");
			//return $this->redirectToWebsite();
		}
		else
		{
			// Get Buyer ID
			$buyer = Buyer::where('email', '=', $email)->first();
			
			if(!$buyer)
			{
				return $this->redirectToWebsite();
			}

			// If user has already purchased the product,
			// redirect to next page
			$this->_check_already_purchase($email, $product, $plan);
			
			$this->_data['buyer'] = $buyer;
			
			// Check if stripe or paypal
			$purchase = Purchase::where('buyer_id', '=', $buyer->id)->where('product_id', '=', $product->id)->first();
			
			if(!$purchase)
			{
				return $this->redirectToWebsite();
			}
			
			if($purchase->pay_method == 1) $this->_data['gateway'] = "stripe";
			if($purchase->pay_method == 2) $this->_data['gateway'] = "paypal";


			// If plan is subscription based and old method is PayPal
			if($purchase->pay_method == 2 AND $plan->is_recurring)
			{
				return Redirect::to("checkout/$product->code/$plan->code?existing=1&buyer=$buyer->id");
			}
			
			$this->_data['purchase'] = $purchase;
		}
		
		// Create data for view
		$this->_data['product'] = $product;
		$this->_data['colors'] = json_decode($product->colors);
		$this->_data['plan'] = $plan;
		
		return View::make('checkout.oto', $this->_data);
	}
	
	/**
	 * Post OTO for paypal
	 */
	public function postPaypalOto()
	{
		$product_id = Input::get('product_id');
		$plan_id = Input::get('plan_id');
		$buyer_id = Input::get('buyer_id');
		
		$product = Product::where('id', '=', $product_id)->first();
		$plan = Plan::where('id', '=', $plan_id)->first();
		$buyer = Buyer::where('id', '=', $buyer_id)->first();
		
		// $plan->price $plan->name  $plan->id
		
		
		// PayPal SetExpressCheckout
	
		$config = array( 
		    'mode' => Config::get('project.paypal_mode'), 
		    'acct1.UserName' => Config::get('project.paypal_api_username'), 
		    'acct1.Password' => Config::get('project.paypal_api_password'),
		    'acct1.Signature' => Config::get('project.paypal_api_signature')
		); 
		
		/*
		 * The SetExpressCheckout API operation initiates an Express Checkout transaction
		 * This sample code uses Merchant PHP SDK to make API call
		 */
		$returnUrl = url("checkout/paypal-confirm-oto"); //"http://secure.digitalkickstart.com/checkout/paypal-confirm-oto";
		$cancelUrl = url("checkout/paypal-cancel"); //"http://secure.digitalkickstart.com/checkout/paypal-cancel";
		
		$currencyCode = 'USD';
		$orderTotal = $plan->price + $plan->setup_fee;






		


		// If recurring then process manually without PayPal Merchant SDK
		if($plan->is_recurring)
		{
			// For Recurring

   			$ecRequestParams = array(
   				'USER' => Config::get('project.paypal_api_username'),
			    'PWD' => Config::get('project.paypal_api_password'),
			    'SIGNATURE' => Config::get('project.paypal_api_signature'),
   				'VERSION' => '109.0',
			    'METHOD' => 'SetExpressCheckout',
			    'RETURNURL' => $returnUrl,
			    'CANCELURL' => $cancelUrl,
			    'PAYMENTREQUEST_0_AMT' => $orderTotal,
			    'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
			    'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
			    'REQCONFIRMSHIPPING' => 0,
			    'NOSHIPPING' => 1,
			    'PROFILESTARTDATE' => date(DATE_ATOM, (time() + (86400 * 30 * $plan->recurring_freq))),
			    'L_BILLINGTYPE0' => 'RecurringPayments',
    			'L_BILLINGAGREEMENTDESCRIPTION0' => $product->name . " - " . $plan->name,
    			'PAYMENTREQUEST_0_CUSTOM' => 'RecurringPayment',
   			);

   			$ecRequestParams = http_build_query($ecRequestParams);

   			$curl = New Curl;

   			$api_url = "https://api-3t.paypal.com/nvp?";

   			if(Config::get('project.paypal_mode') == 'sandbox') $api_url = "https://api-3t.sandbox.paypal.com/nvp?";

   			$api_url = $api_url . $ecRequestParams;
			
			$curl->ssl(false);
			$response = $curl->simple_get($api_url);

			$data = array();
			$key = explode('&',$response);

			if(is_array($key))
			{
				foreach($key as $temp)
				{
					$keyval = explode('=',$temp);
					if(isset($keyval[1]))
						$data[$keyval[0]] = $keyval[1];
				}
			}
			
			if(!empty($data['TOKEN']))
			{
				$token = $data['TOKEN'];

				$payPalURL = Config::get('project.paypal_api_url') . "webscr?cmd=_express-checkout&token=$token&useraction=commit";

				
				// Store Data in session
				Session::put('_product', $product_id);
				Session::put('_plan', $plan_id);
				Session::put('_buyer', $buyer_id);

				return Response::make(json_encode(array("success"=>true, "url"=>$payPalURL)));
			}

			Session::flash('error', "Payment processing error");
			return json_encode(array("error"=>true, "message"=>"Payment processing error"));
		}







		
		// details about payment
		$paymentDetails = new PayPal\EBLBaseComponents\PaymentDetailsType();
		
		// total order amount
		$paymentDetails->OrderTotal = new PayPal\CoreComponentTypes\BasicAmountType($currencyCode, $orderTotal);
		$paymentDetails->PaymentAction = 'Sale';
		$paymentDetails->Custom = $plan->id;
		$paymentDetails->OrderDescription = $product->name . " - " . $plan->name;

		$itemDetails = new PayPal\EBLBaseComponents\PaymentDetailsItemType();
		$itemDetails->Name = $product->name . " - " . $plan->name;
		$itemDetails->Amount = $orderTotal;

		$paymentDetails->PaymentDetailsItem[0] = $itemDetails;
		
		$setECReqDetails = new PayPal\EBLBaseComponents\SetExpressCheckoutRequestDetailsType();
		$setECReqDetails->PaymentDetails[0] = $paymentDetails;
		/*
		 * (Required) URL to which the buyer is returned if the buyer does not approve the use of PayPal to pay you. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->CancelURL = $cancelUrl;
		/*
		 * (Required) URL to which the buyer's browser is returned after choosing to pay with PayPal. For digital goods, you must add JavaScript to this page to close the in-context experience.
		 */
		$setECReqDetails->ReturnURL = $returnUrl;
		
		$setECReqDetails->NoShipping = TRUE;
		
		$setECReqType = new PayPal\PayPalAPI\SetExpressCheckoutRequestType();
		$setECReqType->SetExpressCheckoutRequestDetails = $setECReqDetails;
		$setECReq = new PayPal\PayPalAPI\SetExpressCheckoutReq();
		$setECReq->SetExpressCheckoutRequest = $setECReqType;

		$paypalService = new PayPal\Service\PayPalAPIInterfaceServiceService($config);
		try {
		        /* wrap API method calls on the service object with a try catch */
		        $setECResponse = $paypalService->SetExpressCheckout($setECReq);
		} catch (Exception $ex) {
		        
				return Response::make(json_encode(array("error"=>true, "message"=>"Payment processing error")));
		}
		
		if(isset($setECResponse)) {
			if($setECResponse->Ack =='Success') {
	                $token = $setECResponse->Token;
	                // Redirect to paypal.com here
	                $payPalURL = Config::get('project.paypal_api_url') . "webscr?cmd=_express-checkout&token=$token&useraction=commit";
	                
	                // Store Data in session
	                Session::put('_product', $product_id);
					Session::put('_plan', $plan_id);
					Session::put('_buyer', $buyer_id);
	                
	                return Response::make(json_encode(array("success"=>true, "url"=>$payPalURL)));
	        }
		}
	}
	
	/**
	 * Post OTO for stripe
	 */
	public function postStripeOto()
	{
		// Add third party libraries
		require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
		
		Stripe::setApiKey(Config::get('project.stripe_secret_key'));
		
		$customer_id = Input::get('customer_id');
		$product_id = Input::get('product_id');
		$plan_id = Input::get('plan_id');
		$buyer_id = Input::get('buyer_id');
		
		$plan = Plan::where('id', '=', $plan_id)->first();
		$buyer = Buyer::where('id', '=', $buyer_id)->first();

		// Update Buyer IP
		Buyer::updateLastIP($buyer);
		
		try{
			$customer = Stripe_Customer::retrieve($customer_id);

			// If plan is recurring
	    	if($plan->is_recurring)
	    	{
	    		// Add new subscription
	    		$cu->subscriptions->create(array("plan" => $plan->stripe_id));

	    		// Add Setup Fee
	    		$customer->account_balance = $plan->setup_fee * 100;

	    		$customer->save();
	    	}
	    	else
	    	{
			
				// Extract card token
				Stripe_Charge::create(array(
				  "amount" => $plan->price * 100,
				  "currency" => "usd",
				  //"card" => "tok_1Ohdd6EHs1MIup", // obtained with Stripe.js
				  "customer" => $customer_id,
				  "description" => "Charge for $plan->name ($buyer->email)",
				  "metadata" => array("plan_id"=>$plan->id)
				));
			}
		} 
		catch (Exception $e) 
		{
			return Response::make(json_encode(array("error"=>true, "message"=>"Cannot create charge")));
		}
		
		return Response::make(json_encode(array("success"=>true)));
	}
	
	/**
	 * Show thanks page with tracing code
	 */
	protected function showThanks()
	{

		$this->_data['url'] = Input::get('url');
		$code = Input::get('code');

		if(!$this->_data['product'] = Product::where('code', '=', $code)->first() OR !$this->_data['url'])
		{
			return $this->redirectToWebsite();
		}

		return View::make('checkout.thanks', $this->_data);	
	}

	/**
	 * Redirect to website
	 *
	 * @return Response
	 */
	protected function redirectToWebsite()
	{
		return Redirect::to(Config::get("project.website"));
	}

	/**
	 * Add buyer to Aweber list
	 */
	private function _add_to_aweber($buyer, $listName = NULL)
	{
		if(!$listName) return;

		$list_id = NULL; //"2940324";

		// Add Aweber library
		require_once(app_path() . "/libraries/aweber_api/aweber_api.php");

		// Step 1: assign these values from https://labs.aweber.com/apps
		$consumerKey = 'Ak93deBcfbUqwrLBCr18C4cZ';
		$consumerSecret = 'sqCIdRpBtfHY7Zt65fv3nHMn2uzcNNGjaVzqT7a5';

		$accessKey = "AgsCBQoaGCrLbZRShA2finDC";
		$accessSecret = "OZfPiBHTpxWRF5wuDvGtIGjpG728EmeJiRNbXTE7";

		# Create new instance of AWeberAPI
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);

		try 
		{ 
			$account = $aweber->getAccount($accessKey, $accessSecret);
			$account_id = $account->id;

		    $lists = $account->lists->find(array('name' => $listName));

		    if(count($lists)) 
		    {
		        $list = $lists[0];
		        $list_id = $list->id;

		        $listURL = "/accounts/{$account_id}/lists/{$list_id}"; 
			    $list = $account->loadFromUrl($listURL);
			    $params = array( 
			        'email' => $buyer->email,
			        'ip_address' => Request::server('REMOTE_ADDR'),
			        'ad_tracking' => 'dk_buyer', 
			        'misc_notes' => 'DK app', 
			        'name' => $buyer->first_name . " " . $buyer->last_name 
			    ); 
			    $subscribers = $list->subscribers; 
			    $new_subscriber = $subscribers->create($params);
		    }
		} 
		catch(AWeberAPIException $exc) 
		{ 
		     
		}
	}

	/**
	 * Check if user has already purchase the plan
	 */
	private function _check_already_purchase($buyer_email, $product, $plan)
	{
		// Get Buyer
		$buyer = Buyer::where('email', '=', $buyer_email)->first();

		if($buyer)
		{
			$purchase = Purchase::where('buyer_id', '=', $buyer->id)->first();

			if($purchase)
			{
				if(
					$transaction = Transaction::where('purchase_id', '=', $purchase->id)
												->where('plan_id', '=', $plan->id)
												->where('is_refunded', '=', 0)->first()
					)
				{
					// Redirect to next page
					header("location: " . $plan->next_page_url);
					exit;
				}
			}
		}
	}

	/**
	 * Get and display image using https
	 */
	public function getShowSecureImage()
	{
		$url = Input::get('url');

		if($url)
		{
			$remoteImage = urldecode($url);

			$imginfo = getimagesize($remoteImage);
			header("Content-type: " . $imginfo['mime']);
			readfile($remoteImage);
		}
	}
}