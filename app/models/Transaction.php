<?php

use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsReq;
use PayPal\PayPalAPI\GetRecurringPaymentsProfileDetailsRequestType;
use PayPal\EBLBaseComponents\ManageRecurringPaymentsProfileStatusRequestDetailsType;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusReq;
use PayPal\PayPalAPI\ManageRecurringPaymentsProfileStatusRequestType;
use PayPal\Service\PayPalAPIInterfaceServiceService;

class Transaction extends BaseModel {

	private $_search_params = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Total revenue
	 */
	public function totalRevenue()
    {
        return 0;
        $from = strtotime("midnight", strtotime($this->_search_params['from']));
        $to   = strtotime("tomorrow", strtotime($this->_search_params['to'])) - 1;

        $_tbl_transactions = Transaction::getTableName();
        $_tbl_purchases = Purchase::getTableName();

        $fields = array(DB::raw("SUM($_tbl_transactions.amount) AS amount"));

        $transaction = DB::table($_tbl_transactions)
                            ->join($_tbl_purchases, "$_tbl_purchases.id", '=', "$_tbl_transactions.purchase_id")
                            ->where("$_tbl_transactions.is_refunded", '=', 0)
                            ->whereBetween("$_tbl_transactions.updated_at", array($from, $to))
                            ->select($fields);

        if($this->_search_params['affiliate'] AND $this->_search_params['affiliate'] != "no-affiliate")
        {
            $transaction = $transaction->where("$_tbl_purchases.affiliate_id", '=', $this->_search_params['affiliate']);
        }

        $transaction = $transaction->first();

        return number_format($transaction->amount);
    }
    
    /**
     * Total paid to affiliates
     */
	public function paidToAffiliates()
    {
        return "0";
        $from = strtotime("midnight", strtotime($this->_search_params['from']));
        $to   = strtotime("tomorrow", strtotime($this->_search_params['to'])) - 1;

        if($this->_search_params['affiliate'] AND $this->_search_params['affiliate'] != "no-affiliate")
        {
            $cache_key = "paidToAffiliates_".$from."_".$to."_".$this->_search_params['affiliate'];
        }
        else
        {
            $cache_key = "paidToAffiliates_".$from."_".$to;
        }

        if (Cache::has($cache_key))
        {
            return Cache::get($cache_key);
        }

        $amount = 0;

        $start = date('Ymd\TH:i:s', $from);
        $finish = date('Ymd\TH:i:s', $to);

        // Get all affilaites from DK DB
        $affiliates = Affiliate::get();

        // Add InfusionSoft Library
        require_once(app_path() . "/libraries/infusionsoft/isdk.php");
        
        $isapp = new iSDK;

        // Create Connection
        if ($isapp->cfgCon("comissionTracker")) 
        {
            if($this->_search_params['affiliate'] AND $this->_search_params['affiliate'] != "no-affiliate")
            {
                $pays = $isapp->affPayouts($this->_search_params['affiliate'], $start, $finish);

                foreach ($pays as $payout) 
                {
                    $amount = $amount + $payout['PayAmt'];
                }
            }
            else
            {
                foreach($affiliates as $affiliate)
                {
                    $affiliateId = $affiliate->id;
                    
                    $pays = $isapp->affPayouts($affiliateId, $start, $finish);

                    foreach ($pays as $payout) 
                    {
                        $amount = $amount + $payout['PayAmt'];
                    }
                }
            }
        }

    	$amount = number_format($amount);

        Cache::put($cache_key, $amount, 10);

        return $amount;
    }

