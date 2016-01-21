<?php

class Affiliate extends BaseModel {

	protected $table = 'affiliates';
	public $timestamps = false;
	
	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	/*public function getAuthIdentifier()
	{
		return $this->getKey();
	}*/
	
	protected function getDateFormat()
    {
        return 'U';
    }
}