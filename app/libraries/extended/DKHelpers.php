<?php

class DKHelpers
{
	static function GenerateHash($params, $secret_key)
	{
		$paramStrArr = array();
		$paramStr = NULL;

		foreach($params as $key=>$value)
		{
			$key = str_replace('amp;', '', $key); // Fixed for HeatMapTracker

			// Ignore if it is encrypted key
			if($key == "key") continue;

			if(!$key OR !$value) continue;

			$paramStrArr[] = (string) $value;
		}

		sort($paramStrArr);

		$paramStr = implode("|", $paramStrArr);

		$encKey = hash_hmac('sha1', $paramStr, $secret_key);
		
		return $encKey;
	}

	/**
	 * Determine Payment method
	 */	
	static function GetPayMethod($pay_id)
	{
		if(substr( $pay_id, 0, 3 ) === "ch_")
		{
			return 'Stripe';
		}
		else
		{
			return 'PayPal';
		}
	}

	/**
	 * Convert a comma separated file into an associated array.
	 * The first row should contain the array keys.
	 * 
	 * @param string $filename Path to the CSV file
	 * @param string $delimiter The separator used in the file
	 * @return array
	 */
	static function CSVToArray($filename='', $delimiter=',')
	{
		if(!file_exists($filename) || !is_readable($filename))
			return FALSE;
		
		$header = NULL;
		$data = array();
		if (($handle = fopen($filename, 'r')) !== FALSE)
		{
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
			{
				if(!$header)
					$header = $row;
				else
					$data[] = array_combine($header, $row);
			}
			fclose($handle);
		}
		return $data;
	}
}