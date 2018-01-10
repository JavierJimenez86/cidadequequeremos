<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/dao/TopicDAO.php';
require ROOT_PATH . '/dao/ProposalDAO.php';
require ROOT_PATH . '/dao/CommentDAO.php';

$tpl->load();

require 'inc.auth.php';

$tpl->set('selected_label', '');

$url = get('url');
$topic_id = 0;

$topicDAO = new TopicDAO();
$proposalDAO = new ProposalDAO();
$commentDAO = new CommentDAO();

$topicList = $topicDAO->listAll();

foreach ($topicList as $topic)
{
	$tpl->block('topic', array(
		'url' => $topic->url,
		'topic' => $topic->topic,
		'proposal_count' => $proposalDAO->countQualifiedByTopic($topic->topic_id),
		'css' => $url == $topic->url ? 'proposal-tab-selected' : NULL
	));	
	
	if ($url == $topic->url)
	{
		$tpl->set('selected_label', ' em ' . $topic->topic);
		$topic_id = $topic->topic_id;	
	}
}


$proposalList = $url ? $proposalDAO->listQualifiedByTopic($topic_id) : $proposalDAO->listAllQualified();
foreach ($proposalList as $proposal)
{
	$tpl->block('list', array(
		'proposal_id' => $proposal->proposal_id,
		'votes' => $proposal->votes,
		'title' => $proposal->title,
		'comment_count' => $commentDAO->countByProposal($proposal->proposal_id)		
	));	
}

$tpl->set('css_all', $url ? NULL : 'proposal-tab-selected');
$tpl->set('total_count', $proposalDAO->countAllQualified());
$tpl->show();
?>