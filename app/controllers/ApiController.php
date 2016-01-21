<?php

class ApiController extends BaseController {

	/**
	 * API
	 */
	public function getIndex()
	{
		echo "!";
	}

	/**
	 * Licenses API
	 *
	 * Use below method only for Aaron, it is difficult to convert it in other languages.
	 * Instead use license-manager
	 */
	public function postLicense($method = NULL)
	{
		Log::info('License API Air Raw', array('raw_data'=>Input::all()));
		
		// Include libraries
		require_once(app_path() . "/libraries/DKCrypt.php");
		require_once(app_path() . "/libraries/Curl/Curl.php");

		// License Information API
		$timestamp = Input::get('timestamp');
		$encrypted_data = Input::get('data');
		$product_code = trim(Input::get('code'));

		$ONE_HOUR_TEN_MINUTES_IN_SECONDS = 4200;
		$current_timestamp = time();

		$elapsedSeconds = $current_timestamp - $timestamp;

		// ***ALERT*** TEMPORARY FIX CODE STARTS
		// This code should be removed after fixing EVSL software issue
		$aaron_apps = array('as', 'ss', 'ts');
		if(!in_array($product_code, $aaron_apps)) {$product_code == 'evsl';}
		// ***ALERT*** TEMPORARY FIX CODE END

		// Get product code and then fetch secret key
		$product = Product::where('code', '=', $product_code)->first();

		if(!$product)
		{
			$this->_licenseApiInvalidRequest('product');
		}

		$secret_key = $product->api_key . $timestamp;

		// If request timestamp is older than one hour and ten minutes
		// discard the request
		if($elapsedSeconds > $ONE_HOUR_TEN_MINUTES_IN_SECONDS)
		{
			$this->_licenseApiInvalidRequest('time');
		}

		// Decrypt data
		$data = DKCrypt::decrypt($encrypted_data, $secret_key);
		
		Log::info('License API Air', array('data'=>$data, 'params'=>Input::all()));

		if(!$data)
		{
			$this->_licenseApiInvalidRequest('data');
		}

		// Decode JSON
		$data = json_decode($data);

		// Get license key
		if(isset($data->license)) 
		{
			$license_key = trim($data->license);
		}
		else
		{
			$this->_licenseApiInvalidRequest('no license key');
		}

		// Get License Data
		$license = License::where("license_key", "=", $license_key)->first();

		if(!$license)
		{
			$this->_licenseApiInvalidRequest('wrong license key');
		}

		// Get all active plans of a buyer
		if(!$method)
		{
			$transaction = Transaction::where("id", "=", $license->transaction_id)->first();

			$buyer_email = $transaction->purchase->buyer->email;

			$curl = New Curl;
			$plans = $curl->simple_get($product->api_url, array("getPlans"=>$buyer_email), array(CURLOPT_BUFFERSIZE => 10));

			$plans = json_decode($plans);

			if($plans)
			{
				$plans->created_at = $license->created_at;

				$plans = json_encode($plans);

				$plans = DKCrypt::encrypt($plans, $secret_key);

				echo json_encode(array("timestamp" => $timestamp, "data" => $plans));
			}
			else
			{
				echo json_encode(array("timestamp" => $timestamp, "data" => array()));
			}
		}

		// Activate license key for a GUID
		if($method == "activate")
		{
			// Check if License is already activated
			if(!empty($data->guid))
			{
				$guid = $data->guid;
			}
			else
			{
				$this->_licenseApiInvalidRequest('No GUID');
			}

			// Total Licenses Used
			$totalLicensesUsed = LicensesUses::where("license_id", "=", $license->id)->count();

			if(!$license->status)
			{
				$response = array("success" => "false", "overusage" => "false");
			}
			else
			{
				// If user has allowed usage
				if($totalLicensesUsed >= $license->allowed_usage)
				{
					$response = array("success" => "false", "overusage" => "true");
				}
				else
				{
					$licensesUses = LicensesUses::where("license_id", "=", $license->id)->where("guid", "=", $guid)->first();

					if($licensesUses)
					{
						// Update last checked
						$licensesUses->last_checked = time();
						$licensesUses->save();
					}
					else
					{
						// Add to DB
						$licensesUses = new LicensesUses();

						$licensesUses->license_id = $license->id;
						$licensesUses->guid = $guid;
						$licensesUses->activated_at = time();
						$licensesUses->last_checked = time();

						$licensesUses->save();
					}

					$response = array("success" => "true", "overusage" => "false");
				}
			}

			$response = json_encode($response);
			$response = DKCrypt::encrypt($response, $secret_key);

			echo json_encode(array("timestamp" => $timestamp, "data" => $response));
		}

	}

