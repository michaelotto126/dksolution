<?php

class AdminController extends BaseController {

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
	
	public function getIndex()
	{
		return Redirect::to("admin/transactions");
	}

	/**
	 * Show login
	 */
	public function getLogin()
	{	
		return View::make('login');
	}
	
	/**
	 * Login Post
	 */
	public function postLogin()
	{
			$validator = Validator::make(Input::all(), array(
                "username" => "required",
                "password" => "required"
            ));
            if ($validator->passes())
            {
            	$credentials = array(
                    "username" => Input::get("username"),
                    "password" => Input::get("password")
                );
                if (Auth::attempt($credentials))
                {
                    return Redirect::intended("admin/transactions");
                }
                else 
                {
                	Session::flash('alert_error', 'Wrong credentials!');
					return Redirect::to('admin/login');
                }
            }
            else
            {
                Session::flash('alert_error', 'Wrong credentials!');
				return Redirect::to('admin/login');
            }
	}
	
	/**
	 * Logout
	 */
	public function getLogout()
	{
		 Auth::logout();
		 return Redirect::to('admin/login');
	}
}