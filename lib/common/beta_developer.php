<?php
/**
 * beta_developer
 *
 **/
class beta_developer
{
	/**
	 * constructor
	 *
	 * @param array $array
	 */
	function __construct($array = NULL) 
	{
		//set developer values
		if(is_array($array))
		{
			$this->id = $array['_id'];
			$this->email = $array['email'];
			$this->status = $array['status'];
		}
	}
	
	/**
	 * user id
	 *
	 * @var MongoId
	 **/
	var $id;
	
	/**
	 * user email
	 *
	 * @var string
	 **/
	var $email;
	
	/**
	 * approved
	 *
	 * @var bool
	 **/
	var $status;
		
	/**
	 * returns JSON of the object
	 *
	 * @return string
	 **/
	function toJson()
	{
		return json_encode($this->toArray());
	}
	
	/**
	 * returns object array representation
	 *
	 * @return Array
	 **/
	function toArray()
	{
		return array(
			'email' => $this->email,
		  	'status' => $this->status
		);
	}
	
} // END class
?>