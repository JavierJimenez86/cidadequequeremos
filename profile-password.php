<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/api/recaptcha/recaptchalib.php';
require ROOT_PATH . '/entity/Profile.php';
require ROOT_PATH . '/dao/ProfileDAO.php';

$tpl->load();

require 'inc.auth.php';

if (!$securityUtil->isUserRegistered())
{
	redirect(ROOT_URL . '/entrar');	
}

$currentUser = $securityUtil->getCurrentUser();
$profileDAO = new ProfileDAO();

$profile = $profileDAO->findById($currentUser['profile_id']);

$frm = new Form();

$frm->initialize(array(
	'current_password' => NULL,
	'password' => NULL,
	'password_retry' => NULL
));

if($frm->send() && $frm->validate())
{
	try 
	{
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		if ($profile->password != sha1($p_current_password))
		{
			throw new Exception('Senha atual não confere');	
		}
		
		$profile->password = sha1(input_string($p_password));
		$profileDAO->update($profile);

		$frm->addMessage('Senha alterada');
		$frm->clear();	

	} catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$_POST = get_object_vars($profile);

$tpl->set($_POST);

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>