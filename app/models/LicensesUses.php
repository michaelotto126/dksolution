<?php

class LicensesUses extends BaseModel {

	public $timestamps = false;

	static function getAllUsage($license_key)
	{
		$_tbl_licenses = License::getTableName();
        $_tbl_licensesUses = LicensesUses::getTableName();

        $fields = array("$_tbl_licensesUses.*", "$_tbl_licenses.license_key");

        $usage = DB::table($_tbl_licensesUses)
                            ->join($_tbl_licenses, "$_tbl_licenses.id", '=', "$_tbl_licensesUses.license_id")
                            ->select($fields)
                            ->where("$_tbl_licenses.license_key", '=', $license_key)->get();

        return $usage;
	}

	static function getUsage($id)
	{
		$_tbl_licenses = License::getTableName();
        $_tbl_licensesUses = LicensesUses::getTableName();

        $fields = array("$_tbl_licensesUses.*", "$_tbl_licenses.license_key");

        $usage = DB::table($_tbl_licensesUses)
                            ->join($_tbl_licenses, "$_tbl_licenses.id", '=', "$_tbl_licensesUses.license_id")
                            ->select($fields)
                            ->where("$_tbl_licensesUses.id", '=', $id)->first();

        return $usage;
	}
	
	protected function getDateFormat()
    {
        return 'U';
    }
}