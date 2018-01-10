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

$frm->initialize(new Profile());

if($frm->send()) // && $frm->validate()
{
	try 
	{
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$profile->is_notification_enabled = input_int($p_is_notification_enabled);
		$profileDAO->update($profile);

		$frm->addMessage('Perfil atualizado');
		$frm->clear();	

	} catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$profile->picture = $profile->picture ? $profile->picture : FILES_URL . DEFAULT_PROFILE_PICTURE;
$_POST = get_object_vars($profile);

$tpl->set($_POST);

$tpl->checkbox('is_notification_enabled');

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>