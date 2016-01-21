<?php

class RedirectController extends BaseController {
	
	public function getAffiliate()
	{
		echo Cookie::get('_dks_isa');
	}

	/**
	 * Redirect user to appropriate product landing page.
	 * It also creates a cookie for the affiliate tracking
	 * 
	 * @param $app String
	 */

	public function getIndex($app)
	{
		// Get Affiliate ID
		$affiliate_id = Input::get('affiliate');
		
		// Get product Landing page url
		if($product = Product::where('code', '=', $app)->first())
		{
			$product_url = $product->landing_url;
			
			// if there is encoded link
			if(Input::get('redirect'))
			{
				$product_url = urldecode(Input::get('redirect'));
			}
		}
		else
		{
			// Error, redirect to somewhere
			return Redirect::to(Config::get("project.website"));
		}
		
		// Create cookie for affiliate and Redirect to Landing page
		if($affiliate_id)
		{
			$cookie = Cookie::forever('_dks_isa', $affiliate_id);
			return Redirect::to($product_url)->withCookie($cookie);
		}
		else
		{
			return Redirect::to($product_url);
		}
	}
}