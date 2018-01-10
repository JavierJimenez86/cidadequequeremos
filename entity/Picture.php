<?php
class Picture {
	
	/*  */
	public $picture_id = NULL;

	/*  */
	public $proposal_id = NULL;

	/*  */
	public $picture = NULL;
	
	/*  */
	public $legend = NULL;

	function __construct() {}

	public function toArray() 
	{
		return get_object_vars($this);	
	}	
}
?>