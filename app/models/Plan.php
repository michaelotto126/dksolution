<?php

class Plan extends BaseModel {


	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	/*public function getAuthIdentifier()
	{
		return $this->getKey();
	}*/

	static function getAvailableFEPlans($product_id)
	{
		return Plan::where('product_id', '=', $product_id)
						->where('is_oto', '=', 0)
						->where('status', '=', 1)
						->where('show_at_checkout', '=', 1)
						->get();
	}
	
	protected function getDateFormat()
    {
        return 'U';
    }

}