	/**
	 * Prints invalid request message (For Aaron's request only)
	 */
	private function _licenseApiInvalidRequest($error_type = NULL)
	{
		Log::info('License API Air Error', array('error_type'=>$error_type, 'data'=>Input::all()));
		die("Invalid request");
	}

	/**
	 * License Manager
	 *
	 * Allows to check plans and activate a license key
	 */
	public function postLicenseManager($method = NULL)
	{
		// Include libraries
		require_once(app_path() . "/libraries/Curl/Curl.php");

		// Validate the request
		if($this->_isValidRequest())
		{
			$code = Input::get('code') ? Input::get('code') : Input::get('amp;code');
			$license_key = Input::get('license');
			$guid = Input::get('guid') ? Input::get('guid') : Input::get('amp;guid');

			if(!$code OR !$license_key)
			{
				$this->_invalidRequest("Code and License Key parameters are required");
			}

			// Get product code and then fetch secret key
			$product = Product::where('code', '=', $code)->first();

			if(!$product)
			{
				$this->_invalidRequest("Product was not found, contact support.");
			}

			// Get License Data
			$license = License::where("license_key", "=", $license_key)->first();

			if(!$license)
			{
				$this->_invalidRequest("License Key was not found");
			}

			// Get all active plans of a buyer
			if(!$method)
			{
				$transaction = Transaction::where("id", "=", $license->transaction_id)->first();

				$buyer_email = $transaction->purchase->buyer->email;

				$curl = New Curl;
				$plans = $curl->simple_get($product->api_url, array("getPlans"=>$buyer_email), array(CURLOPT_BUFFERSIZE => 10));

				$plans = json_decode($plans);

				if($plans)
				{
					$plans->created_at = $license->created_at;

					die(json_encode($plans));
				}
				else
				{
					die(json_encode(array()));
				}
			}

			// Activate license key for a GUID
			if($method == "activate")
			{
				// Check if License is already activated
				if(!$guid)
				{
					$this->_invalidRequest("GUID parameter is required");
				}

				// Total Licenses Used
				$totalLicensesUsed = LicensesUses::where("license_id", "=", $license->id)->count();

				if(!$license->status)
				{
					$response = array("success" => "false", "overusage" => "false");
				}
				else
				{
					// If user has allowed usage
					if($totalLicensesUsed >= $license->allowed_usage)
					{
						$response = array("success" => "false", "overusage" => "true");
					}
					else
					{
						$licensesUses = LicensesUses::where("license_id", "=", $license->id)->where("guid", "=", $guid)->first();

						if($licensesUses)
						{
							// Update last checked
							$licensesUses->last_checked = time();
							$licensesUses->save();
						}
						else
						{
							// Add to DB
							$licensesUses = new LicensesUses();

							$licensesUses->license_id = $license->id;
							$licensesUses->guid = $guid;
							$licensesUses->activated_at = time();
							$licensesUses->last_checked = time();

							$licensesUses->save();
						}

						$response = array("success" => "true", "overusage" => "false");
					}
				}

				die(json_encode($response));
			}
		}
	}

