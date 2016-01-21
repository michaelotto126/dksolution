<?php

class Product extends BaseModel {


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