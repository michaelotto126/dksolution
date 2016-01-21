<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Project related local configurations
	|--------------------------------------------------------------------------
	|
	| Define all configuration that you want to override for local setup
	|
	*/

	'stripe_pub_key' => "pk_test_wfQkhni4ryjpEEKZCbXoqfn5",
	'stripe_secret_key' => "sk_test_anuGRNsWgWS0YmxEmuytZPaU",

	'paypal_mode' => 'sandbox', // live | sandbox
	'paypal_api_url' => 'https://www.sandbox.paypal.com/',
	'paypal_api_username' => 'seller-dkapp_api1.yahoo.com',
	'paypal_api_password' => '1384427408',
	'paypal_api_signature' => 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-ACMd2zACX5qJ-paBXXrNPdxKO33j',

	'paypal_ipn_url' => 'http://fraz.pagekite.me/Mark/DigitalKickstart/App/index.php/ipn/index/paypal'

);