    /**
    * Get all transactions
    */
    public function search($params = NULL)
    {
        $_tbl_transactions = Transaction::getTableName();
        $_tbl_purchases = Purchase::getTableName();
        $_tbl_products = Product::getTableName();
        $_tbl_plans = Plan::getTableName();
        $_tbl_affiliates = Affiliate::getTableName();
        $_tbl_buyers = Buyer::getTableName();

        // Build search params
        if(!$params) 
        {
            $this->_build_search_params();
        }
        else
        {
            $this->setSearchParams($params);
        }

        $from = strtotime("midnight", strtotime($this->_search_params['from']));
        $to   = strtotime("tomorrow", strtotime($this->_search_params['to'])) - 1;

        //echo $from . " | " . $to;exit;

        $fields = array("$_tbl_transactions.*",
                    "$_tbl_purchases.affiliate_id",
                    "$_tbl_purchases.product_id",
                    "$_tbl_products.name AS product_name",
                    "$_tbl_plans.name AS plan_name",
                    "$_tbl_affiliates.name AS affiliate_name",
                    "$_tbl_buyers.first_name AS buyer_first_name",
                    "$_tbl_buyers.last_name AS buyer_last_name",
                    "$_tbl_buyers.email AS buyer_email");

        $transaction = DB::table($_tbl_transactions)
                            ->join($_tbl_purchases, "$_tbl_purchases.id", '=', "$_tbl_transactions.purchase_id")
                            ->join($_tbl_products, "$_tbl_products.id", '=', "$_tbl_purchases.product_id")
                            ->join($_tbl_plans, "$_tbl_plans.id", '=', "$_tbl_transactions.plan_id")
                            ->join($_tbl_buyers, "$_tbl_buyers.id", '=', "$_tbl_purchases.buyer_id")
                            ->leftJoin($_tbl_affiliates, "$_tbl_affiliates.id", '=', "$_tbl_purchases.affiliate_id")
                            ->select($fields);


        $transaction = $transaction->orderBy('created_at', 'DESC');

        if(!$this->_search_params['paid'] OR !$this->_search_params['refunded'])
        {
            if($this->_search_params['paid'] AND !$this->_search_params['refunded'])
            {
                $is_refunded = FALSE;
            }

            if(!$this->_search_params['paid'] AND $this->_search_params['refunded'])
            {
                $is_refunded = TRUE;
            }

            if(isset($is_refunded))
            {
                $transaction = $transaction->where("is_refunded", '=', $is_refunded);
            }
        }

        if($this->_search_params['product'])
        {
            // Get All Plan IDs
            $plans = Plan::select('id')->where("product_id", '=', $this->_search_params['product'])->get();

            $planArr = array();

            foreach($plans as $plan)
            {
                $planArr[] = $plan->id;
            }

            $transaction = $transaction->whereIn("$_tbl_transactions.plan_id", $planArr);
        }

        if($this->_search_params['affiliate'])
        {
            if($this->_search_params['affiliate'] == "no-affiliate")
            {
                $transaction = $transaction->where("$_tbl_purchases.affiliate_id", '=', NULL);
            }
            else
            {
                $transaction = $transaction->where("$_tbl_purchases.affiliate_id", '=', $this->_search_params['affiliate']);
            }
        }

        // Quick Search
        if($this->_search_params['q'])
        {
            $transaction = $transaction->orWhere("$_tbl_buyers.email", '=', $this->_search_params['q'])
                                        ->orWhere("$_tbl_buyers.first_name", 'LIKE', $this->_search_params['q']."%")
                                        ->orWhere("$_tbl_buyers.last_name", 'LIKE', $this->_search_params['q']."%")
                                        ->orWhere(DB::raw('CONCAT('.$_tbl_buyers.'.first_name," ",'.$_tbl_buyers.'.last_name)'), 'LIKE', $this->_search_params['q']."%")
                                        ->orWhere("$_tbl_transactions.pay_id", 'LIKE', "%".$this->_search_params['q']."%");
        }

        // Email Search
        if(!empty($this->_search_params['email']))
        {
            $transaction = $transaction->where("$_tbl_buyers.email", '=', $this->_search_params['email']);
        }

        $transaction = $transaction->whereBetween("$_tbl_transactions.updated_at", array($from, $to));

        return $transaction->paginate(25);
    }

    public function setSearchParams($params)
    {
        $this->_search_params = $params;
    }
    
