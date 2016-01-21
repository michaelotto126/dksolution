<?php

class ProductsController extends BaseController {
	
	/**
	 * Properties
	 */
	protected $_data;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->beforeFilter(function()
		{
			// Support should not be given access to products section
			if(Auth::user()->username == "support" OR Auth::user()->username == "rhoda" OR Auth::user()->username == "jedfelices")
			{
				Session::flash('alert_error', '<strong>Ooops!</strong> You do not have permission to access Products section.');
				return Redirect::to('admin/transactions');
			}
		});

		$this->_data = array();
		
		$this->_data['section'] = "products";
	}
	
	/**
	 * All Products
	 */
	public function getIndex()
	{
		// Page Title
		$this->_data['page_title'] = "Products";
		
		// Get All products
		$this->_data['products'] = Product::orderBy('name', 'ASC')->get();
		
		return View::make('admin.products.index', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Product Plans
	 */
	public function getPlans($product_id)
	{
		$this->_data['page_title'] = "Product Plans";
		
		// Get product
		$this->_data['product'] = $product = Product::find($product_id);
		
		// Get All Plans
		$this->_data['plans'] = Plan::where('product_id', $product->id)->get();
		
		return View::make('admin.products.plans', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Add Product
	 */
	public function getNewProduct()
	{
		$this->_data['page_title'] = "Add New Product";
		
		return View::make('admin.products.add', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Add Product
	 */
	public function postNewProduct()
	{
		$rules = array(
				'name' => 'required',
		        'code' => 'required|unique:products',
		        'type' => 'required',
		        'has_license' => 'numeric',
				'logo_url' => 'required|url',
				'landing_url' => 'required|url',
				'aweber_list_id' => '',
				'ipn_url' => 'url',
				'api_url' => 'required|url',
				'api_key' => '',
				'head_code' => '',
				'body_code' => '',
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to('admin/products/new-product')->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$product = new Product;

			$product->name 				= Input::get('name');
			$product->code 				= Input::get('code');
			$product->type 				= Input::get('type');
			$product->has_license 		= Input::get('has_license') ? 1 : 0;
			$product->logo_url 			= Input::get('logo_url');
			$product->landing_url 		= Input::get('landing_url');
			$product->aweber_list_id 	= Input::get('aweber_list_id');
			$product->ipn_url 			= Input::get('ipn_url');
			$product->api_url 			= Input::get('api_url');
			$product->api_key 			= Input::get('api_key');
			$product->head_code 		= Input::get('head_code');
			$product->body_code 		= Input::get('body_code');
			
			$product->save();
			
			Session::flash('alert_message', '<strong>Well done!</strong> You successfully have added new product.');
			return Redirect::to('admin/products');
	    }
	}
	
	/**
	 * Add Product Plan
	 */
	public function getNewPlan($product_id)
	{
		$this->_data['page_title'] = "Add new Product plan";
		
		$this->_data['product'] = Product::find($product_id);
		
		return View::make('admin.products.addPlan', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post New Plan
	 */
	public function postNewPlan($product_id)
	{
		$rules = array(
				'name' => 'required',
		        'code' => "required|unique:plans",
				'description' => 'required',
				'price' => 'required',
				'setup_fee' => 'required',
				'stripe_id' => 'required',
				'infusion_id' => 'required',
				'is_oto' => 'numeric',
				'is_lifetime' => 'numeric',
				'is_recurring' => 'numeric',
				'recurring_freq' => 'numeric',
				'allow_paypal_sub' => 'numeric',
				'has_license' => 'numeric',
				'license_allowed_usage' => 'numeric',
				'show_at_checkout' => 'numeric',
				'show_available_plans' => 'numeric',
				'has_split_pay' => 'numeric',
				'total_installments' => 'numeric',
				'split_pay_desc' => 'max:255',
				'price_per_installment' => 'numeric',
				'next_page_url' => 'required|url',
				'order_btn_text_1' => 'required|max:255',
				'order_btn_text_2' => 'max:255'
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to("admin/products/new-plan/$product_id")->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$plan = new Plan;

			$plan->name 					= Input::get('name');
			$plan->code 					= Input::get('code');
			$plan->product_id 				= $product_id;
			$plan->description 				= Input::get('description');
			$plan->price 					= Input::get('price');
			$plan->setup_fee 				= Input::get('setup_fee');
			$plan->stripe_id 				= Input::get('stripe_id');
			$plan->infusion_id 				= Input::get('infusion_id');
			$plan->is_oto 					= Input::get('is_oto') ? 1 : 0;
			$plan->is_lifetime 				= Input::get('is_lifetime') ? 1 : 0;
			$plan->is_recurring 			= Input::get('is_recurring') ? 1 : 0;
			$plan->recurring_freq 			= Input::get('recurring_freq');
			$plan->allow_paypal_sub 		= Input::get('allow_paypal_sub') ? 1 : 0;
			$plan->has_license 				= Input::get('has_license') ? 1 : 0;
			$plan->license_allowed_usage 	= Input::get('license_allowed_usage');
			$plan->show_at_checkout 		= Input::get('show_at_checkout') ? 1 : 0;
			$plan->show_available_plans 	= Input::get('show_available_plans') ? 1 : 0;
			$plan->has_split_pay 			= Input::get('has_split_pay') ? 1 : 0;
			$plan->total_installments 		= Input::get('total_installments');
			$plan->split_pay_desc 			= Input::get('split_pay_desc');
			$plan->price_per_installment 	= Input::get('price_per_installment');
			$plan->next_page_url 			= Input::get('next_page_url');
			$plan->order_btn_text_1 		= Input::get('order_btn_text_1');
			$plan->order_btn_text_2 		= Input::get('order_btn_text_2');
			
			$plan->save();
			
			Session::flash('alert_message', '<strong>Well done!</strong> You successfully have added new plan.');
			return Redirect::to("admin/products/plans/$product_id");
	    }
	}
	
	/**
	 * Edit Product
	 */
	public function getEditProduct($id)
	{
		// Page Title
		$this->_data['page_title'] = "Products";
		
		// Get All products
		$this->_data['product'] = Product::find($id);
		
		return View::make('admin.products.edit', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Edit Product
	 */
	public function postEditProduct($id)
	{
		$product = Product::find($id);
		
		$rules = array(
				'name' => 'required',
		        'code' => ($product->code == Input::get('code') ? 'required' : 'required|unique:products'),
		        'type' => 'required',
		        'has_license' => 'numeric',
				'logo_url' => 'required|url',
				'landing_url' => 'required|url',
				'aweber_list_id' => '',
				'ipn_url' => 'url',
				'api_url' => 'required|url',
				'api_key' => '',
				'head_code' => '',
				'body_code' => '',
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to("admin/products/edit-product/$id")->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$product->name 				= Input::get('name');
			$product->code 				= Input::get('code');
			$product->type 				= Input::get('type');
			$product->has_license 		= Input::get('has_license') ? 1 : 0;
			$product->logo_url 			= Input::get('logo_url');
			$product->landing_url 		= Input::get('landing_url');
			$product->aweber_list_id 	= Input::get('aweber_list_id');
			$product->ipn_url 			= Input::get('ipn_url');
			$product->api_url 			= Input::get('api_url');
			$product->api_key 			= Input::get('api_key');
			$product->head_code 		= Input::get('head_code');
			$product->body_code 		= Input::get('body_code');
			
			$product->save();
			
			Session::flash('alert_message', '<strong>Well done!</strong> You successfully have updated product.');
			return Redirect::to('admin/products');
	    }
	}
	
	/**
	 * Edit Product plan
	 */
	public function getEditPlan($product_id, $id)
	{
		// Page Title
		$this->_data['page_title'] = "Edit Product Plan";
		
		// Get All products
		$this->_data['product'] = Product::find($product_id);
		$this->_data['plan'] = Plan::find($id);
		
		return View::make('admin.products.editPlan', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Edit Product
	 */
	public function postEditPlan($product_id, $id)
	{
		$product = Product::find($product_id);
		$plan = Plan::find($id);
		
		$rules = array(
				'name' => 'required',
		        'code' => ($plan->code == Input::get('code') ? 'required' : 'required|unique:plans'),
				'description' => 'required',
				'price' => 'required',
				'setup_fee' => 'required',
				'stripe_id' => 'required',
				'infusion_id' => 'required',
				'is_oto' => 'numeric',
				'is_lifetime' => 'numeric',
				'is_recurring' => 'numeric',
				'recurring_freq' => 'numeric',
				'allow_paypal_sub' => 'numeric',
				'has_license' => 'numeric',
				'license_allowed_usage' => 'numeric',
				'show_at_checkout' => 'numeric',
				'show_available_plans' => 'numeric',
				'has_split_pay' => 'numeric',
				'total_installments' => 'numeric',
				'split_pay_desc' => 'max:255',
				'price_per_installment' => 'numeric',
				'next_page_url' => 'required|url',
				'order_btn_text_1' => 'required|max:255',
				'order_btn_text_2' => 'max:255'
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to("admin/products/edit-product/$id")->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$plan->name 					= Input::get('name');
			$plan->code 					= Input::get('code');
			$plan->product_id 				= $product_id;
			$plan->description 				= Input::get('description');
			$plan->price 					= Input::get('price');
			$plan->setup_fee 				= Input::get('setup_fee');
			$plan->stripe_id 				= Input::get('stripe_id');
			$plan->infusion_id 				= Input::get('infusion_id');
			$plan->is_oto 					= Input::get('is_oto') ? 1 : 0;
			$plan->is_lifetime 				= Input::get('is_lifetime') ? 1 : 0;
			$plan->is_recurring 			= Input::get('is_recurring') ? 1 : 0;
			$plan->recurring_freq 			= Input::get('recurring_freq');
			$plan->allow_paypal_sub 		= Input::get('allow_paypal_sub') ? 1 : 0;
			$plan->has_license 				= Input::get('has_license') ? 1 : 0;
			$plan->next_page_url 			= Input::get('next_page_url');
			$plan->license_allowed_usage 	= Input::get('license_allowed_usage');
			$plan->show_at_checkout 		= Input::get('show_at_checkout') ? 1 : 0;
			$plan->show_available_plans 	= Input::get('show_available_plans') ? 1 : 0;
			$plan->has_split_pay 			= Input::get('has_split_pay') ? 1 : 0;
			$plan->total_installments 		= Input::get('total_installments');
			$plan->split_pay_desc 			= Input::get('split_pay_desc');
			$plan->price_per_installment 	= Input::get('price_per_installment');
			$plan->order_btn_text_1 		= Input::get('order_btn_text_1');
			$plan->order_btn_text_2 		= Input::get('order_btn_text_2');
			
			$plan->save();
			
			Session::flash('alert_message', '<strong>Well done!</strong> You successfully have updated the plan.');
			return Redirect::to("admin/products/plans/$product_id");
	    }
	}

	/**
	 * Change Plan status
	 */
	public function getChangePlanStatus($product_id, $plan_id)
	{
		$plan = Plan::where('id', '=', $plan_id)->first();

		$plan->status = $plan->status ? 0 : 1;
		$plan->save();

		Session::flash('alert_message', '<strong>Well done!</strong> You successfully have updated the plan status.');
		return Redirect::to("admin/products/plans/$product_id");		
	}
	
	/**
	 * Product Customize
	 */
	public function getCustomize($product_id)
	{
		$this->_data['page_title'] = "Product checkout page customization";
		
		$this->_data['product'] = $product = Product::find($product_id);
		
		$this->_data['customize'] = json_decode($product->colors);
		
		return View::make('admin.products.customize', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Product Customize
	 */
	public function postCustomize($product_id)
	{
		$product = Product::find($product_id);
		
		$rules = array(
				'cta_btn_color' => 'required',
		        'cta_btn_hover_color' => 'required',
				'background_color' => 'required',
				'sidebar_text' => 'required'
		);
		 
		$validator = Validator::make(Input::all(), $rules);
		
		if ($validator->fails())
	    {
	        return Redirect::to("admin/products/customize/$product_id")->withErrors($validator)->withInput();
	    }
	    else
	    {
	    	$colors = array(
	    		"cta_btn_color" 		=> Input::get('cta_btn_color'),
		    	"cta_btn_hover_color" 	=> Input::get('cta_btn_hover_color'),
		    	"background_color" 		=> Input::get('background_color'),
		    	"sidebar_text" 			=> Input::get('sidebar_text')
	    	);
	    	$product->colors 				= json_encode($colors);
			
			$product->save();
			
			Session::flash('alert_message', '<strong>Well done!</strong> You successfully have customized the product checkout page.');
			return Redirect::to("admin/products");
	    }
	}
	
	/**
	 * Delete a Product
	 */
	public function getDeleteProduct($product_id)
	{
		$this->_data['page_title'] = "Delete product confirmation";
		
		$this->_data['product'] = $product = Product::find($product_id);
		
		return View::make('admin.products.delete', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Delete a Product
	 */
	public function postDeleteProduct($product_id)
	{
		$product = Product::find($product_id);
		$product->delete();
		
		Session::flash('alert_message', '<strong>Done!</strong> You successfully have deleted the product.');
		return Redirect::to("admin/products");
	}
	
	/**
	 * Delete a Product Plan
	 */
	public function getDeletePlan($product_id, $id)
	{
		$this->_data['page_title'] = "Delete plan confirmation";
		
		$this->_data['product'] = $product = Product::find($product_id);
		$this->_data['plan'] = $plan = Plan::find($id);
		
		return View::make('admin.products.deletePlan', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}
	
	/**
	 * Post Delete a Product Plan
	 */
	public function postDeletePlan($product_id, $id)
	{
		$product = Product::find($product_id);
		
		$plan = Plan::find($id);
		$plan->delete();
		
		Session::flash('alert_message', '<strong>Done!</strong> You successfully have deleted the plan.');
		return Redirect::to("admin/products/plans/$product->id");
	}

	/**
	 * Get all plans of a product
	 */
	public function getGetPlansByProduct($product_id)
	{
		$plansObj = Plan::orderBy('name', 'ASC')->where('product_id', '=', $product_id)->get();
		$plans = array();

		if($plansObj)
		{
			foreach($plansObj as $plan)
			{
				$plans[$plan->id] = $plan->name;
			}
		}

		return json_encode($plans);
	}

}