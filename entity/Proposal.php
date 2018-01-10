<?php
class Proposal {
	
	/*  */
	public $proposal_id = NULL;

	/*  */
	public $profile_id = NULL;

	/*  */
	public $topic_id = 2; //id banco

	/*  */
	public $is_qualified = 0;
	
	/*  */
	public $is_selected = 0;
	
	/*  */
	public $title = NULL;
	
	/*  */
	public $authors = NULL;
	
	/*  */
	public $abstract = NULL;
	
	/*  */
	public $description = NULL;
	
	/*  */
	public $benefit = NULL;	
	
	/*  */
	public $executor = NULL;	
	
	/*  */
	public $audience = NULL;
	
	/*  */
	public $investment_level = NULL;	
	
	/*  */
	public $reference = NULL;
	
	/*  */
	public $votes = 0;	
	
	/*  */
	public $submit_date = NULL;	
	
	function __construct()
	{
		$this->submit_date = current_datetime();		
	}

	public function toArray() 
	{
		return get_object_vars($this);	
	}	
}
?>