<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return Redirect::to(Config::get("project.website"));
});

Route::get('users', function()
{
    return 'Users!';
});

Route::get('admin/login', array('uses' => 'AdminController@getLogin'));
Route::post('admin/login', array('uses' => 'AdminController@postLogin'));
Route::get('admin/logout', array('uses' => 'AdminController@getLogout'));
Route::get('admin', array('uses' => 'AdminController@getIndex'));

Route::get('redirect/{app}', array('uses' => 'RedirectController@getIndex'));
Route::get('redirect/get/affiliate', array('uses' => 'RedirectController@getAffiliate'));

Route::controller('home', 'HomeController');

Route::controller('checkout', 'CheckoutController');
Route::controller('ipn', 'IpnController');
Route::controller('api/v1', 'ApiController');
Route::controller('customer', 'CustomersController');

Route::group(array('before' => 'auth'), function()
{
	Route::controller('payments', 'PaymentsController');
	Route::controller('admin/products', 'ProductsController');
	Route::controller('admin/transactions', 'TransactionsController');
	Route::controller('admin/customers', 'BuyersController');
	Route::controller('admin/licenses', 'LicensesController');

	Route::controller('admin/utilities', 'UtilitiesController');
});