<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/entity/Proposal.php';
require ROOT_PATH . '/entity/Picture.php';
require ROOT_PATH . '/dao/ProposalDAO.php';
require ROOT_PATH . '/dao/TopicDAO.php';
require ROOT_PATH . '/dao/PictureDAO.php';

$tpl->load();

require 'inc.auth.php';

if (!$securityUtil->isUserRegistered())
{
	redirect(ROOT_URL . '/entrar');	
}

$currentProfile = $securityUtil->getCurrentUser();

$proposalDAO = new ProposalDAO();
$pictureDAO = new PictureDAO();
$topicDAO = new TopicDAO();
$frm = new Form();

$proposal_id = input_int(get('proposal_id'));
$proposal = $proposalDAO->findById($proposal_id);

if (!$proposal || $proposal->profile_id != $currentProfile['profile_id'])
{
	redirect(ROOT_URL . '/perfil/ideias');	
}

$frm->initialize(new Proposal());
$frm->initialize(array(
	'pictures' => array(),
	'legends' => array(),
	'legends_update' => array(),
	'remove' => array()
));

if($frm->send() && $frm->validate('proposal-submit.xml'))
{
	try 
	{
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
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
		
		if ((sizeof($p_pictures) + sizeof($p_legends_update)) > 6)
		{
			throw new Exception('Você pode enviar no máximo 6 imagens');		
		}

		/* check title 
		if ($proposalDAO->countByTitle($p_title) > 0)
		{
			throw new Exception('Já existe uma proposta com o título informado');		
		}
		*/

		if (!$currentProfile)
		{
			throw new Exception('É necessário entrar para submeter uma proposta');		
		}
		
		/* insert proposal in db */
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
		$proposalDAO->update($proposal);
		
		
		/* update pictures in db */
		foreach ($p_legends_update as $picture_id => $legend)
		{
			$picture = $pictureDAO->findById($picture_id);
			$picture->legend = input_string($legend);
			
			if (array_search($picture_id, $p_remove) !== false)
			{
				FileUtil::deleteFile(FILES_PATH . '/proposal/thumbnail/' . $picture->picture);
				FileUtil::deleteFile(FILES_PATH . '/proposal/' . $picture->picture);
				$pictureDAO->delete($picture_id);
			}
			else
			{
				$pictureDAO->update($picture); 
			}
		}
		
		/* insert pictures in db */
		foreach ($p_pictures as $i => $p)
		{
			$picture = new Picture();	
			$picture->proposal_id = $proposal_id;
			$picture->picture = $p;
			$picture->legend = input_string($p_legends[$i]);
			$pictureDAO->insert($picture); 	
		}

		/* remove pictures 
		foreach ($p_remove as $picture_id)
		{
			$pictureDAO->delete($picture_id); 		
		}
		*/

		$frm->addMessage('Proposta atualizada');
		$frm->clear();	

	}
	catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$_POST = get_object_vars($proposal);
$tpl->set($_POST);
$tpl->radioList('topic_id', $topicDAO->listAll(), array('key' => 'topic_id', 'label' => 'topic'));	


$pictureList = $pictureDAO->listByProposal($proposal_id);
foreach ($pictureList as $picture)
{
	$tpl->block('picture', $picture);	
}

//$frm->addWarning('A edição de propostas será permitida durante a fase de submissão');

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->set('warnings', $frm->getWarningsAsHtml());
$tpl->show();
?>