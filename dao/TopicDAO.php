<?php
class TopicDAO extends GenericPDO {
	
	protected $table = 'ic_topic';
	protected $id = 'topic_id';

	function __construct()
	{
		parent::__construct();
	}

	public function listAll()
	{
		return $this->fetchAll('SELECT * FROM ic_topic ORDER BY topic ASC');	
	}
	
	public function listCountByProposal()
	{
		return $this->fetchAll('SELECT t.*, COUNT(p.proposal_id) AS proposal_count FROM ic_topic t 
		LEFT JOIN ic_proposal p ON p.topic_id = t.topic_id
		WHERE p.is_qualified = ?
		GROUP BY t.topic_id
		ORDER BY t.topic_id ASC', 
		array(1));	
	}
}
?>