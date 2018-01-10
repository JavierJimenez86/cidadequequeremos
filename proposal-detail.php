<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/dao/ProposalDAO.php';
require ROOT_PATH . '/dao/PictureDAO.php';
require ROOT_PATH . '/dao/CommentDAO.php';
require ROOT_PATH . '/entity/Comment.php';
require ROOT_PATH . '/include/Email.php';
//require ROOT_PATH . '/api/recaptcha/recaptchalib.php';

$tpl->load();

require 'inc.auth.php';

$frm = new Form();
$proposalDAO = new ProposalDAO();
$pictureDAO = new PictureDAO();
$commentDAO = new CommentDAO();

$recaptcha = false;
$publickey = '6LfLD-QSAAAAABKEao_sY3KBYgnP9J34YZTGl9pY';
$privatekey = '6LfLD-QSAAAAAKfTAKnPWSXunxutZ-MZUjko9z9F'; 

/* current profile */
$currentProfile = $securityUtil->getCurrentUser();

/* proposal detail */
$comment_id = 0;
$proposal_id = input_int(get('proposal_id'));

$proposal = $proposalDAO->findFullById($proposal_id);

if (!$proposal || !$proposal->is_qualified)
{
	redirect(ROOT_URL . '/ideias');	
}

$pictureList = $pictureDAO->listByProposal($proposal_id);
foreach ($pictureList as $picture)
{
	$tpl->block('picture', $picture);	
}

$tpl->set('proposal_id', $proposal->proposal_id);
$tpl->set('topic', $proposal->topic);
$tpl->set('url', $proposal->url);
$tpl->set('title', $proposal->title);
$tpl->set('abstract', nl2p($proposal->abstract));
$tpl->set('description', nl2p($proposal->description));
$tpl->set('benefit', nl2p($proposal->benefit));
$tpl->set('benefit', nl2p($proposal->benefit));
$tpl->set('executor', nl2p($proposal->executor));
$tpl->set('audience', nl2p($proposal->audience));
$tpl->set('investment_level', $proposal->investment_level ? nl2p($proposal->investment_level) : 'Não informado');
$tpl->set('reference', $proposal->reference ? nl2p($proposal->reference) : 'Não informado');

/* proposal vote */
if ($frm->send('submit-vote'))
{
		
}

/* proposal comments */
$frm->initialize(new Comment());

if ($frm->send('submit-comment') && $frm->validate())
{
	try
	{
		$profile_comment = input_string(strip_tags(post('comment')));
		
		if ($recaptcha)
		{
			$resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
			
			if (!$resp->is_valid) 
			{
				throw new Exception('O texto de verificação está incorreto');	
			}	
		}
		
		if (strlen($profile_comment) > 2000)
		{
			throw new Exception('O comentário deve ter no máximo 2.000 caracteres');	
		}
		
		if (!$currentProfile)
		{
			$frm->addError('É necessário entrar para enviar um comentário');		
		}	
		
		$comment = new Comment();
		$comment->proposal_id = $proposal->proposal_id;
		$comment->profile_id = $currentProfile['profile_id']; 
		$comment->comment = $profile_comment; 		
		$comment_id = $commentDAO->insert($comment);
		
		/* send email to author */
		if ($proposal->is_notification_enabled)
		{
			if ($proposal->profile_id != $currentProfile['profile_id'])
			{
				$email = new Email('comment');
				$email->set('comment_name', $currentProfile['profile_name']);
				$email->set('proposal_id', $proposal->proposal_id);
				$email->set('proposal_author', array_shift(explode(' ', $proposal->name)));
				$email->set('proposal_title', $proposal->title);
				$email->send('Não responder', APP_EMAIL, $proposal->email, $currentProfile['profile_name'] . ' comentou sua proposta - Cidade que queremos');
			}
		}
		
		$frm->clear();
	}
	catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$commentList = $commentDAO->listByProposal($proposal->proposal_id);
$total_comments = sizeof($commentList);

if ($total_comments == 0)
{
	$tpl->block('no_comments');	
}
else
{
	foreach ($commentList as $comment)
	{
		$tpl->block('comment', array(
			'comment_id' => $comment->comment_id,
			'name' => $comment->name,
			'comment' => nl2br($comment->comment),
			'comment_date' => $comment->comment_date
		));	
	}
}

if (!$currentProfile)
{
	$tpl->block('comment_login');		
}
else
{
	$tpl->block('comment_form', array(
		'profile_name' => $currentProfile['profile_name'],
		'comment' => $_POST['comment']
	));	
}

if ($recaptcha)
{
	$tpl->block('recaptcha');	
}

$tpl->set('total_comments', $total_comments);
$tpl->setCount('total_comments_label', $total_comments, array('Nenhum comentário', 'comentário', 'comentários'));

$tpl->set('comment_id', $comment_id);
$tpl->set('days_until', date_diff2('2014-10-26', date('Y-m-d')));
$tpl->set('has_errors', $frm->hasErrors() ? 'true' : 'false');
$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>