	/**
	 * Change license Allowed usage
	 *
	 * Allows to update maximum allowed license key usage
	 * Key used in this method is not encrypted and it is not core API method
	 */
	public function postChangeLicenseUsage()
	{
		$email = Input::get('email');
		$total = Input::get('total');
		$key  = Input::get('key');
		$product_code  = Input::get('code');

		// Get product code and then fetch secret key
		$product = Product::where('code', '=', $product_code)->first();

		if(!$product)
		{
			$this->_invalidRequest("Product was not found, contact support.");
		}

		// If secret key is matched
		if($product->api_key == $key)
		{
			// Get buyer
			if($buyer = Buyer::where('email', '=', $email)->first())
			{
				if($purchase = Purchase::where('buyer_id', '=', $buyer->id)->where('product_id', '=', $product->id)->first())
				{
					if($transaction = Transaction::where('purchase_id', '=', $purchase->id)->first())
					{
						if($license = License::where('transaction_id', '=', $transaction->id)->first())
						{
							$license->allowed_usage = $total;
							$license->save();

							echo json_encode(array("success" => "true"));
						}
					}
				}
			}
		}
	}

	/**
	 * Delete a license usage
	 *
	 * Allows to delete a usage of license key
	 */
	public function postDeleteLicenseUsage()
	{
		if($this->_isValidRequest())
		{
			$license_key = Input::get('license_key');
			$guid = Input::get('guid');

			if($license = License::where('license_key', '=', $license_key)->first())
			{
				// Get Usage
				$usage = LicensesUses::where('guid', '=', $guid)->where('license_id', '=', $license->id)->first();

				// Delete Usage
				$usage->delete();

				die(json_encode(array("success" => "true")));
			}
		}
	}

	/**
	 * Get user licenses
	 *
	 * Lists all licenses of a use for a product
	 */
	public function postGetUserLicenses()
	{
		if($this->_isValidRequest())
		{
			$email = Input::get('email');
			$product_code = Input::get('code');

			if(!$email OR !$product_code)
			{
				$this->_invalidRequest();
			}

			$product = Product::where('code', '=', $product_code)->first();

			if(!$product)
			{
				$this->_invalidRequest();
			}

			$licenses = License::search($email, 'email', $product->code);

			$data = array();

			foreach($licenses as $key=>$license)
			{
				$usage = LicensesUses::getAllUsage($license->license_key);
				$data[] = array('license' => $license, 'usage' => $usage);
			}

			return Response::json($data);
		}
	}

	/**
	 * User Token 
	 *
	 * Generates and store the token on behalf of a user
	 */
	public function postToken()
	{
		if($this->_isValidRequest())
		{
			$first_name = Input::get('first_name');
			$last_name = Input::get('last_name');
			$email = Input::get('email');

			if(!$first_name OR !$last_name OR !$email)
			{
				$this->_invalidRequest("All parameters are required");
			}

			// Check if token exists or not
			if($token = Token::where('email', '=', $email)->first())
			{
				die(json_encode(array("token" => $token->token)));
			}

			// Generate new token
			$timestamp = hash_hmac('sha1', time(), "dksystem");
			$unique_token = substr($timestamp,0,10) . str_random(22);

			$token = new Token();

			$token->token = $unique_token;
			$token->first_name = Input::get('first_name');
			$token->last_name = Input::get('last_name');
			$token->email = Input::get('email');

			$token->save();

			die(json_encode(array("token" => $unique_token)));
		}
	}

	/**
	 * Get Card
	 *
	 * Get user credit/debit card details
	 */
	public function postGetCard()
	{
		if($this->_isValidRequest())
		{
			$card = $this->_get_stripe_card();

			$response = array(
				'type' => $card->type,
				'last4' => $card->last4,
				'exp_month' => $card->exp_month,
				'exp_year' => $card->exp_year
			);

			die(json_encode($response));
		}
	}

