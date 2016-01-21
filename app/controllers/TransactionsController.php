<?php

use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class TransactionsController extends BaseController {
	
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
		
		$this->_data['section'] = "transactions";
	}
	
	/**
	 * Dashboard / All Transactions
	 */
	public function getIndex()
	{		
		// Page Title
		$this->_data['page_title'] = "Transactions";
		
		
		$this->_data['affiliates'] = Affiliate::orderBy('name', 'ASC')->get();
		$this->_data['products'] = Product::orderBy('name', 'ASC')->get();


		$transactions = new Transaction();

		$this->_data['transactions'] = $transactions->search();
		$this->_data['searchParams'] = $transactions->getSearchParams();
		$this->_data['revenue'] = $transactions->totalRevenue();
		$this->_data['paidAffliates'] = $transactions->paidToAffiliates();
		$this->_data['refundQueue'] = $transactions->refundQueueCount();
		
		return View::make('admin.transactions.index', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Transaction Detail
	 */
	public function getDetail($id)
	{
		// Page Title
		$this->_data['page_title'] = "Transaction Detail";
		
		$transaction = Transaction::find($id);
		$this->_data['transaction'] = $transaction;

		$this->_data['pay_method'] = DKHelpers::GetPayMethod($transaction->pay_id);
		
		return View::make('admin.transactions.detail', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Refund Transaction
	 */
	public function getRefund($id)
	{
		// Get transaction
		$transaction = Transaction::find($id);

		// Force Refund
		if(Input::get('force_refund'))
		{
			if($this->_completeRefund($transaction))
			{
				Session::flash('alert_message', '<strong>Done!</strong> You successfully have refunded the sale.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			}
		}

		// Page Title
		$this->_data['page_title'] = "Refund Transaction";
		
		$this->_data['transaction'] = $transaction;
		
		return View::make('admin.transactions.refund', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	* Refund Queue
	*/
	public function getRefundQueue()
	{
		// Page Title
		$this->_data['page_title'] = "Refund Queue Transactions";

		$this->_data['transactions'] = Transaction::getRefundQueue();
		
		return View::make('admin.transactions.queue', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Mark as Refunded Transaction
	 */
	public function getMarkRefund($id)
	{
		// Page Title
		$this->_data['page_title'] = "Refund Transaction";
		
		$this->_data['transaction'] = Transaction::find($id);
		
		return View::make('admin.transactions.refund-queue', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Post mark refund
	 */
	public function postMarkRefund($id)
	{
		$transaction = Transaction::find($id);
		
		// Update transaction
		$transaction->commission_refunded = 1;
		$transaction->save();
			
		Session::flash('alert_message', '<strong>Done!</strong> You successfully have marked the transaction as refunded.');
		return Redirect::to("admin/transactions/refund-queue");
	}
	
	/**
	 * Post refund
	 */
	public function postRefund($id)
	{
		$transaction = Transaction::find($id);

		// Get Plan data
		$plan = Plan::where('id', '=', $transaction->plan_id)->first();

		// Get purchase data
		$purchase = Purchase::where('id', '=', $transaction->purchase_id)->first();
		
		if($transaction->purchase->pay_method == 1 /*Stripe*/)
		{
			// Add Stripe library
			require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
			
			Stripe::setApiKey(Config::get('project.stripe_secret_key'));
			
			try
			{
				$ch = Stripe_Charge::retrieve($transaction->pay_id);
				$ch->refund();
			} 
			catch (Exception $e) 
			{
				$error = TRUE;
			}

			// If Split pay then cancel subscription as well
			if($plan->has_split_pay)
			{
				$at_period_end = FALSE;

				$customer = $purchase->stripe_token;
				$subscription_id = NULL;

				try
				{
					$cu = Stripe_Customer::retrieve($customer);

					$subscriptions = $cu->subscriptions->all(array('count'=>100));

					foreach($subscriptions->data as $subscription)
					{
						if($subscription->plan->id == $plan_id)
						{
							if($subscription->status == 'active')
							{
								$subscription_id = $subscription->id;

								break;
							}
						}
					}

					$cu->subscriptions->retrieve($subscription_id)->cancel(array('at_period_end' => $at_period_end));

				} 
				catch (Exception $e) 
				{
					$error = TRUE;
				}
			}

		}
		elseif($transaction->purchase->pay_method == 2 /*Paypal*/)
		{
			$config = array( 
			    'mode' => Config::get('project.paypal_mode'), 
			    'acct1.UserName' => Config::get('project.paypal_api_username'), 
			    'acct1.Password' => Config::get('project.paypal_api_password'),
			    'acct1.Signature' => Config::get('project.paypal_api_signature')
			); 

			/*
			 * The RefundTransaction API operation issues a refund to the PayPal account holder associated with a transaction. 
			 This sample code uses Merchant PHP SDK to make API call
			 */
			$refundReqest = new PayPal\PayPalAPI\RefundTransactionRequestType();

			/*
			 *          Type of refund you are making. It is one of the following values:
			                
			                 * `Full` - Full refund (default).
			                 * `Partial` - Partial refund.
			                 * `ExternalDispute` - External dispute. (Value available since
			                 version
			                 82.0)
			                 * `Other` - Other type of refund. (Value available since version
			                 82.0)
			 */
			
			$refundReqest->RefundType = 'Full';

			/*
			 *  Either the `transaction ID` or the `payer ID` must be specified.
			                 PayerID is unique encrypted merchant identification number
			                 For setting `payerId`,
			                 `refundTransactionRequest.setPayerID("A9BVYX8XCR9ZQ");`

			                 Unique identifier of the transaction to be refunded.
			 */
			$refundReqest->TransactionID = $transaction->pay_id;

			/*
			 *  (Optional)Type of PayPal funding source (balance or eCheck) that can be used for auto refund. It is one of the following values:

			    any – The merchant does not have a preference. Use any available funding source.

			    default – Use the merchant's preferred funding source, as configured in the merchant's profile.

			    instant – Use the merchant's balance as the funding source.

			    eCheck – The merchant prefers using the eCheck funding source. If the merchant's PayPal balance can cover the refund amount, use the PayPal balance.

			 */
			//$refundReqest->RefundSource = $_REQUEST['refundSource'];
			$refundReqest->Memo = "Refunded from Digital Kickstart App";
			/*
			 * 
			   (Optional) Maximum time until you must retry the refund. 
			 */
			//$refundReqest->RetryUntil = $_REQUEST['retryUntil'];

			$refundReq = new PayPal\PayPalAPI\RefundTransactionReq();
			$refundReq->RefundTransactionRequest = $refundReqest;

			/*
			 *          ## Creating service wrapper object
			Creating service wrapper object to make API call and loading
			Configuration::getAcctAndConfig() returns array that contains credential and config parameters
			*/
			$paypalService = new PayPal\Service\PayPalAPIInterfaceServiceService($config);
			try {
			        /* wrap API method calls on the service object with a try catch */
			        $refundResponse = $paypalService->RefundTransaction($refundReq);
			} catch (Exception $ex) {
			        $error = TRUE;
			}

			// If Split pay then cancel subscription as well
			if($plan->has_split_pay)
			{	
				$paypal_sub_id = $purchase->paypal_sub_id;

				/*
				 * The ManageRecurringPaymentsProfileStatus API operation cancels, suspends, or reactivates a recurring payments profile. 
				 */
				$manageRPPStatusReqestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType();
				/*
				 *  (Required) The action to be performed to the recurring payments profile. Must be one of the following:

				    Cancel – Only profiles in Active or Suspended state can be canceled.

				    Suspend – Only profiles in Active state can be suspended.

				    Reactivate – Only profiles in a suspended state can be reactivated.

				 */
				$manageRPPStatusReqestDetails->Action =  'Cancel';
				/*
				 * (Required) Recurring payments profile ID returned in the CreateRecurringPaymentsProfile response.
				 */
				$manageRPPStatusReqestDetails->ProfileID =  $paypal_sub_id;

				$manageRPPStatusReqest = new ManageRecurringPaymentsProfileStatusRequestType();
				$manageRPPStatusReqest->ManageRecurringPaymentsProfileStatusRequestDetails = $manageRPPStatusReqestDetails;


				$manageRPPStatusReq = new ManageRecurringPaymentsProfileStatusReq();
				$manageRPPStatusReq->ManageRecurringPaymentsProfileStatusRequest = $manageRPPStatusReqest;

				/*
				 * 	 ## Creating service wrapper object
				Creating service wrapper object to make API call and loading
				Configuration::getAcctAndConfig() returns array that contains credential and config parameters
				*/
				$paypalService = new PayPalAPIInterfaceServiceService($config);
				try {
					/* wrap API method calls on the service object with a try catch */
					$manageRPPStatusResponse = $paypalService->ManageRecurringPaymentsProfileStatus($manageRPPStatusReq);
				} catch (Exception $ex) {
					$error = TRUE;
				}

				if(isset($manageRPPStatusResponse) AND $manageRPPStatusResponse->Ack == 'Success') 
				{
					// Do nothing
				}
				else
				{
					$error = TRUE;
				}
			}

		}
		
		if(empty($error))
		{
			if($this->_completeRefund($transaction))
			{
				Session::flash('alert_message', '<strong>Done!</strong> You successfully have refunded the sale.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			}
		}
		else
		{
			Session::flash('alert_error', '<strong>Ooops!</strong> Sale was not refunded, try again. Or <a href="' . url("admin/transactions/refund/$transaction->id?") . 'force_refund=true">Force refund?</a>');
			return Redirect::to("admin/transactions/detail/$transaction->id");
		}
	}

	/**
	 * Complete refund and push notification
	 */
	private function _completeRefund($transaction)
	{
		// Update transaction
		$transaction->is_refunded = 1;
		$transaction->save();

		// Push to IPN
		$ipn_data = array(
			"type" => "refund",
			"plan" => $transaction->plan->code,
			"email" => $transaction->purchase->buyer->email
		);

		// Add Curl library
		require_once(app_path() . "/libraries/Curl/Curl.php");

		// Add an encrypted key to the request
		$ipn_data['key'] = DKHelpers::GenerateHash($ipn_data, $transaction->purchase->product->api_key);
	
		// Post data to IPN
		$curl = New Curl;
		$curl->simple_post($transaction->purchase->product->ipn_url, $ipn_data, array(CURLOPT_BUFFERSIZE => 10, CURLOPT_SSL_VERIFYPEER => false));

		// Send refund email to buyer
		$this->_send_email_refund($transaction->purchase->product->name, $transaction->plan->name, $transaction->purchase->buyer->email, $transaction->pay_id, $transaction->amount);
			
		return TRUE;
	}


	/**
	 * Cancel Subscription
	 */
	public function getCancelSubscription($id, $at_period_end = FALSE)
	{
		// Page Title
		$this->_data['page_title'] = "Cancel Subscription";
		
		$transaction = Transaction::find($id);
		$this->_data['transaction'] = $transaction;

		$pay_id = $transaction->pay_id;

		$pay_method = DKHelpers::GetPayMethod($pay_id);

		// If Stripe
		if($pay_method == 'Stripe')
		{
			$plan_id = $transaction->plan->stripe_id;
			$stripe_sub_id = NULL;
			$is_stripe_sub_active = 0;

			// Add Stripe library
			require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
			
			Stripe::setApiKey(Config::get('project.stripe_secret_key'));
			
			try
			{
				$ch = Stripe_Charge::retrieve($pay_id);

				$subscriptions = Stripe_Customer::retrieve($ch->customer)->subscriptions->all(array('count'=>100));

				foreach($subscriptions->data as $subscription)
				{
					if($subscription->plan->id == $plan_id)
					{
						$stripe_sub_id = $subscription->id;
						if($subscription->status == 'active') $is_stripe_sub_active = 1;

						break;
					}
				}

				$this->_data['stripe_customer_id'] = $ch->customer;
			} 
			catch (Exception $e) 
			{
				Session::flash('alert_error', '<strong>Ooops!</strong> Subscription was not retreived, try again.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			}
		}

		// If PayPal
		if($pay_method == 'PayPal')
		{
			$paypal_sub_id = NULL;
			$is_paypal_sub_active = 0;

			$purchase = $transaction->purchase;

			$paypal_sub_id = $purchase->paypal_sub_id;

			$config = array( 
			    'mode' => Config::get('project.paypal_mode'), 
			    'acct1.UserName' => Config::get('project.paypal_api_username'), 
			    'acct1.Password' => Config::get('project.paypal_api_password'),
			    'acct1.Signature' => Config::get('project.paypal_api_signature')
			); 

			/*
			 * Obtain information about a recurring payments profile. 
			 */
			$getRPPDetailsReqest = new GetRecurringPaymentsProfileDetailsRequestType();
			/*
			 * (Required) Recurring payments profile ID returned in the CreateRecurringPaymentsProfile response. 19-character profile IDs are supported for compatibility with previous versions of the PayPal API.
			 */
			$getRPPDetailsReqest->ProfileID = $paypal_sub_id;


			$getRPPDetailsReq = new GetRecurringPaymentsProfileDetailsReq();
			$getRPPDetailsReq->GetRecurringPaymentsProfileDetailsRequest = $getRPPDetailsReqest;

			/*
			 * 	 ## Creating service wrapper object
			Creating service wrapper object to make API call and loading
			Configuration::getAcctAndConfig() returns array that contains credential and config parameters
			*/
			$paypalService = new PayPalAPIInterfaceServiceService($config);
			try {
				/* wrap API method calls on the service object with a try catch */
				$getRPPDetailsResponse = $paypalService->GetRecurringPaymentsProfileDetails($getRPPDetailsReq);
			} catch (Exception $ex) {
				Session::flash('alert_error', '<strong>Ooops!</strong> Subscription was not retreived, try again.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			}

			if(isset($getRPPDetailsResponse) AND $getRPPDetailsResponse->Ack == 'Success') {

				$status = $getRPPDetailsResponse->GetRecurringPaymentsProfileDetailsResponseDetails->ProfileStatus;

				if($status == 'ActiveProfile') $is_paypal_sub_active = 1;
			}








			//exit;
		}

		$this->_data['subscription_id'] = $pay_method == 'Stripe' ? $stripe_sub_id : $paypal_sub_id;
		$this->_data['is_sub_active'] = $pay_method == 'Stripe' ? $is_stripe_sub_active : $is_paypal_sub_active;
		$this->_data['at_period_end'] = $at_period_end;
		$this->_data['pay_method'] = $pay_method;
		
		return View::make('admin.transactions.cancel-subscription', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Cancel Subscription process
	 */
	public function postCancelSubscription($id, $at_period_end = FALSE)
	{
		$transaction = Transaction::find($id);

		if(Input::get('pay_method') == 'Stripe')
		{
			$at_period_end = ((!$at_period_end) ? FALSE : TRUE);

			$customer = Input::get('stripe_customer_id');
			$subscription_id = Input::get('subscription_id');

			// Add Stripe library
			require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
			
			Stripe::setApiKey(Config::get('project.stripe_secret_key'));
			
			try
			{
				$cu = Stripe_Customer::retrieve($customer);
				$cu->subscriptions->retrieve($subscription_id)->cancel(array('at_period_end' => $at_period_end));

				Session::flash('alert_message', '<strong>Done!</strong> You successfully have cancelled the subscription.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			} 
			catch (Exception $e) 
			{
				Session::flash('alert_error', '<strong>Ooops!</strong> Subscription was not cancelled, try again.');
				return Redirect::to("admin/transactions/cancel-subscription/$transaction->id/$at_period_end");
			}
		}

		if(Input::get('pay_method') == 'PayPal')
		{
			$config = array( 
			    'mode' => Config::get('project.paypal_mode'), 
			    'acct1.UserName' => Config::get('project.paypal_api_username'), 
			    'acct1.Password' => Config::get('project.paypal_api_password'),
			    'acct1.Signature' => Config::get('project.paypal_api_signature')
			); 
			
			$purchase = $transaction->purchase;

			$paypal_sub_id = $purchase->paypal_sub_id;

			/*
			 * The ManageRecurringPaymentsProfileStatus API operation cancels, suspends, or reactivates a recurring payments profile. 
			 */
			$manageRPPStatusReqestDetails = new ManageRecurringPaymentsProfileStatusRequestDetailsType();
			/*
			 *  (Required) The action to be performed to the recurring payments profile. Must be one of the following:

			    Cancel – Only profiles in Active or Suspended state can be canceled.

			    Suspend – Only profiles in Active state can be suspended.

			    Reactivate – Only profiles in a suspended state can be reactivated.

			 */
			$manageRPPStatusReqestDetails->Action =  'Cancel';
			/*
			 * (Required) Recurring payments profile ID returned in the CreateRecurringPaymentsProfile response.
			 */
			$manageRPPStatusReqestDetails->ProfileID =  $paypal_sub_id;

			$manageRPPStatusReqest = new ManageRecurringPaymentsProfileStatusRequestType();
			$manageRPPStatusReqest->ManageRecurringPaymentsProfileStatusRequestDetails = $manageRPPStatusReqestDetails;


			$manageRPPStatusReq = new ManageRecurringPaymentsProfileStatusReq();
			$manageRPPStatusReq->ManageRecurringPaymentsProfileStatusRequest = $manageRPPStatusReqest;

			/*
			 * 	 ## Creating service wrapper object
			Creating service wrapper object to make API call and loading
			Configuration::getAcctAndConfig() returns array that contains credential and config parameters
			*/
			$paypalService = new PayPalAPIInterfaceServiceService($config);
			try {
				/* wrap API method calls on the service object with a try catch */
				$manageRPPStatusResponse = $paypalService->ManageRecurringPaymentsProfileStatus($manageRPPStatusReq);
			} catch (Exception $ex) {
				Session::flash('alert_error', '<strong>Ooops!</strong> Subscription was not cancelled, try again.');
				return Redirect::to("admin/transactions/cancel-subscription/$transaction->id/$at_period_end");
			}

			if(isset($manageRPPStatusResponse) AND $manageRPPStatusResponse->Ack == 'Success') {

				Session::flash('alert_message', '<strong>Done!</strong> You successfully have cancelled the subscription.');
				return Redirect::to("admin/transactions/detail/$transaction->id");
			}
			else
			{
				Session::flash('alert_error', '<strong>Ooops!</strong> Subscription was not cancelled, try again.');
				return Redirect::to("admin/transactions/cancel-subscription/$transaction->id/$at_period_end");
			}
		}
	}

	/**
	 * Send receipt to the customer
	 */
	private function _send_email_refund($product, $plan, $email, $transaction_id, $price)
	{
		$emailBodyHtml = "

		This is an automated message to confirm that a
		refund has been processed. <br><br>

		REFUND DETAILS: <br><br>

		Product: $product - $plan <br>
		Price: $".$price." <br>
		Transaction ID: $transaction_id <br><br>

		Need Product Support? <br><br>

		* If you have any issues or concerns, please create
		a support ticket at our help desk. <br><br>

		Please visit: <br>
		http://support.digitalkickstart.com <br><br>

		==================================================== <br>
		DO NOT REPLY TO THIS EMAIL <br>
		This is an automated message from DigitalKickstart.com <br>
		See http://www.digitalkickstart.com for more information.";
	
		// Set POST variables
		$url = 'http://api.postmarkapp.com/email';
		
		$data = array(
			"From" => "$product <".Config::get('project.postmark_sender_email').">",
			"To" => "$email",
			"Tag" => "Payment Refund",
			"Subject" => "$product Refund Confirmation",
			"HtmlBody" => "$emailBodyHtml",
			"ReplyTo" => Config::get('project.postmark_sender_email')
		);
		
		$headers = array(
	                        'Accept: application/json',
	                        'Content-Type: application/json',
	                        'X-Postmark-Server-Token: ' . Config::get('project.postmark_key')
	                );
		
		// Open connection
		$ch = curl_init();
		
		// Set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		// Execute post
		$result = curl_exec($ch);
		
		// Close connection
		curl_close($ch);
		
		return $result;
	}

}