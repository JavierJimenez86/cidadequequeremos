<?php
class PictureDAO extends GenericPDO {
	
	protected $table = 'ic_picture';
	protected $id = 'picture_id';

	function __construct()
	{
		parent::__construct();
	}

	public function listByProposal($proposal_id)
	{
		return $this->fetchAll(
			'SELECT * FROM ic_picture WHERE proposal_id = ?', 
			array($proposal_id)
		);	
	}
}
?>