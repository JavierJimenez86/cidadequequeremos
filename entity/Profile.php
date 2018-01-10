<?php
class Profile {
	
	/*  */
	public $profile_id = NULL;

	/*  */
	public $city_id = 4364; //Colorado do Oeste

	/*  */
	public $social_id = 0;

	/*  */
	public $is_inactive = 0;
	
	/*  */
	public $is_valid = 1;
	
	/*  */
	public $is_judge = 0;
	
	/*  */
	public $is_notification_enabled = 0;
	
	/*  */
	public $name = NULL;
	
	/*  */
	public $email = NULL;
	
	/*  */
	public $genre = 'M';	
	
	/*  */
	public $birth_date = NULL;	
	
	/*  */
	public $picture = NULL;
	
	/*  */
	public $password = NULL;	
	
	/*  */
	public $scholarity = 0;
	
	/*  */
	public $validation_key = NULL;	
	
	/*  */
	public $star_used = 0;
	
	/*  */
	public $register_date = NULL;	
	
	function __construct()
	{
		$this->register_date = current_datetime();		
	}

	public function toArray() 
	{
		return get_object_vars($this);	
	}	
}
?>