<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/api/recaptcha/recaptchalib.php';
require ROOT_PATH . '/dao/ProfileDAO.php';
require ROOT_PATH . '/include/Email.php';

$tpl->load();

require 'inc.auth.php';

$publickey = '6LfLD-QSAAAAABKEao_sY3KBYgnP9J34YZTGl9pY';
$privatekey = '6LfLD-QSAAAAAKfTAKnPWSXunxutZ-MZUjko9z9F'; 

$block = 'form';

$frm = new Form();

$frm->initialize(array(
	'recaptcha_challenge_field' => NULL,
	'recaptcha_response_field' => NULL,
	'email' => NULL,
	'birth_date' => NULL
));

if($frm->send() && $frm->validate())
{
	try 
	{
		$profileDAO = new ProfileDAO();
		
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		/*
		$resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		
		if (!$resp->is_valid) 
		{
			throw new Exception('O texto de verificação está incorreto');	
		}
		*/
		
		$profile = $profileDAO->findByEmail($p_email);
		
		if (!$profile || $profile->birth_date != input_date($p_birth_date))
		{
			throw new Exception('Nenhum registro encontrado com os dados informados');	
		}
		
		/* generate random password */
		$password = substr(md5(microtime()), rand(1, 11), 8);

		/* send email */
		$email = new Email('recover');
		$email->set('password', $password);

		if (!$email->send('Não responder', APP_EMAIL, $profile->email, 'Recuperação de acesso - Cidade que queremos'))
		{
			throw new Exception('Não foi possível enviar o e-mail. Tente novamente.');	
		}
		else
		{
			/* update password in db */
			$profile->password = sha1($password);
			$profileDAO->update($profile);	
		}
		
		$block = 'complete';
		
		//$frm->addMessage('Uma nova senha foi gerada e enviada para o endereço de e-mail informado.');
		$frm->clear();	

	} catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$tpl->set($_POST);

$tpl->block($block);	

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>