<?php
class CityDAO extends GenericPDO {
	
	protected $table = 'ic_city';
	protected $id = 'city_id';

	function __construct()
	{
		parent::__construct();
	}

	public function listByState($state_id)
	{
		return $this->fetchAll(
			'SELECT * FROM ic_city WHERE state_id = ? ORDER BY city ASC', 
			array($state_id)
		);	
	}
}
?>