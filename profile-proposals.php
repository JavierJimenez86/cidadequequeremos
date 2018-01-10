<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/dao/ProposalDAO.php';
require ROOT_PATH . '/dao/CommentDAO.php';

$tpl->load();

require 'inc.auth.php';

if (!$securityUtil->isUserRegistered())
{
	redirect(ROOT_URL . '/entrar');	
}

$currentProfile = $securityUtil->getCurrentUser();

$proposalDAO = new ProposalDAO();
$commentDAO = new CommentDAO();

$proposalList = $proposalDAO->listByProfile($currentProfile['profile_id']);
foreach ($proposalList as $proposal)
{
	$tpl->block('list', array(
		'proposal_id' => $proposal->proposal_id,
		'votes' => $proposal->votes,
		'title' => $proposal->title,
		'topic' => $proposal->topic,
		'submit_date' => $proposal->submit_date,
		'status' => $proposal->is_qualified ? 'Publicada' : 'Pré-seleção'
		//'comment_count' => $commentDAO->countByProposal($proposal->proposal_id)		
	));	
}

$tpl->show();
?>