	/**
	 * Update Card
	 *
	 * Update buyer credit/debit card
	 */
	public function postUpdateCard()
	{
		if($this->_isValidRequest())
		{
			// Get existing card ID
			$old_card = $this->_get_stripe_card();

			$old_card_id = $old_card->id;
			$customer = $old_card->customer;

			// Add a new card, make it default
			$new_card_arr = array(
				'number' => Input::get('number'),
				'exp_month' => Input::get('exp_month'),
				'exp_year' => Input::get('exp_year'),
				'cvc' => Input::get('cvc')
			);

			foreach($new_card_arr as $key=>$value)
			{
				if(!$value)
				{
					$this->_invalidRequest("All card parameters are required");
				}
			}

			try
			{
				// Add new card
				$cu = Stripe_Customer::retrieve($customer);
				$card = $cu->cards->create(array("card" => $new_card_arr));

				// Update customer default card to this new card
				$cu = Stripe_Customer::retrieve($customer);
				$cu->default_card = $card->id;
				$cu->save();
			} 
			catch(Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			// Delete old card
			try
			{
				$cu = Stripe_Customer::retrieve($customer);
				$cu->cards->retrieve($old_card_id)->delete();
			} 
			catch(Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			// Return response
			$response = array(
				'type' => $card->type,
				'last4' => $card->last4,
				'exp_month' => $card->exp_month,
				'exp_year' => $card->exp_year
			);

			die(json_encode($response));
		}
	}

	/**
	 * Update Subscription
	 *
	 * Update buyer subscription
	 */
	public function postUpdateSubscription()
	{
		if($this->_isValidRequest())
		{
			$current_plan = Input::get('current_plan');
			$new_plan = Input::get('new_plan');

			if(!$current_plan OR !$new_plan)
			{
				$this->_invalidRequest("Current and New Plans parameters are required");
			}

			$current_plan = Plan::where('code', '=', $current_plan)->first();
			$new_plan = Plan::where('code', '=', $new_plan)->first();

			if(!$current_plan OR !$new_plan)
			{
				$this->_invalidRequest("Given Current or New Plan is incorrect");
			}

			// Include Stripe Library
			$this->_include_stripe_lib();

			// Get existing subscription ID
			$customer_id = $this->_get_stripe_customer_id();

			try
			{
				$subscriptions = Stripe_Customer::retrieve($customer_id)->subscriptions->all(array('count'=>100));

				foreach($subscriptions->data as $subscription)
				{
					if($subscription->plan->id == $current_plan->stripe_id)
					{
						$stripe_sub_id = $subscription->id;

						break;
					}
				}
			} 
			catch (Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			if(empty($stripe_sub_id)) $this->_invalidRequest("No existing subscription was found");

			// Update Subscription (Using Prorate)
			try
			{
				$cu = Stripe_Customer::retrieve($customer_id);
				$subscription = $cu->subscriptions->retrieve($stripe_sub_id);
				$subscription->plan = $new_plan->stripe_id;
				$subscription->save();
			} 
			catch (Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			// Update customer metadata with new Plan ID
			try
			{
				$cu = Stripe_Customer::retrieve($customer_id);

				$cu->metadata['plan_id'] = $new_plan->id;

				$cu->save();

			} 
			catch (Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			// Return response
			die(json_encode(array('success'=>'true')));
		}
	}

	/**
	 * Recent Transactions
	 *
	 * Get 25 recent transactions of a user
	 */	
	public function postRecentTransactions()
	{
		if($this->_isValidRequest())
		{
			$email = Input::get('email');

			if(!$email)
			{
				$this->_invalidRequest("Email parameter is required");
			}

			$transaction = new Transaction();

			$product_id = Product::where('code', '=', Input::get('code'))->first()->id;

			// Set search params
			$params = array(
	            "from" => "1970-01-01",
	            "to" => date("Y-m-d", time()),
	            "range" => "custom",
	            "product" => $product_id,
	            "affiliate" => NULL,
	            "paid" => 1,
	            "refunded" => 1,
	            "search" => "true",
	            "q" => NULL,
	            "email" => $email
	        );

			$transactions = $transaction->search($params);

			$response = array();

			if(!$transactions) die(json_encode(array('data'=>$response)));

			foreach($transactions as $transaction)
			{
				$response[] = array(
					'transaction_id' => $transaction->pay_id,
					'amount' => $transaction->amount,
					'is_refunded' => $transaction->is_refunded ? 'true' : 'false',
					'date' => $transaction->updated_at,
					'plan_name' => $transaction->plan_name,
					'plan' => Plan::where('id', '=', $transaction->plan_id)->first()->code
				);
			}

			die(json_encode(array('data'=>$response)));
		}
	}

	/**
	 * Product URLs
	 *
	 * Get all URLs being used in all plans
	 */	
	public function postProductUrls()
	{
		if($this->_isValidRequest())
		{
			$code = Input::get('code');

			if(!$code)
			{
				$this->_invalidRequest("Code parameter is required");
			}

			// Get product code and then fetch secret key
			$product = Product::where('code', '=', $code)->first();

			if(!$product)
			{
				$this->_invalidRequest("Product was not found, contact support.");
			}

			$urls = array();

			$urls[] = array('url' => $product->landing_url, 'plan_code' => NULL);

			// Get all plans of the product
			$plans = Plan::where('product_id', '=', $product->id)->get();

			if($plans)
			{
				foreach ($plans as $plan) 
				{
					$next_plan = Plan::where('id', '=', $plan->next_plan_id)->first();
					$urls[] = array('url' => $plan->next_page_url, 'plan_code' => ($next_plan ? $next_plan->code : NULL));
				}
			}

			die(json_encode(array('data'=>$urls)));
		}
	}

	/**
	 * Generate License
	 *
	 * Generate license of any product
	 */	
	public function postGenerateLicense()
	{
		//if($this->_isValidRequest())
		//{
			$prefix = Input::get('code');
			$transaction_id = Input::get('transaction_id');
			$allowed_usage = Input::get('allowed_usage');

			if(!$transaction_id) $this->_invalidRequest("Transaction ID parameter is required");
			if(!$allowed_usage) $this->_invalidRequest("Allowed Usage parameter is required");

			// Check if license is already created for given transaction
			if(License::where('transaction_id', '=', $transaction_id)->first())
			{
				$this->_invalidRequest("License already exists for transaction with ID: $transaction_id");
			}

			$license_key = License::generate($prefix);

			// Save license
			$license = new License();

			$license->license_key = $license_key;
			$license->transaction_id = $transaction_id;
			$license->allowed_usage = $allowed_usage;

			$license->save();

			$data = array('license_key' => $license_key);

			die(json_encode(array('data'=>$data)));
		//}
	}

	/**
	 * Find Transaction
	 *
	 * Find a transaction by email and plan code
	 */	
	public function postFindTransaction()
	{
		if($this->_isValidRequest())
		{
			$plan = Input::get('plan');
			$email = Input::get('email');

			if(!$plan) $this->_invalidRequest("Plan parameter is required");
			if(!$email) $this->_invalidRequest("Email parameter is required");

			// Get Plan
			$plan = Plan::where('code', '=', $plan)->first();

			if(!$plan)
			{
				$this->_invalidRequest("Plan was not found, contact support.");
			}

			// Get buyer
			$buyer = Buyer::where('email', '=', $email)->first();

			if(!$buyer)
			{
				$this->_invalidRequest("Buyer was not found, contact support.");
			}

			$purchase = Purchase::where('product_id', '=', $plan->product_id)->where('buyer_id', '=', $buyer->id)->first();

			if(!$purchase)
			{
				$this->_invalidRequest("Purchase was not found, contact support.");
			}

			$transaction = Transaction::where('purchase_id', '=', $purchase->id)->where('plan_id', '=', $plan->id)->first();

			if(!$transaction)
			{
				$this->_invalidRequest("Transaction was not found, contact support.");
			}

			$transaction = array(
				'id' => $transaction->id,
				'amount' => $transaction->amount,
				'pay_id' => $transaction->pay_id,
				'is_refunded' => $transaction->is_refunded,
				'commission_refunded' => $transaction->commission_refunded,
				'invoice_id' => $transaction->invoice_id,
				'buyer' => array(
					'first_name' => $buyer->first_name,
					'last_name' => $buyer->last_name
				)
			);

			die(json_encode(array('data'=>$transaction)));
		}
	}

	/**
	 * Refund Transaction
	 *
	 * Refund a transaction and push its IPN
	 */	
	public function postRefundTransaction()
	{
		if($this->_isValidRequest())
		{
			$transaction_id = Input::get('transaction_id');
			$force_refund = Input::get('force_refund', FALSE);

			if(Transaction::refund($transaction_id, $force_refund))
			{
				die(json_encode(array('data'=>array('success'=>TRUE))));
			}
			else
			{
				die(json_encode(array('data'=>array('error'=>TRUE))));
			}
		}
	}

	/**
	 * Generate Transaction
	 *
	 * Generate a transaction manually and push its IPN
	 */	
	public function postGenerateTransaction()
	{
		//if($this->_isValidRequest())
		//{
			$params = array(
					'first_name' => Input::get('first_name'),
			        'last_name' => Input::get('last_name'),
			        'email' => Input::get('email'),
			        'password' => Input::get('password'),
					'product_id' => Input::get('product_id'),
					'plan_id' => Input::get('plan_id'),
					'pay_id' => Input::get('pay_id'),
					'stripe_token' => Input::get('stripe_token'),
					'paypal_sub_id' => Input::get('paypal_sub_id'),
					'amount' => Input::get('amount'),
					'affiliate_id' => Input::get('affiliate_id'),
			);

			if(Transaction::addManually($params))
			{
				die(json_encode(array('data'=>array('success'=>TRUE))));
			}
		//}
	}

	/**
	 * Register a user
	 *
	 * Register a new buyer into the system
	 */	
	public function postRegisterBuyer()
	{
		if($this->_isValidRequest())
		{
			$buyer = new Buyer;

			$buyer->email 			= Input::get('email');
			$buyer->first_name 		= Input::get('first_name');
			$buyer->last_name 		= Input::get('last_name');
			$buyer->affiliate_id 	= Input::get('affiliate_id');
			
			$buyer->save();

			die(json_encode(array('data'=>array('success'=>TRUE, 'id'=>$buyer->id))));
		}
	}

	/**
	 * Get Next Invoice
	 *
	 * Get user next invoice details details
	 */
	public function postGetUpcomingInvoice()
	{
		if($this->_isValidRequest())
		{
			// Include Stripe Library
			$this->_include_stripe_lib();

			$customer_id = $this->_get_stripe_customer_id();

			try
			{
				// Get customer's upcoming invoice
				$invoice = Stripe_Invoice::upcoming(array("customer" => $customer_id));
			} 
			catch(Exception $e) 
			{
				$this->_invalidRequest($e->getMessage());
			}

			if(!empty($invoice->lines))
			{
				$invoice  = json_decode($invoice);

				$lines_data = !empty($invoice->lines->data) ? $invoice->lines->data : NULL;

				$lines = NULL;

				if($lines_data)
				{
					foreach ($lines_data as $line) 
					{
						$lines[] = array(
							'description' => $this->_get_stripe_invoice_line_description($line),
							'amount' => $this->_format_stripe_amount($line->amount)
						);

					}
				}

				$invoiceObj = new StdClass;
				$invoiceObj->duration = date('M d, Y', $invoice->period_start) . ' to ' . date('M d, Y', $invoice->period_end);
				$invoiceObj->summary = array(
					'subtotal' => $this->_format_stripe_amount($invoice->subtotal),
					'total' => $this->_format_stripe_amount($invoice->total),
					'amount_due' => $this->_format_stripe_amount($invoice->amount_due) . ' USD'
				);
				$invoiceObj->lines = $lines;

				return Response::json($invoiceObj);
			}
		}
	}

	/**
	 * Format Stripe amount
	 * 
	 * Formats Stripe amount in readable format with $ sign
	 */
	private function _format_stripe_amount($amount)
	{
		$negative  = FALSE;

		if($amount < 0)
		{
			$negative  = TRUE;
			$amount = $amount * -1;
		}

		$amount = ($amount / 100);
		$amount = number_format($amount, 2);

		if($negative)
		{
			$amount = '-$' . $amount;
		}
		else
		{
			$amount = '$' . $amount;
		}

		return $amount;
	}

	/**
	 * Get line description
	 *
	 * Get description from Stripe Invoice Line Item
	 */
	private function _get_stripe_invoice_line_description($line)
	{
		if($line->description)
		{
			return $line->description;
		}
		elseif(!empty($line->plan))
		{
			return $line->plan->name;
		}
	}

	/**
	 * Include Stripe Library
	 */	
	private function _include_stripe_lib()
	{
		// Add Stripe library
		require_once(app_path() . "/libraries/stripe-php-1.9.0/lib/Stripe.php"); // Add Stripe library
    	Stripe::setApiKey(Config::get('project.stripe_secret_key'));
	}

	/**
	 * Get Stripe Card Object
	 */
	private function _get_stripe_card($email = NULL, $product_code = NULL)
	{
		// Include Stripe Library
		$this->_include_stripe_lib();

		$customer_id = $this->_get_stripe_customer_id();

		try
		{
			// Get customer and card details from Stripe API
			$customer = Stripe_Customer::retrieve($customer_id);
		} 
		catch(Exception $e) 
		{
			$this->_invalidRequest($e->getMessage());
		}

		if(!empty($customer->cards['data'][0]))
		{
			$card = $customer->cards['data'][0];

			return $card;
		}
		else
		{
			$this->_invalidRequest("No card was found for this user");
		}
	}

	/**
	 * Get Stripe purchase
	 */
	private function _get_stripe_customer_id($email = NULL, $product_code = NULL)
	{
		$email = $email ? $email : Input::get('email');
		$product_code = $product_code ? $product_code : Input::get('code');
		
		// Get product
		$product = Product::where('code', '=', $product_code)->first();

		// Get buyer
		if(!$buyer = Buyer::where('email', '=', $email)->first())
		{
			$this->_invalidRequest("Buyer account was not found");
		}

		// Get purchase
		if(!$purchase = Purchase::where('buyer_id', '=', $buyer->id)->where('product_id', '=', $product->id)->where('pay_method', '=', '1')->first())
		{
			$this->_invalidRequest("No purchase was found, contact support.");
		}

		// Get Stripe Customer ID
		if(!$purchase->stripe_token)
		{
			$this->_invalidRequest("Payment processor's customer ID is missing, contact support.");
		}

		return $purchase->stripe_token;
	}

	/**
	 * Validates the request
	 */
	private function _isValidRequest()
	{
		$encryptedKey = Input::get('key') ? Input::get('key') : Input::get('amp;key');
		$product_code = Input::get('code') ? Input::get('code') : Input::get('amp;code');

		// Get product code and then fetch secret key
		if(!$product = Product::where('code', '=', $product_code)->first())
		{
			$this->_invalidRequest("Code parameter is missing");
		}

		$secret_key = $product->api_key;

		// Get all Post data
		if(isset($_POST))
		{
			$encKey = $this->_generateHash($_POST, $secret_key);

			if($encKey == $encryptedKey)
			{
				return TRUE;
			}
			else
			{
				$this->_invalidRequest();
			}
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
	 * Prints invalid message and exit the process
	 */
	private function _invalidRequest($error_msg = NULL)
	{
		if(!$error_msg) $error_msg = "Invalid request";

		$response  = json_encode(array("error" => $error_msg));
		die($response);
	}

}