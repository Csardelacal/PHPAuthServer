<?php namespace spitfire\model\adapters;

use spitfire\exceptions\PrivateException;

/**
 * This adapter is in charge of ensuring that only integers reach the database
 * channeled through it. So, whenever the user sets data to it we will perform
 * a check to see if it's Integer data. Otherwise they'll get an exception.
 */
class FloatAdapter extends BaseAdapter
{
	/**
	 * This adapter verifies that anything that reaches it is a valid integer value,
	 * otherwise it will throw an exception and stop any potentially unsafe 
	 * operation.
	 * 
	 * @param int $data
	 * @throws PrivateException
	 */
	public function usrSetData($data) {
		//Check if the incoming data is an int
		if ( $data !== null && !is_numeric($data) ) {
			throw new PrivateException('This adapter only accepts floats and nulls');
		}
		//Make sure the finally stored data is an integer.
		parent::usrSetData($data === null? null : (float)$data);
	}
}

