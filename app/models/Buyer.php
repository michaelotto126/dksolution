<?php

class Buyer extends BaseModel {

	static function search()
	{
		$buyers = Buyer::orderBy('created_at', 'DESC');

		$q = Input::get('q');
    	$param = Input::get('param');

        if($q AND $param)
        {
            if($param == "email")
            {
                $buyers = $buyers->where("email", '=', $q);
            }

            if($param == "fname")
            {
                $buyers = $buyers->where("first_name", '=', $q);
            }

            if($param == "lname")
            {
                $buyers = $buyers->where("last_name", '=', $q);
            }
        }

        return $buyers->paginate(25);
	}

    static function getOrCreate($member)
    {
        $email = $member['email'];

        if($buyer = Buyer::where('email', '=', $email)->first())
        {
            return $buyer;
        }
        else
        {
            $buyer = new Buyer();

            $buyer->first_name = $member['fname'] ? $member['fname'] : 'First';
            $buyer->last_name = $member['lname'] ? $member['lname'] : 'Last';
            $buyer->email = $member['email'];

            $buyer->save();

            return $buyer;
        }
    }

	/*
	 * Updates byer last IP
	 *
	 * param $buyer object
	 */
	static function updateLastIP($buyer)
	{
		$buyer->last_used_ip = Request::getClientIp();

		$buyer->save();
	}

	protected function getDateFormat()
    {
        return 'U';
    }
    
	public function affiliate()
    {
        return $this->belongsTo('Affiliate');
    }

}