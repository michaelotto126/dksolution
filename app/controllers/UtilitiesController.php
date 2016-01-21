<?php

class UtilitiesController extends BaseController {

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
		
		$this->_data['section'] = "utilities";
	}

	/**
	 * Show index
	 */
	public function getIndex()
	{
		$this->_data['page_title'] = "System Utilities";

		return View::make('admin.utilities.index', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Show manual additions transacton form
	 */
	public function getManualTransaction()
	{
		$this->_data['page_title'] = "Add Manual Transaction";

		$this->_data['affiliates'] = Affiliate::orderBy('name', 'ASC')->get();
		$this->_data['products'] = Product::orderBy('name', 'ASC')->get();
		$this->_data['plans'] = Plan::orderBy('name', 'ASC')->where('product_id', '=', Input::old('product_id'))->get();

		return View::make('admin.utilities.manual-transaction', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Post manual additions transacton form
	 */
	public function postManualTransaction()
	{
		$rules = array(
				'first_name' => 'required',
		        'last_name' => 'required',
		        'email' => 'required',
		        'password' => '',
				'product_id' => 'required|numeric',
				'plan_id' => 'required|numeric',
				'pay_id' => '',
				'stripe_token' => '',
				'paypal_sub_id' => '',
				'amount' => 'required|numeric',
				'affiliate_id' => 'numeric',
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to('admin/utilities/manual-transaction')->withErrors($validator)->withInput();
	    }
	    else
	    {
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
				Session::flash('alert_message', '<strong>Well done!</strong> You successfully have added new transaction.');
				return Redirect::to('admin/utilities/manual-transaction');
			}
	    }
	}

	/**
	 * Show generate license form
	 */
	public function getGenerateLicense()
	{
		$this->_data['page_title'] = "Generate License";

		$this->_data['affiliates'] = Affiliate::orderBy('name', 'ASC')->get();
		$this->_data['products'] = Product::orderBy('name', 'ASC')->get();
		$this->_data['plans'] = Plan::orderBy('name', 'ASC')->where('product_id', '=', Input::old('product_id'))->get();

		return View::make('admin.utilities.generate-license', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	/**
	 * Generate license
	 */
	public function postGenerateLicense()
	{
		$rules = array(
				'transaction_id' => 'required'
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to('admin/utilities/generate-license')->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$transaction_id = Input::get('transaction_id');

	    	if($transaction = Transaction::where('id', '=', $transaction_id)->first())
	    	{
	    		if($license = License::where('transaction_id', '=', $transaction_id)->first())
		    	{
		    		Session::flash('alert_error', '<strong>Ooops!</strong> License for given transaction already exists.');
					return Redirect::to('admin/licenses?q=' . $license->license_key . '&param=key');
		    	}

	    		$plan = Plan::where('id', '=', $transaction->plan_id)->first();

	    		if($plan->has_license)
				{
					$product = Product::where('id', '=', $plan->product_id)->first();

					$license_key = License::generate($product->code);

					// Save license
					$license = new License();

					$license->license_key = $license_key;
					$license->transaction_id = $transaction_id;
					$license->allowed_usage = $plan->license_allowed_usage;

					$license->save();

					Session::flash('alert_message', '<strong>Well done!</strong> You successfully have generated license key.');
					return Redirect::to('admin/licenses?q=' . $license_key . '&param=key');
				}
				else
				{
					Session::flash('alert_error', '<strong>Ooops!</strong> This plan does not allow to generate a license key.');
					return Redirect::to('admin/utilities/generate-license');
				}
	    	}
	    	else
	    	{
	    		Session::flash('alert_error', '<strong>Ooops!</strong> Transaction was not found.');
				return Redirect::to('admin/utilities/generate-license');
	    	}
	    }
	}

	/**
	 * Card Update URL
	 */
	public function getCardUpdateUrl()
	{
		$this->_data['page_title'] = "Generate Card Update URL";

		$this->_data['products'] = Product::orderBy('name', 'ASC')->get();

		return View::make('admin.utilities.card-update-url', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);	
	}

	/**
	 * Card Update URL
	 */
	public function postCardUpdateUrl()
	{
		$rules = array(
				'email' => 'email|required',
				'code' => 'required'
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::back()->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$code = urlencode(Crypt::encrypt(Input::get('code')));
			$email = urlencode(Crypt::encrypt(Input::get('email')));

	    	$url = url() . "/customer/update-card?e=$email&c=$code";

	    	return Redirect::back()->with('updateUrl', $url);
	    }
	}

}