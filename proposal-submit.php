<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/api/recaptcha/recaptchalib.php';
require ROOT_PATH . '/entity/Proposal.php';
require ROOT_PATH . '/entity/Picture.php';
require ROOT_PATH . '/dao/ProposalDAO.php';
require ROOT_PATH . '/dao/TopicDAO.php';
require ROOT_PATH . '/dao/PictureDAO.php';
require ROOT_PATH . '/include/Email.php';

$tpl->load();

require 'inc.auth.php';

$currentProfile = $securityUtil->getCurrentUser();

$publickey = '6LfLD-QSAAAAABKEao_sY3KBYgnP9J34YZTGl9pY';
$privatekey = '6LfLD-QSAAAAAKfTAKnPWSXunxutZ-MZUjko9z9F'; 

$topicDAO = new TopicDAO();
$frm = new Form();

$frm->initialize(new Proposal());
$frm->initialize(array(
	'pictures' => array(),
	'legends' => array()
));

if($frm->send() && $frm->validate())
{
	try 
	{
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		/*
		$resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		
		if (!$resp->is_valid) 
		{
			throw new Exception('O texto de verificação está incorreto');	
		}
		*/
		
		/* check form */
		$abstract = explode(' ', $p_abstract);
		
		if (sizeof($p_authors) > 1000)
		{
			throw new Exception('Autores deve ter no máximo 1.000 caracteres');	
		}
		
		if (sizeof($abstract) > 300)
		{
			throw new Exception('Resumo deve ter no máximo 300 palavras');	
		}
		
		if (strlen($p_description) > 15000)
		{
			throw new Exception('Descrição deve ter no máximo 15.000 caracteres');	
		}
		
		if (strlen($p_benefit) > 3000)
		{
			throw new Exception('Descrição deve ter no máximo 3.000 caracteres');	
		}
		
		if (strlen($p_executor) > 3000)
		{
			throw new Exception('Responsáveis pela execução deve ter no máximo 3.000 caracteres');	
		}
		
		if (strlen($p_audience) > 3000)
		{
			throw new Exception('Público alvo deve ter no máximo 3.000 caracteres');	
		}
		
		if (strlen($p_investment_level) > 3000)
		{
			throw new Exception('Nível de investimento deve ter no máximo 3.000 caracteres');	
		}
		
		if (strlen($p_reference) > 5000)
		{
			throw new Exception('Referências deve ter no máximo 5.000 caracteres');	
		}
		
		if (sizeof($p_pictures) > 6)
		{
			throw new Exception('Você pode enviar no máximo 6 imagens');		
		}
		
		$proposalDAO = new ProposalDAO();
		$pictureDAO = new PictureDAO();
		
		/* check title */
		if ($proposalDAO->countByTitle($p_title) > 0)
		{
			throw new Exception('Já existe uma proposta com o título informado');		
		}

		if (!$currentProfile)
		{
			throw new Exception('É necessário entrar para submeter uma proposta');		
		}
		
		/* insert proposal in db */
		$proposal = new Proposal();
		$proposal->topic_id = input_int($p_topic_id);
		$proposal->profile_id = $currentProfile['profile_id'];
		$proposal->title = input_string($p_title);
		$proposal->authors = input_string($p_authors);
		$proposal->abstract = input_string($p_abstract);
		$proposal->description = input_string($p_description);
		$proposal->benefit = input_string($p_benefit);
		$proposal->executor = input_string($p_executor);
		$proposal->audience = input_string($p_audience);
		$proposal->investment_level = input_string($p_investment_level);
		$proposal->reference = input_string($p_reference);
		$proposal->title = input_string($p_title);
		$proposal_id = $proposalDAO->insert($proposal);
		
		/* insert pictures in db */
		foreach ($p_pictures as $i => $p)
		{
			/* move files */
			FileUtil::renameFile(FILES_PATH . '/temp/' . $p, FILES_PATH . '/proposal/' . $p);
			FileUtil::renameFile(FILES_PATH . '/temp/thumbnail/' . $p, FILES_PATH . '/proposal/thumbnail/' . $p);

			$picture = new Picture();	
			$picture->proposal_id = $proposal_id;
			$picture->picture = $p;
			$picture->legend = input_string($p_legends[$i]);
			$pictureDAO->insert($picture); 	
		}
		
		/* send email */
		$email = new Email('proposal');
		$email->set('name', $currentProfile['profile_name']);
		$email->set('proposal_id', $proposal_id);
		$email->set('proposal_title', $proposal->title);
		$email->send('Não responder', APP_EMAIL, 'hedi.minin@ifro.edu.br', 'Nova proposta - Cidade que queremos');
		
		redirect(ROOT_URL . '/ideias/submissao/concluido');
		
		$frm->addMessage('Proposta submetida com sucesso');
		$frm->clear();	

	}
	catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

if (!$currentProfile)
{
	$tpl->block('submit_login');		
}
else
{
	$tpl->block('submit_form');
	$tpl->set($_POST);
	$tpl->radioList('topic_id', $topicDAO->listAll(), array('key' => 'topic_id', 'label' => 'topic'));	
	
	foreach ($_POST['pictures'] as $i => $picture)
	{
		$tpl->block('picture', array(
			'picture' => $picture,
			'legend' => $_POST['legends'][$i]
		));	
	}
}

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>