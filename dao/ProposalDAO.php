<?php
class ProposalDAO extends GenericPDO {
	
	protected $table = 'ic_proposal';
	protected $id = 'proposal_id';

	function __construct()
	{
		parent::__construct();
	}
	
	public function findFullById($proposal_id)
	{
		return $this->fetch(
			'SELECT p.*, t.topic, t.url, pf.name, pf.email, pf.is_notification_enabled 
			FROM ic_proposal p
			INNER JOIN ic_topic t ON t.topic_id = p.topic_id
			INNER JOIN ic_profile pf ON pf.profile_id = p.profile_id
			WHERE p.proposal_id = ? 
			AND p.is_qualified = ?', 
			array($proposal_id, 1)
		);	
	}

	public function countAllQualified()
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_proposal WHERE is_qualified = ?', 
			array(1)
		);	
	}
	
	public function countByTitle($title)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_proposal WHERE title = ?', 
			array($title)
		);	
	}
	
	public function countQualifiedByTopic($topic_id)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_proposal WHERE topic_id = ? AND is_qualified = ?', 
			array($topic_id, true)
		);	
	}	

	public function listAllQualified()
	{
		return $this->fetchAll(
			'SELECT p.*, t.topic, t.url FROM ic_proposal p
			INNER JOIN ic_topic t ON t.topic_id = p.topic_id
			WHERE p.is_qualified = ? ORDER BY RAND()', 
			array(1)
		);	
	}
	
	public function listQualifiedByTopic($topic_id)
	{
		return $this->fetchAll(
			'SELECT p.*, t.topic, t.url FROM ic_proposal p 
			INNER JOIN ic_topic t ON t.topic_id = p.topic_id
			WHERE p.topic_id = ? AND p.is_qualified = ? 
			ORDER BY RAND()', 
			array($topic_id, true)
		);	
	}
	
	public function listByProfile($profile_id)
	{
		return $this->fetchAll(
			'SELECT p.*, t.topic, t.url FROM ic_proposal p 
			INNER JOIN ic_topic t ON t.topic_id = p.topic_id
			WHERE p.profile_id = ? 
			ORDER BY p.submit_date ASC', 
			array($profile_id)
		);	
	}
}
?>