<?php

use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class IpnController extends BaseController {
	
	function getTest()
	{
		
	}

	/**
	 * IPN for Stripe
	 */
	public function getIndex($gateway=NULL)
	{
		if($gateway == 'stripe')
		{
			$this->_ipn_stripe();
		}
		
		if($gateway == 'paypal')
		{
			$this->_ipn_paypal();
		}
	}
	
	/**
	 * Route Post data to getIndex method
	 */
	public function postIndex($gateway=NULL)
	{
		$this->getIndex($gateway);
	}
	
	/**
	 * PayPal IPN
	 */
	private function _ipn_paypal()
	{
		Log::info('PayPal IPN Log', Input::all());

		// https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNandPDTVariables/#id08CTB0S055Z

		// recurring_payment_profile_created

		// recurring_payment_profile_cancel
		// recurring_payment_suspended
		// recurring_payment_suspended_due_to_max_failed_payment
		// ACTION: Cancel Subscription
		// PARAMS: recurring_payment_id

		// recurring_payment
		// ACTION: Record Transaction
		// PARAMS: amount, recurring_payment_id, txn_id


		// If PayPal IPN is hiiting
		if(Input::get('txn_type'))
		{
			// Get buyer info using recurring_payment_id
			$paypal_sub_id = Input::get('recurring_payment_id');

			// Get Product, Plan and Buyer
			$ipn_product_name = Input::get('product_name') ? Input::get('product_name') : Input::get('item_name');

			if($ipn_product_name)
			{
				// Pattern will always be (PRODUCT NAME - PLAN NAME)
				$ipn_product_name = explode('-', $ipn_product_name, 2);

				$product_name = trim($ipn_product_name[0]);
				$plan_name = trim($ipn_product_name[1]);

				if($product = Product::where('name', '=', $product_name)->first())
				{
					$plan = Plan::where('name', '=', $plan_name)->where('product_id', '=', $product->id)->first();
				}

				Log::info('PayPal IPN RF', array('product'=>$product->code, 'plan'=>$plan->code));
			}

			if($paypal_sub_id)
			{
				$purchase = Purchase::where('paypal_sub_id', '=', $paypal_sub_id)->first();

				if(!$purchase) return;

				$buyer = $purchase->buyer;
			}

			// Cancel Subscription
			if(
				Input::get('txn_type') == 'recurring_payment_profile_cancel' OR
				Input::get('txn_type') == 'recurring_payment_suspended' OR
				Input::get('txn_type') == 'recurring_payment_suspended_due_to_max_failed_payment'
				)
			{

				// If Split payment installment is received
				if($plan->has_split_pay)
				{
					$total_paid_installments = Transaction::where('purchase_id', '=', $purchase->id)->where('plan_id', '=', $plan->id)->get();
					
					if(count($total_paid_installments) == $plan->total_installments)
					{
						// Do not push IPN, its fine user has paid all installments
						Log::info('PayPal Split Not Cancelled', array('product'=>$product->code, 'plan'=>$plan->code));
						return;
					}
				}

				// Push to IPN
				$ipn_data = array(
					"type" => "sub-cancel",
					"plan" => $plan->code,
					"email" => $buyer->email
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}

			// Recurring Payment received
			if(Input::get('txn_type') == 'recurring_payment')
			{
				$paid_amount = Input::get('amount');
				$pay_id = Input::get('txn_id');

				// Check if Pay ID already exist
				if(Transaction::where('pay_id', '=', $pay_id)->first())
				{
					echo "Transaction was already recorded.";
					return;
				}

				// User all transactions
				$user_transactions = Transaction::where('purchase_id', '=', $purchase->id)->where('plan_id', '=', $plan->id)->get();

				// If Split payment installment is received
				if($plan->has_split_pay)
				{
					if((count($user_transactions) + 1) == $plan->total_installments)
					{
						// Cancel the subscription
						$params['purchase'] = $purchase;

						Log::info('PayPal Split Cancelled', array('product'=>$product->code, 'plan'=>$plan->code));

						$this->_cancelSubscription('PayPal', $params);
					}
				}
				
				// Add payment in InfusionSoft
				if($invoice_id = $this->_infusion_sales($product, $plan, $buyer->email, $buyer->first_name, $buyer->last_name, $purchase->affiliate_id, $paid_amount))
				{
					// Record Sales Transaction
					$transaction = new Transaction();
					
					$transaction->purchase_id = $purchase->id;
					$transaction->plan_id = $plan->id;
					$transaction->amount = $paid_amount; //$plan->price;
					$transaction->invoice_id = $invoice_id;
					$transaction->pay_id = $pay_id;
					$transaction->pay_data = ''; //json_encode(Input::all());
					$transaction->buyer_ip = $buyer->last_used_ip;
					
					$transaction->save();

					// Do not generate license key if this is recurring charge
					$license_key = NULL;

					if((count($user_transactions) + 1) === 1)
					{
						// Generate and Save License Key
						$license_key = $this->_generate_license($product, $plan, $transaction->id);
					}

					// Email Receipt
					$this->_send_email_receipt($product->name, $plan->name, $buyer->email, $pay_id, $paid_amount, $license_key);
				}
				
				// Push IPN to product IPN URL
				$ipn_data = array(
					"type" => "sales",
					"plan" => $plan->code,
					"pay_id" => $pay_id,
					"amount" => $paid_amount,
					"email" => $buyer->email,
					"first_name" => $buyer->first_name,
					"last_name" => $buyer->last_name
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}

			// If payment received
			if(Input::get('txn_type') == 'express_checkout' AND Input::get('payment_status') == 'Completed' AND Input::get('txn_id'))
			{
				if(!empty($product) AND !empty($plan))
				{
					$pay_id = Input::get('txn_id');

					// Check if Pay ID already exist
					if(Transaction::where('pay_id', '=', $pay_id)->first())
					{
						echo "Transaction was already recorded.";
						return;
					}

					// Identify the buyer or purchase

					// Record transaction
				}
			}
			
		}

		// DK generated IPN to create new user and transaction
		if(Input::get('dk_new_user'))
		{
			$plan_id = Input::get('plan_id');
			$product_id = Input::get('product_id');
			$email = Input::get('email');
			$first_name = Input::get('first_name');
			$last_name = Input::get('last_name');
			$password = Input::get('password');
			$affiliate_id = Input::get('affiliate_id');
			$transaction_id = Input::get('transaction_id');
			$amount = Input::get('amount');
			
			// Get Plan and Product
			$plan = Plan::where('id', '=', $plan_id)->first();
			$product = Product::where('id', '=', $product_id)->first();
			
			$buyer = Buyer::where('email', '=', $email)->first();
			
			// Add payment in InfusionSoft
			if($invoice_id = $this->_infusion_sales($product, $plan, $email, $first_name, $last_name, $affiliate_id, $amount))
			{
				// Get Purchase ID
				$purchase = Purchase::where('product_id', '=', $product->id)->where('buyer_id', '=', $buyer->id)->first();
				
				// Record Sales Transaction
				$transaction = new Transaction();
				
				$transaction->purchase_id = $purchase->id;
				$transaction->plan_id = $plan->id;
				$transaction->amount = $amount;
				$transaction->invoice_id = $invoice_id;
				$transaction->pay_id = $transaction_id;
				$transaction->pay_data = ''; //json_encode(Input::all());
				$transaction->buyer_ip = $buyer->last_used_ip;
				
				$transaction->save();

				// Generate and Save License Key
				$license_key = $this->_generate_license($product, $plan, $transaction->id);

				// Email Receipt
				$this->_send_email_receipt($product->name, $plan->name, $email, $transaction_id, $amount, $license_key);
			}
			
			// Push IPN to product IPN URL
			$ipn_data = array(
				"type" => "sales",
				"password" => $password,
				"plan" => $plan->code,
				"pay_id" => $transaction_id,
				"amount" => $plan->price,
				"email" => $email,
				"first_name" => $first_name,
				"last_name" => $last_name
			);

			// Add an encrypted key to the request
			$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

			$this->_push_ipn($product->ipn_url, $ipn_data);
		}
		
		if(Input::get('dk_new_charge'))
		{
			$plan_id = Input::get('plan_id');
			$product_id = Input::get('product_id');
			$buyer_id = Input::get('buyer_id');
			$transaction_id = Input::get('transaction_id');
			$amount = Input::get('amount');
			
			// Get Plan and Product
			$plan = Plan::where('id', '=', $plan_id)->first();
			$product = Product::where('id', '=', $product_id)->first();
			
			$buyer = Buyer::where('id', '=', $buyer_id)->first();
			
			// Get Purchase ID
			$purchase = Purchase::where('product_id', '=', $product->id)->where('buyer_id', '=', $buyer->id)->first();
				
			// Add payment in InfusionSoft
			if($invoice_id = $this->_infusion_sales($product, $plan, $buyer->email, NULL, NULL, $purchase->affiliate_id, $amount))
			{	
				// Record Sales Transaction
				$transaction = new Transaction();
				
				$transaction->purchase_id = $purchase->id;
				$transaction->plan_id = $plan->id;
				$transaction->amount = $amount;
				$transaction->invoice_id = $invoice_id;
				$transaction->pay_id = $transaction_id;
				$transaction->pay_data = ''; //json_encode(Input::all());
				$transaction->buyer_ip = $buyer->last_used_ip;
				
				$transaction->save();

				// Generate and Save License Key
				$license_key = $this->_generate_license($product, $plan, $transaction->id);

				// Email Receipt
				$this->_send_email_receipt($product->name, $plan->name, $buyer->email, $transaction_id, $amount, $license_key);
			}
			
			// Push IPN to product IPN URL
			$ipn_data = array(
				"type" => "sales",
				"plan" => $plan->code,
				"pay_id" => $transaction_id,
				"amount" => $amount,
				"email" => $buyer->email,
				"first_name" => $buyer->first_name,
				"last_name" => $buyer->last_name
			);

			// Add an encrypted key to the request
			$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

			$this->_push_ipn($product->ipn_url, $ipn_data);
		}
		
		// @TODO: Check refund/chargeback etc from PayPal IPN
	}
	
	/**
	 * Stripe IPN
	 */
	private function _ipn_stripe()
	{
    	// Set your secret key: remember to change this to your live secret key in production
		// See your keys here https://manage.stripe.com/account
		// Add Stripe library
		require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
    	Stripe::setApiKey(Config::get('project.stripe_secret_key'));
		
		// Retrieve the request's body and parse it as JSON
		$body = @file_get_contents('php://input');
		$event_json = json_decode($body);

		// For extra security, retrieve from the Stripe API
		try
		{
			$event_id = $event_json->id;
			$event_json = Stripe_Event::retrieve($event_id);
		} 
		catch(Exception $e) 
		{
			exit($e->getMessage());
		}
		
		
		// Do something with $event_json
		if(isset($event_json->type))
		{		
			// Customer and Affiliate
			// Get user_id
			$customer_id = !empty($event_json->data->object->customer) ? $event_json->data->object->customer : NULL;
			
			if($customer_id)
			{
				try 
				{
					$customer = Stripe_Customer::retrieve($customer_id);
					$email = $customer->email;
					$dkData = $customer->metadata;
					
					$buyer = Buyer::where('email', '=', $email)->first();
					
					$affiliate_id = !empty($dkData['affiliate_id']) ? $dkData['affiliate_id'] : NULL; // $buyer->affiliate_id
					$first_name = !empty($dkData['first_name']) ? $dkData['first_name'] : NULL;
					$last_name = !empty($dkData['last_name']) ? $dkData['last_name'] : NULL;

					// Get Product Info
					$product = Product::where('id', '=', $dkData['product_id'])->first();
				} 
				catch (Exception $e) 
				{
					header('HTTP/1.1 400 Bad Request', true, 400);
					exit("Not able to fetch customer");
				}
			}
			else
			{
				// No customer ID was found, stop the process here
				exit('Customer was not found in object');
			}

			// If No buyer was found
			if(empty($buyer))
			{
				exit($event_json->type . ' : Buyer was not found');
			}

			// If No product was found
			if(empty($product))
			{
				exit($event_json->type . ' : Product was not found');
			}
				
			// Create subscription
			if($event_json->type == "customer.subscription.created") 
			{
				$plan_code = $event_json->data->object->plan->id;

				// Remove word "_split" from it
				$plan_code = str_replace('_split', '', $plan_code);

				// Get Plan and Product
				$plan = Plan::where('stripe_id', '=', $plan_code)->first();

				// Push IPN to product IPN URL
				$ipn_data = array(
					"type" => "sales",
					"password" => isset($dkData['password']) ? $dkData['password'] : NULL,
					"plan" => $plan->code,
					"amount" => $plan->price,
					"email" => $email,
					"first_name" => $first_name,
					"last_name" => $last_name
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}
			
			// Successful Charge
			if($event_json->type == "charge.succeeded") 
			{	
				// Delay 10 seconds, so purchase can be added to database			
				sleep(10);

				$pay_id = $event_json->data->object->id;
				$paid_amount = ($event_json->data->object->amount / 100);
				
				// Check if Pay ID already exist
				if(Transaction::where('pay_id', '=', $pay_id)->first())
				{
					echo "Transaction was already recorded.";
					return;
				}
				
				$chargeMetadata = $event_json->data->object->metadata;
				if(empty($chargeMetadata->plan_id) /*Charge metadata*/)
				{
					$plan_id = $dkData['plan_id'];
				}
				else 
				{
					$plan_id = !empty($chargeMetadata->plan_id) ? $chargeMetadata->plan_id : NULL;
				}
				
				// Get Plan and Product
				$plan = Plan::where('id', '=', $plan_id)->first();

				$purchase = Purchase::where('product_id', '=', $product->id)->where('buyer_id', '=', $buyer->id)->first();

				if(!$purchase)
				{
					header('HTTP/1.1 400 Bad Request', true, 400);
					echo "Purchase was not found";

					// Delete InfusionSoft Invoice
					//$this->_delete_infusion_invoice($invoice_id);
					
					return;
				}

				// User all transactions
				$user_transactions = Transaction::where('purchase_id', '=', $purchase->id)->where('plan_id', '=', $plan->id)->get();

				// If Split payment installment is received
				if($plan->has_split_pay)
				{
					if((count($user_transactions) + 1) >= $plan->total_installments)
					{
						// Cancel the subscription
						$params['stripe_customer_id'] = $customer_id;
						$params['plan_id'] = $plan->stripe_id . '_split';

						Log::info('Stripe Split Not Cancelled', array('product'=>$product->code, 'plan'=>$plan->code));

						$this->_cancelSubscription('Stripe', $params);
					}
				}
				
				// Add payment in InfusionSoft
				if($invoice_id = $this->_infusion_sales($product, $plan, $email, $first_name, $last_name, $affiliate_id, $paid_amount))
				{
					if(!$buyer->last_used_ip)
					{
						$buyer = Buyer::where('id', '=', $buyer->id)->first();
					}
					// Record Sales Transaction
					$transaction = new Transaction();
					
					$transaction->purchase_id = $purchase->id;
					$transaction->plan_id = $plan->id;
					$transaction->amount = $paid_amount; //$plan->price;
					$transaction->invoice_id = $invoice_id;
					$transaction->pay_id = $pay_id;
					$transaction->pay_data = ''; //json_encode($event_json);
					$transaction->buyer_ip = $buyer->last_used_ip;
					
					$transaction->save();

					// Do not generate license key if this is recurring charge
					$license_key = NULL;

					if((count($user_transactions) + 1) === 1)
					{
						// Generate and Save License Key
						$license_key = $this->_generate_license($product, $plan, $transaction->id);
					}

					// Email Receipt
					$this->_send_email_receipt($product->name, $plan->name, $email, $pay_id, $paid_amount, $license_key);
				}
				
				// Push IPN to product IPN URL
				$ipn_data = array(
					"type" => "sales",
					"password" => isset($dkData['password']) ? $dkData['password'] : NULL,
					"plan" => $plan->code,
					"pay_id" => $event_json->data->object->id,
					"amount" => $plan->price,
					"email" => $email,
					"first_name" => $first_name,
					"last_name" => $last_name
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}
			
			// Update subscription
			if($event_json->type == "customer.subscription.updated") 
			{
				// $event_json->data->object->cancel_at_period_end

				$stripe_plan_code = $event_json->data->object->plan->id;

				// Remove word "_split" from it
				$stripe_plan_code = str_replace('_split', '', $stripe_plan_code);

				$plan = Plan::where('stripe_id', '=', $stripe_plan_code)->first();

				// Update Customer Metadata in Stripe
				try
				{
					$metadata = $customer->metadata;
					$metadata['plan_id'] = $plan->id;

    				$customer->metadata = $metadata;

					$customer->save();
				} 
				catch (Exception $e) 
				{
					header('HTTP/1.1 400 Bad Request', true, 400);
					echo "Customer was not update";
					
					return;
				}

				// Push to IPN
				$ipn_data = array(
					"type" => "sub-update",
					"plan" => $plan->code,
					"email" => $buyer->email
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}
			
			// Delete Subscription
			if($event_json->type == "customer.subscription.deleted") 
			{
				$stripe_plan_code = $event_json->data->object->plan->id;

				// Remove word "_split" from it
				$stripe_plan_code = str_replace('_split', '', $stripe_plan_code);

				$plan = Plan::where('stripe_id', '=', $stripe_plan_code)->first();

				// If Split payment installment is received
				if($plan->has_split_pay)
				{
					$purchase = Purchase::where('product_id', '=', $product->id)->where('buyer_id', '=', $buyer->id)->first();

					$total_paid_installments = Transaction::where('purchase_id', '=', $purchase->id)->where('plan_id', '=', $plan->id)->get();
					
					if(count($total_paid_installments) >= $plan->total_installments)
					{
						// Do not push IPN, its fine user has paid all installments
						Log::info('Stripe Split Cancelled', array('product'=>$product->code, 'plan'=>$plan->code));
						return;
					}
				}

				// Push to IPN
				$ipn_data = array(
					"type" => "sub-cancel",
					"plan" => $plan->code,
					"email" => $buyer->email
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}

			// Charge Failed
			if($event_json->type == "charge.failed") // invoice.payment_failed
			{
				// Charge failed, ask customer to update card via email
				// @TODO: Ask Mark to enable some tries after failure
			}
			
			// Charge refunded
			if($event_json->type == "charge.refunded") 
			{
				// Check if transaction has not been refunded from UI, then go ahead
				// Else stop process

				$pay_id = $event_json->data->object->id;

				$transaction = Transaction::where('pay_id', '=', $pay_id)->first();

				if($transaction->is_refunded)
				{
					return;
				}

				// Push to IPN
				$ipn_data = array(
					"type" => "refund",
					"plan" => $transaction->plan->code,
					"email" => $buyer->email
				);

				// Add an encrypted key to the request
				$ipn_data['key'] = $this->_generateHash($ipn_data, $product->api_key);

				$this->_push_ipn($product->ipn_url, $ipn_data);
			}
			
			if(isset($error))
			{
				header('HTTP/1.1 400 Bad Request', true, 400);
				echo "Unsuccessful event";
				return;
			}
		}
	}

	/**
	 * Delete an Invoice from InfusionSoft
	 */
	private function _delete_infusion_invoice($invoiceId)
	{
		// Add or Get buyer from InfusionSoft
		require_once(app_path() . "/libraries/infusionsoft/isdk.php"); // Add InfusionSoft Library
		
		$isapp = new iSDK;

		if ($isapp->cfgCon("comissionTracker")) {
			$result = $isapp->deleteInvoice($invoiceId);

			return $result;
		}
	}
	
	/**
	 * Record sales on InfusionSoft
	 */
	private function _infusion_sales($product, $plan, $email, $first_name, $last_name, $affiliate_id, $paid_amount = NULL)
	{
		// Add or Get buyer from InfusionSoft
		require_once(app_path() . "/libraries/infusionsoft/isdk.php"); // Add InfusionSoft Library
		
		$isapp = new iSDK;

		// Create Connection
		if ($isapp->cfgCon("comissionTracker")) {
	
			// find contact by email
			$contacts = $isapp->findByEmail($email, array('Id', 'Email'));
			
			// If contact found
			if(!empty($contacts[0]['Id']))
			{
				$contact_id = $contacts[0]['Id'];
			}
			else
			{
				// Create new contact
				$contactData = array('Email' => $email, 'FirstName'=>$first_name, 'LastName'=>$last_name);
				$contact_id = $isapp->addCon($contactData);
			}
	
			// Sets current date
		    $currentDate = date("d-m-Y");
		    $oDate = $isapp->infuDate($currentDate);
	
			// Creates blank order
		    $newOrder = $isapp->blankOrder( $contact_id , "$product->name - $plan->name ($contact_id)", $oDate, NULL, $affiliate_id);
	
		    // Add Order Item - Product ID
		    // type = 4 or 9 (Product or Subscription)
		    $infusion_product_type = ($product->type == 1 ? 4 : 9);

		    if($paid_amount === NULL OR $paid_amount === '')
		    {
		    	$paid_amount = $plan->price;
		    }
		    
		    $orderPrice = $paid_amount; //$paid_amount ? $paid_amount : $plan->price;
		    $orderPrice = floatval(round($orderPrice, 2));
		    $result = $isapp->addOrderItem($newOrder, $plan->infusion_id, $infusion_product_type, $orderPrice, 1, "Sales Made From DK Solution", "Generated Through API");
		    
		    // Add Manual Payment - since CC charged with Stripe
		    $payment = $isapp->manualPmt($newOrder, $orderPrice, $oDate, "Credit Card", "Payment via DK Solution", false); //credit

		    // Add Affiliate in our database
		    if($affiliate_id)
		    {
		    	// Get Affiliate
		    	$affiliate = Affiliate::find($affiliate_id);

		    	if(!$affiliate OR empty($affiliate->email))
		    	{
		    		$affData = $isapp->dsFind('Affiliate', 1, 0, 'Id', $affiliate_id, array('AffName', 'ContactId'));
		    	}

		    	if(!$affiliate)
		    	{
					$affName = !empty($affData[0]['AffName']) ? $affData[0]['AffName'] : NULL;
					
					// Save Affiliate name
					$affiliate = new Affiliate();
					
					$affiliate->id = $affiliate_id;
					$affiliate->name = $affName;
					
					$affiliate->save();
		    	}

		    	if($affiliate AND empty($affiliate->email))
		    	{
					$affContactId = !empty($affData[0]['ContactId']) ? $affData[0]['ContactId'] : NULL;

					if($affContactId)
					{
						$affContactData = $isapp->dsFind('Contact', 1, 0, 'Id', $affContactId, array('Email'));

						$affEmail = !empty($affContactData[0]['Email']) ? $affContactData[0]['Email'] : NULL;
						$affiliate->email = $affEmail;
						
						$affiliate->save();
					}
		    	}

		    	// Send Commission Email to Affiliate
		    	if($affiliate->email)
		    	{
		    		// Get earned commission
		    		$from = strtotime("midnight", time());
			        $to   = strtotime("tomorrow", time()) - 1;

			        $start = date('Ymd\TH:i:s', $from);
			        $finish = date('Ymd\TH:i:s', $to);

			        $commissions = $isapp->affCommissions($affiliate->id, $start, $finish);

			        if(!empty($commissions) AND is_array($commissions))
			        {
			        	foreach ($commissions as $commission) 
			            {
			            	if($commission['InvoiceId'] == $newOrder)
			            	{
			            		$AffEarnedCommission = $commission['AmtEarned'];
			            	}
			            }
			        }

		            // Send email to Affiliate
		            if(!empty($AffEarnedCommission))
		            {
		            	$this->_send_email_commission($product->name, $plan->name, $affiliate->email, $affiliate->name, $AffEarnedCommission);
		            }
		    	}
		    }
	
		    return $newOrder;
		} else {
			// Error
			// echo "Connection Failed";
			return FALSE;
		}
	}
	
	/**
	 * Push IPN to product IPN URL
	 * 
	 * Types: Sales, Refund, Cancel
	 */
	private function _push_ipn($url, $data)
	{
		Log::info('DK IPN Log', $data);

		// Add Curl library
		require_once(app_path() . "/libraries/Curl/Curl.php");
		
		// Post data to IPN
		$curl = New Curl;
		$curl->simple_post($url, $data, array(CURLOPT_BUFFERSIZE => 10, CURLOPT_SSL_VERIFYPEER => false));
		
		// Store IPN ping in DB with status
	}

	/**
	 * Send receipt to the customer
	 */
	private function _send_email_receipt($product, $plan, $email, $transaction_id, $price, $license_key = NULL)
	{
		$emailBodyHtml = "

		Congratulations on your purchase. <br><br>

		NOTE: This is just your receipt.  Your login credentials 
		will be sent to you in a separate email. <br><br>

		PURCHASE DETAILS: <br><br>

		Product: $product - $plan <br>";
		
		if($price != '0' OR $price != '0.00')
		{
			$emailBodyHtml .= "Price: $".$price." <br>";
		} 

		$emailBodyHtml .=  "Transaction ID: $transaction_id <br>";

		if($license_key)
		{
			$emailBodyHtml .= "License key: $license_key <br>";
		}

		$emailBodyHtml .= "<br>Need Product Support? <br><br>

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
			"Tag" => "Payment Receipt",
			"Subject" => "[Receipt] $product Purchase",
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

	/**
	 * Send commission notification to the affiliate
	 */
	private function _send_email_commission($product, $plan, $email, $name, $commission)
	{
		$commission = number_format((float)$commission, 2, '.', '');

		// Take out first name
		$name = explode(' ', $name);
		$name = $name[0];

		$emailBodyHtml = "

		Hey $name, <br><br>

		Great job...You just made a new sale!<br><br>

		Product: $product <br>
		Plan: $plan <br>
		Commission: $".$commission." <br><br>

		Just as a reminder the DigialKickstart affiliate program
		pays out the 1st of every month, less any purchases made
		within the last 30 days during the refund period. <br><br>

		Don’t forget you can <a href='http://digitalkickstart.com/login'>login to your affiliate account</a> at
		anytime to see your updated commission totals, payout
		balance and affiliate ledger. <br><br>

		If you have any questions or comments, please contact our
		support desk at <a href='http://support.digitalkickstart.com'>http://support.digitalkickstart.com</a> <br><br>

		Cheers, <br>
		Mark";
	
		// Set POST variables
		$url = 'http://api.postmarkapp.com/email';
		
		$data = array(
			"From" => "DigitalKickstart <".Config::get('project.postmark_sender_email').">",
			"To" => "$email",
			"Tag" => "Commission Notification",
			"Subject" => "Congrats, You Made a Commission of $".$commission."!",
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

	/**
	 * Generate and store license in database
	 */
	private function _generate_license($product, $plan, $transaction_id)
	{
		if($plan->has_license)
		{
			$license_key = License::generate($product->code);

			// Save license
			$license = new License();

			$license->license_key = $license_key;
			$license->transaction_id = $transaction_id;
			$license->allowed_usage = $plan->license_allowed_usage;

			$license->save();

			return $license_key;
		}
	}

	/**
	 * Generate Hash for a request
	 */
	private function _generateHash($params, $secret_key)
	{
		return DKHelpers::GenerateHash($params, $secret_key);
	}

	/**
	 * Cancel Subscription process
	 */
	private function _cancelSubscription($pay_method, $params)
	{
		if($pay_method == 'Stripe')
		{
			$at_period_end = FALSE;

			$customer = $params['stripe_customer_id'];
			$plan_id = $params['plan_id'];
			$subscription_id = NULL;

			// Add Stripe library
			require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
			
			Stripe::setApiKey(Config::get('project.stripe_secret_key'));
			
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

				Log::info('Stripe Split Sub Cancelled', $params);

			} 
			catch (Exception $e) 
			{
				Log::info('Stripe cancel error', array('msg' => $e->getMessage()));

				header('HTTP/1.1 400 Bad Request', true, 400);
				echo "Unsuccessful event";
				return;
			}
		}

		if($pay_method == 'PayPal')
		{
			$config = array( 
			    'mode' => Config::get('project.paypal_mode'), 
			    'acct1.UserName' => Config::get('project.paypal_api_username'), 
			    'acct1.Password' => Config::get('project.paypal_api_password'),
			    'acct1.Signature' => Config::get('project.paypal_api_signature')
			); 
			
			$purchase = $params['purchase']; //$transaction->purchase;

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
				header('HTTP/1.1 400 Bad Request', true, 400);
				echo "Unsuccessful event";
				return;
			}

			if(isset($manageRPPStatusResponse) AND $manageRPPStatusResponse->Ack == 'Success') {

				//Session::flash('alert_message', '<strong>Done!</strong> You successfully have cancelled the subscription.');
				//return Redirect::to("admin/transactions/detail/$transaction->id");
			}
			else
			{
				header('HTTP/1.1 400 Bad Request', true, 400);
				echo "Unsuccessful event";
				return;
			}
		}
	}
}