<?php

class Purchase extends BaseModel {


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

	public function transaction()
    {
        return $this->hasMany('Transaction');
    }
    
	public function product()
    {
        return $this->belongsTo('Product');
    }

    public function plan()
    {
        return $this->belongsTo('Plan');
    }
    
	public function buyer()
    {
        return $this->belongsTo('Buyer');
    }
    
	public function affiliate()
    {
        return $this->belongsTo('Affiliate');
    }
    
}