    private function _build_search_params()
    {

        // Fetch all params
        if(Input::get('search') AND Input::get('q'))
        {
            $this->_search_params = array(
                "from" => "1970-01-01",
                "to" => date("Y-m-d", time()),
                "range" => "custom",
                "product" => NULL,
                "affiliate" => NULL,
                "paid" => 1,
                "refunded" => 1,
                "search" => "true",
                "q" => Input::get('q')
            );
        }
        elseif(Input::get('search'))
        {
            $from = NULL;
            $to = NULL;
            $range = Input::get('range');
            $paid = NULL;
            $refunded = NULL;

            if($range == 'custom')
            {
                $from = Input::get('from');
                $to = Input::get('to');
            }
            else
            {
                $dates = $this->getRangeDates($range);

                $from = $dates['from'];
                $to = $dates['to'];
            }

            $this->_search_params = array(
                "from" => $from,
                "to" => $to,
                "range" => Input::get('range'),
                "product" => Input::get('product'),
                "affiliate" => Input::get('affiliate'),
                "paid" => Input::get('paid'),
                "refunded" => Input::get('refunded'),
                "search" => "true",
                "q" => NULL
            );
        }
        else
        {
            $dates = $this->getRangeDates("month");

            $from = $dates['from'];
            $to = $dates['to'];

            $this->_search_params = array(
                "from" => $from,
                "to" => $to,
                "range" => "month",
                "product" => NULL,
                "affiliate" => NULL,
                "paid" => 1,
                "refunded" => 1,
                "search" => "true",
                "q" => NULL
            );
        }
    }

    public function getSearchParams()
    {
        return $this->_search_params;
    }

    private function getRangeDates($range)
    {
        $to = date("Y-m-d", time());

        switch ($range) {
            case 'today':
                $from = date("Y-m-d", time());
                break;
            case 'week':
                $from = date("Y-m-d", strtotime('Last Monday', time()));
                break;
            case 'month':
                $from = date("Y-m-d", strtotime("first day of this month", time()));
                break;
            case 'last-month':
                $from = date("Y-m-d", strtotime("first day of last month", time()));
                $to = date("Y-m-d", strtotime("last day of last month", time()));
                break;
            case 'year':
                $from = date("Y", time()) . "-01-01";
                break;
            
            default:
                $from = date("Y-m-d", strtotime("first day of this month", time()));
                break;
        }

        return array('from'=>$from, 'to'=>$to);
    }

    public function refundQueueCount()
    {
        $transaction = new Transaction();
        $transaction = $transaction->where('is_refunded', '=', 1)->where("commission_refunded", '=', 0)->count('id');
        return $transaction;
    }

    static function getRefundQueue()
    {
        $_tbl_transactions = Transaction::getTableName();
        $_tbl_purchases = Purchase::getTableName();
        $_tbl_products = Product::getTableName();
        $_tbl_plans = Plan::getTableName();
        $_tbl_affiliates = Affiliate::getTableName();
        $_tbl_buyers = Buyer::getTableName();

        $fields = array("$_tbl_transactions.*",
                    "$_tbl_purchases.affiliate_id",
                    "$_tbl_purchases.product_id",
                    "$_tbl_products.name AS product_name",
                    "$_tbl_plans.name AS plan_name",
                    "$_tbl_affiliates.name AS affiliate_name",
                    "$_tbl_buyers.first_name AS buyer_first_name",
                    "$_tbl_buyers.last_name AS buyer_last_name",
                    "$_tbl_buyers.email AS buyer_email");

        $transaction = DB::table($_tbl_transactions)
                            ->join($_tbl_purchases, "$_tbl_purchases.id", '=', "$_tbl_transactions.purchase_id")
                            ->join($_tbl_products, "$_tbl_products.id", '=', "$_tbl_purchases.product_id")
                            ->join($_tbl_plans, "$_tbl_plans.id", '=', "$_tbl_transactions.plan_id")
                            ->join($_tbl_buyers, "$_tbl_buyers.id", '=', "$_tbl_purchases.buyer_id")
                            ->leftJoin($_tbl_affiliates, "$_tbl_affiliates.id", '=', "$_tbl_purchases.affiliate_id")
                            ->select($fields);

        $transaction = $transaction->where("$_tbl_transactions.is_refunded", '=', 1)->where("$_tbl_transactions.commission_refunded", '=', 0);


        return $transaction->paginate(25);
    }

