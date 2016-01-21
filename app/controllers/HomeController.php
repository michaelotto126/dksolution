<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showWelcome()
	{
		return View::make('hello');
	}
	
	/*
	|--------------------------------------------------------------------------
	| Test Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function getTest()
	{
		//require_once app_path() .  DIRECTORY_SEPARATOR . "";
		require_once(app_path() . "/libraries/infusionsoft/isdk.php");
		$isapp = new iSDK;
		
		// Get Affiliate ID from Cookie
		$affiliate_id = Cookie::get('_dks_isa');
		
		// Create Connection
		if ($isapp->cfgCon("comissionTracker")) {
	
			// find contact by email
			$contacts = $isapp->findByEmail('fraz@jeeglo.com', array('Id', 'Email'));
			
			// If contact found
			if(!empty($contacts[0]['Id']))
			{
				$contact_id = $contacts[0]['Id'];
			}
			else
			{
				// Create new contact
				$contactData = array('Email'	=> 'fraz@jeeglo.com');
				$contact_id = $isapp->addCon($contactData);
			}
			
			echo $contact_id;
	
			echo "<br><br>";
			
			// Testing Order Through Invoice
	
			//Sets current date
		    $currentDate = date("d-m-Y");
		    $oDate = $isapp->infuDate($currentDate);
		    echo "date set<br/>";
	
			//Creates blank order
		    $newOrder = $isapp->blankOrder( $contact_id , "New Order for Contact $contact_id", $oDate, NULL, $affiliate_id);
		    echo "newOrder=" . $newOrder . "<br/>";
		    
		    //$newOrder = 710;
	
		    // Add Order Item - Product ID
		    // type = 4 or 9 (Product or Subscription)
		    $result = $isapp->addOrderItem($newOrder, 24, 4, 50.00, 1, "Sale Made From API", "Generated Through API");
		    echo "item added<br/>";
		    //print_r($result);
	
		    // Add Manual Payment - since CC charged with Stripe
		    $payment = $isapp->manualPmt($newOrder, 50.00, $oDate, "credit", "Order done through API / Stripe", false);
		    print_r($payment);
	
		} else {
			echo "Connection Failed";
		}
		
		//$affiliate_id = Cookie::get('_dks_isa');
		//echo "hi " . app_path();
	}
	
	/**
	 * Some test for username
	 */
	public function getApi()
	{
		return '{"user_exists": false}';
	}
	
	/**
	 * Some test for IPN
	 */
	public function postIpn()
	{
		echo "IPN";
	}

}