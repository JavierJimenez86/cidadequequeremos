<?php
class StateDAO extends GenericPDO {
	
	protected $table = 'ic_state';
	protected $id = 'state_id';

	function __construct()
	{
		parent::__construct();
	}

	public function listAll()
	{
		return $this->fetchAll('SELECT * FROM ic_state ORDER BY state ASC');	
	}
}
?>