    /**
     * Post manual additions transacton form
     */
    static function addManually($params)
    {
        extract($params);
        
        $ipn_url = Config::get('project.paypal_ipn_url');

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

        $members[] = array(
            'email' => $email,
            'fname' => $first_name,
            'lname' => $last_name
        );

        $data = array();

        if($members)
        {
            foreach($members as $member)
            {
                // Add or get buyer
                if($buyer = Buyer::getOrCreate($member))
                {
                    // Get Plan
                    $plan = Plan::find($plan_id);

                    if($plan AND !$plan->is_oto)
                    {
                        // Add purchase for the buyer
                        $purchase = new Purchase();

                        $purchase->buyer_id = $buyer->id;
                        $purchase->product_id = $product_id;
                        $purchase->plan_id = $plan_id;
                        $purchase->stripe_token = $stripe_token ? $stripe_token : NULL;
                        $purchase->paypal_sub_id = $paypal_sub_id ? $paypal_sub_id : NULL;
                        $purchase->pay_method = 2;
                        $purchase->affiliate_id = $affiliate_id;

                        // If method is Stripe
                        if($pay_id AND DKHelpers::GetPayMethod($pay_id) == 'Stripe')
                        {
                            $purchase->pay_method = 1;
                        }
                        
                        $purchase->save();
                    }

                    // Push to PayPal IPN of DK
                    $ipn_data = array(
                        'plan_id' => $plan_id,
                        'product_id' => $product_id,
                        'email' => $buyer->email,
                        'first_name' => $buyer->first_name,
                        'last_name' => $buyer->last_name,
                        'password' => $password,
                        'transaction_id' => $pay_id ? $pay_id : 'MNL-' . time(),
                        'amount' => $amount,
                        'manual_transaction' => TRUE
                    );

                    if($password)
                    {
                        $ipn_data['dk_new_user'] = TRUE;
                    }
                    else
                    {
                        $ipn_data['dk_new_charge'] = TRUE;
                        $ipn_data['buyer_id'] = $buyer->id;
                    }

                    // Add Curl library
                    require_once(app_path() . "/libraries/Curl/Curl.php");
                    
                    // Post data to IPN
                    $curl = New Curl;
                    $curl->simple_post($ipn_url, $ipn_data, array(CURLOPT_BUFFERSIZE => 10));
                }
            }
        }

        return TRUE;
    }
	
	protected function getDateFormat()
    {
        return 'U';
    }
    
	public function purchase()
    {
        return $this->belongsTo('Purchase');
    }
    
	public function plan()
    {
        return $this->belongsTo('Plan');
    }

    /**
     * Refund the transaction
     */
    static function refund($id, $force_refund = FALSE)
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
                 *   ## Creating service wrapper object
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

        if(empty($error) OR $force_refund)
        {
            self::completeRefund($transaction);

            return TRUE;
        }
    }

    /**
     * Complete refund and push notification
     */
    static function completeRefund($transaction)
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
        $curl->simple_post($transaction->purchase->product->ipn_url, $ipn_data, array(CURLOPT_BUFFERSIZE => 10));

        // Send refund email to buyer
        self::send_email_refund($transaction->purchase->product->name, $transaction->plan->name, $transaction->purchase->buyer->email, $transaction->pay_id, $transaction->amount);
            
        return TRUE;
    }

    /**
     * Send receipt to the customer
     */
    static function send_email_refund($product, $plan, $email, $transaction_id, $price)
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