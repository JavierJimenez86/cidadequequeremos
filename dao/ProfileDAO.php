<?php
class ProfileDAO extends GenericPDO {
	
	protected $table = 'ic_profile';
	protected $id = 'profile_id';

	function __construct()
	{
		parent::__construct();
	}
	
	public function findFullById($profile_id)
	{
		return $this->fetch(
			'SELECT p.*, c.city, s.state FROM ic_profile p 
			LEFT JOIN ic_city c ON c.city_id = p.city_id
			LEFT JOIN ic_state s ON s.state_id = c.state_id
			WHERE profile_id = ?', 
			array($profile_id)
		);
	}
	
	public function countBySocialId($social_id)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_profile WHERE social_id = ?', 
			array($social_id)
		);	
	}
	
	public function countByEmail($email)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_profile WHERE email = ?', 
			array($email)
		);	
	}
	
	public function countByEmailAndPassword($email, $password)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_profile WHERE email = ? AND password = ?', 
			array($email, $password)
		);	
	}
	
	public function findBySocialId($social_id)
	{
		return $this->fetch(
			'SELECT * FROM ic_profile WHERE social_id = ?', 
			array($social_id)
		);	
	}
	
	public function findByEmail($email)
	{
		return $this->fetch(
			'SELECT * FROM ic_profile WHERE email = ?', 
			array($email)
		);
	}
	
	public function findByEmailAndPassword($email, $password)
	{
		return $this->fetch(
			'SELECT * FROM ic_profile WHERE email = ? AND password = ?', 
			array($email, $password)
		);
	}
}
?>