<?php
class Comment {
	
	/*  */
	public $comment_id = NULL;

	/*  */
	public $proposal_id = NULL;
	
	/*  */
	public $profile_id = NULL;

	/*  */
	public $is_inactive = 0;
	
	/*  */
	public $comment = NULL;
	
	/*  */
	public $comment_date = NULL;

	function __construct()
	{
		$this->comment_date = current_datetime();		
	}

	public function toArray() 
	{
		return get_object_vars($this);	
	}	
}
?>