<?php
class CommentDAO extends GenericPDO {
	
	protected $table = 'ic_comment';
	protected $id = 'comment_id';

	function __construct()
	{
		parent::__construct();
	}
	
	public function countByProposal($proposal_id)
	{
		return $this->count(
			'SELECT COUNT(*) FROM ic_comment WHERE proposal_id = ? AND is_inactive = ?', 
			array($proposal_id, false)
		);	
	}

	public function listByProposal($proposal_id)
	{
		return $this->fetchAll(
			'SELECT c.*, p.name FROM ic_comment c  
			INNER JOIN ic_profile p ON p.profile_id = c.profile_id
			WHERE c.proposal_id = ? AND c.is_inactive = ?
			ORDER BY c.comment_date ASC', 
			array($proposal_id, false)
		);	
	}
}
?>