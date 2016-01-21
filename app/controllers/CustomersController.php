<?php

class CustomersController extends BaseController {

	
	public function getIndex()
	{
		return Redirect::to(Config::get("project.website"));
	}

	public function getUpdateCard()
	{
		$urlError = "You are using incorrect URL, please contact to support.";

		try {

			$email = Crypt::decrypt(urldecode(Input::get('e')));

			$this->_data['email'] = $email;

		} catch (Exception $e) {
			$this->_data['urlError'] = $urlError;
		}

		try {
			
			$code = Crypt::decrypt(urldecode(Input::get('c')));
			
			// Get Product by Code
			$this->_data['product'] = Product::where('code', $code)->first();

		} catch (Exception $e) {
			$this->_data['urlError'] = $urlError;
		}

		return View::make('customer.card-update', $this->_data);
	}

	public function postUpdateCard()
	{
		// Add Curl library
		require_once(app_path() . "/libraries/Curl/Curl.php");

		// Get Product
		$product = Product::where('code', Input::get('code'))->first();

		// Get Expiry month and year
		$expiry = explode(' / ', Input::get('ccExpire'));

		// Put all values in session
		Session::flash('ccNum', Input::get('ccNum'));
		Session::flash('ccExpire', Input::get('ccExpire'));
		Session::flash('ccCVC', Input::get('ccCVC'));

		$data = array(
			'email' => Input::get('email'),
			'code' => Input::get('code'),
			'number' => Input::get('ccNum'),
			'exp_month' => !empty($expiry[0]) ? $expiry[0] : NULL,
			'exp_year' => !empty($expiry[1]) ? $expiry[1] : NULL,
			'cvc' => Input::get('ccCVC')
		);

		$data['key'] = DKHelpers::GenerateHash($data, $product->api_key);

		$url = url() . "/api/v1/update-card";
		
		// Post data to IPN
		$curl = New Curl;
		$response = $curl->simple_post($url, $data, array(CURLOPT_BUFFERSIZE => 10));

		$response = json_decode($response);

		if(empty($response->error))
		{
			$success = "Your card (**** **** **** $response->last4) has been updated successfully.";

			return Redirect::back()->with('success', $success);
		}
		else
		{
			return Redirect::back()->with('error', $response->error);
		}
	}

}