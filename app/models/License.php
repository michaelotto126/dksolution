<?php

class License extends BaseModel {
	
	protected function getDateFormat()
    {
        return 'U';
    }

    /**
     * Generate unique license key
     */
    static function generate($product_code = NULL)
    {
    	if(!$product_code) return FALSE;

    	do 
		{
			$license_key = strtoupper($product_code . '-' . str_random(4) . '-' . str_random(4) . '-' . str_random(4) . '-' . str_random(4));
			$unique = TRUE;
			
        	$license = License::where('license_key', '=', $license_key)->first();
		  	
		  	if($license)
		  	{
		  		$unique = FALSE;
		  	}

		} while (!$unique);
		
		return $license_key;
    }

    /**
    * Search Licenses
    */
    static function search($q = NULL, $param = NULL, $product_code = NULL)
    {
        $_tbl_licenses = License::getTableName();
        $_tbl_licensesUses = LicensesUses::getTableName();
        $_tbl_transactions = Transaction::getTableName();
        $_tbl_purchases = Purchase::getTableName();
        $_tbl_products = Product::getTableName();
        $_tbl_plans = Plan::getTableName();
        $_tbl_buyers = Buyer::getTableName();

        $fields = array(
                    "$_tbl_licenses.*",
                    DB::raw("COUNT($_tbl_licensesUses.id) AS totalUsed"),
                    "$_tbl_buyers.first_name",
                    "$_tbl_buyers.last_name",
                    "$_tbl_buyers.email",
                    "$_tbl_products.code",
                    "$_tbl_plans.code AS plan_code",
                    "$_tbl_products.api_key");

        $licenses = DB::table($_tbl_licenses)
                            ->leftJoin($_tbl_licensesUses, "$_tbl_licensesUses.license_id", '=', "$_tbl_licenses.id")
                            ->join($_tbl_transactions, "$_tbl_transactions.id", '=', "$_tbl_licenses.transaction_id")
                            ->join($_tbl_plans, "$_tbl_transactions.plan_id", '=', "$_tbl_plans.id")
                            ->join($_tbl_purchases, "$_tbl_purchases.id", '=', "$_tbl_transactions.purchase_id")
                            ->join($_tbl_products, "$_tbl_products.id", '=', "$_tbl_purchases.product_id")
                            ->join($_tbl_buyers, "$_tbl_buyers.id", '=', "$_tbl_purchases.buyer_id")
                            ->select($fields)
                            ->groupBy("$_tbl_licenses.id");

    	$q = $q ? $q : Input::get('q');
    	$param = $param ? $param : Input::get('param');

        if($q)
        {
            if($param == "key")
            {
                $licenses = $licenses->where("license_key", '=', $q);
            }

            if($param == "email")
            {
                $licenses = $licenses->where("email", '=', $q);
            }

            if($product_code)
            {
                $licenses = $licenses->where($_tbl_licenses . ".license_key", 'LIKE', strtoupper($product_code) .'-%');
            }
        }

        return $licenses->orderBy($_tbl_licenses . '.created_at', 'DESC')->paginate(25);
    }

    public function transaction()
    {
        return $this->belongsTo('Transaction');
    }

    public function licenseUse()
    {
        return $this->hasMany('LicensesUses');
    }
}