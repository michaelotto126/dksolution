<?php

class LicensesController extends BaseController {

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
		
		$this->_data['section'] = "licenses";
	}
	
	public function getIndex()
	{
		// Page Title
		$this->_data['page_title'] = "Licenses";
		
		$this->_data['licenses'] = License::search();
		
		return View::make('admin.licenses.index', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	public function getUsage($license_key)
	{
		// Page Title
		$this->_data['page_title'] = "Licenses - See All Usage";
		
		$this->_data['uses'] = LicensesUses::getAllUsage($license_key);

		$this->_data['license_key'] = $license_key;
		
		return View::make('admin.licenses.usage', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	public function getPayload()
	{
		$code = Input::get('code');
		$api_key = Input::get('api_key');
		$license_key = Input::get('license');
		$activate = Input::get('activate');

		$current_url = url("admin/licenses/payload?code=$code&api_key=$api_key&license=$license_key");

		// Page Title
		$this->_data['page_title'] = "Licenses - Get Payload";

		$this->_data['license_key'] = $license_key;

		$this->_data['uses'] = LicensesUses::getAllUsage($license_key);

		// Include libraries
		require_once(app_path() . "/libraries/Curl/Curl.php");

		$params = array("code"=>$code, "license"=>$license_key);

		$curl = New Curl;

		if($activate) $params['guid'] = time();

		$params['key'] = DKHelpers::GenerateHash($params, $api_key);

		if($activate)
		{
			$response = $curl->simple_post(url('api/v1/license-manager/activate'), $params, array(CURLOPT_BUFFERSIZE => 10));

			$response = json_decode($response);

			if(isset($response->success) AND $response->success == 'false')
			{
				if(isset($response->overusage) AND $response->overusage == 'true')
				{
					Session::flash('alert_error', '<strong>Alert!</strong> License cannot be activated due to overusage.');
					return Redirect::to($current_url);
				}
			}
			else
			{
				Session::flash('alert_message', '<strong>Done!</strong> License has been activated successfully.');
				return Redirect::to($current_url);
			}
		}

		// Get Payload
		$this->_data['payload'] = $curl->simple_post(url('api/v1/license-manager'), $params, array(CURLOPT_BUFFERSIZE => 10));
		
		$this->_data['activate_url'] = $current_url . '&activate=true';

		return View::make('admin.licenses.payload', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	public function getDeleteUsage($id)
	{
		// Page Title
		$this->_data['page_title'] = "Licenses - Delete a used license";
		
		$this->_data['use'] = LicensesUses::getUsage($id);
		
		return View::make('admin.licenses.delete-usage', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	public function postDeleteUsage($id)
	{	
		// Get Usage
		$usage = LicensesUses::find($id);

		// Get License
		$license = License::find($usage->license_id);

		// Delete Usage
		$usage->delete();
			
		Session::flash('alert_message', '<strong>Done!</strong> You successfully have deleted a license usage.');
		return Redirect::to("admin/licenses/usage/$license->license_key");
	}

	public function getChangeUsage($license_key)
	{
		// Page Title
		$this->_data['page_title'] = "Licenses - Change Maxium Allowed Usage";
		
		$this->_data['license'] = License::where('license_key', '=', $license_key)->first();
		
		return View::make('admin.licenses.change-usage', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

	public function postChangeUsage($license_key)
	{
		$license = License::where('license_key', '=', $license_key)->first();

		// Update License
		$license->allowed_usage = Input::get('allowed_usage');
		$license->save();

		Session::flash('alert_message', '<strong>Done!</strong> You successfully have changed allowed usage of a license.');
		return Redirect::to("admin/licenses/change-usage/$license->license_key");
	}

	public function getRevoke($license_key)
	{
		$license = License::where('license_key', '=', $license_key)->first();

		// Update License
		$license->status = 0;
		$license->save();

		Session::flash('alert_message', "<strong>Done!</strong> You successfully have revoked the access of a license ($license->license_key).");
		return Redirect::to("admin/licenses");
	}

	public function getActivate($license_key)
	{
		$license = License::where('license_key', '=', $license_key)->first();

		// Update License
		$license->status = 1;
		$license->save();

		Session::flash('alert_message', "<strong>Done!</strong> You successfully have re-activated the access of a license ($license->license_key).");
		return Redirect::to("admin/licenses");
	}
}