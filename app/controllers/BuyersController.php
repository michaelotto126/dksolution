<?php

class BuyersController extends BaseController {
	
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
		
		$this->_data['section'] = "users";
	}
	
	/**
	 * Buyers
	 */
	public function getIndex()
	{
		// Page Title
		$this->_data['page_title'] = "Buyers";
		
		$this->_data['buyers'] = Buyer::search();
		
		return View::make('admin.buyers.index', $this->_data)
						->nest('header', 'admin.common.header', $this->_data)
						->nest('footer', 'admin.common.footer', $this->_data);
	}

}