<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Project related configurations
	|--------------------------------------------------------------------------
	|
	| Define all extra configuration that are needed for this particular
	| application.
	|
	*/

	'website' => "http://digitalkickstart.com",

	/*'stripe_pub_key' => "pk_test_wfQkhni4ryjpEEKZCbXoqfn5",
	'stripe_secret_key' => "sk_test_anuGRNsWgWS0YmxEmuytZPaU",*/

	'stripe_pub_key' => "pk_live_0bW7tuvhS5JhMajHBWQjfAf2",
	'stripe_secret_key' => "sk_live_Pxh9E2MVYlmaGqJxcPpDEwWO ",

	/*'paypal_mode' => 'sandbox', // live | sandbox
	'paypal_api_url' => 'https://www.sandbox.paypal.com/',
	'paypal_api_username' => 'seller-dkapp_api1.yahoo.com',
	'paypal_api_password' => '1384427408',
	'paypal_api_signature' => 'An5ns1Kso7MWUdW4ErQKJJJ4qi4-ACMd2zACX5qJ-paBXXrNPdxKO33j',*/

	'paypal_mode' => 'live', // live | sandbox
	'paypal_api_url' => 'https://www.paypal.com/',
	'paypal_api_username' => 'mark_api1.searchcreatively.com',
	'paypal_api_password' => '22RH4J7XMMY4ENZ7',
	'paypal_api_signature' => 'AeJAsqiYZ-3ZtFLPibor0xPuQhZvAOiFEiB.e55fRnblr1GnDRHTICqe',

	'paypal_ipn_url' => url("ipn/index/paypal"),

	'infusion_soft_invoice_url' => 'https://fo123.infusionsoft.com/Job/manageJob.jsp?view=edit&ID=',

	'postmark_key' => 'da7a5fd6-1edd-42b6-a1d4-83a398cbe86a',
	'postmark_sender_email' => 'support@digitalkickstart.com'

);
