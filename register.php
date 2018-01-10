<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/api/recaptcha/recaptchalib.php';
require ROOT_PATH . '/entity/Profile.php';
require ROOT_PATH . '/dao/ProfileDAO.php';
require ROOT_PATH . '/dao/CityDAO.php';
require ROOT_PATH . '/dao/StateDAO.php';
require ROOT_PATH . '/type/Sexo.php';

$tpl->load();

require 'inc.auth.php';

$publickey = '6LfLD-QSAAAAABKEao_sY3KBYgnP9J34YZTGl9pY';
$privatekey = '6LfLD-QSAAAAAKfTAKnPWSXunxutZ-MZUjko9z9F'; 

$cityDAO = new CityDAO();
$stateDAO = new StateDAO();
$frm = new Form();

$redirect = get('redirect');

$frm->initialize(new Profile());
$frm->initialize(array(
	'recaptcha_challenge_field' => NULL,
	'recaptcha_response_field' => NULL,
	'terms' => NULL,
	'state_id' => 22
));

if($frm->send() && $frm->validate())
{
	try 
	{
		$profileDAO = new ProfileDAO();
		
		extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$resp = recaptcha_check_answer($privatekey, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
		
		if (!$resp->is_valid) 
		{
			throw new Exception('O texto de verificação está incorreto');	
		}
		
		if (!input_int($p_terms)) 
		{
			throw new Exception('É necessário aceitar os termos de uso para continuar');	
		}
		
		if ($profileDAO->countByEmail($p_email) > 0)
		{
			throw new Exception('Já existe um cadastro com o e-mail informado');	
		}
		
		/* register in db */
		$profile = new Profile();
		$profile->city_id = input_int($p_city_id);
		$profile->name = input_string($p_name);
		$profile->email = input_string($p_email);
		$profile->genre = input_string($p_genre);
		$profile->birth_date = input_date($p_birth_date);
		$profile->password = sha1(input_string($p_password));
		$profile->is_notification_enabled = input_int($p_is_notification_enabled);
		$profile->validation_key = sha1(microtime());
		
		$profile_id = $profileDAO->insert($profile);
		
		/* register session */
		$securityUtil = new SecurityUtil();

		$securityUtil->registerUser(array(
			'profile_id' => $profile_id,
			'profile_name' => $profile->name,
			'profile_email' => $profile->email,
			'is_judge' => $profile->is_judge
			//'profile_picture' => PICTURE_URL . DEFAULT_PROFILE_PICTURE
		));
		
		if ($redirect)
		{
			redirect(ROOT_URL . '/' .$redirect);	
		}
		
		redirect(ROOT_URL . '/ideias');

		$frm->addMessage('Cadastro concluído');
		$frm->clear();	

	} catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}

$tpl->set($_POST);

$tpl->select('state_id', $stateDAO->listAll(), array('key' => 'state_id', 'label' => 'state', 'required'));
$tpl->select('city_id', $cityDAO->listByState($_POST['state_id']), array('key' => 'city_id', 'label' => 'city', 'empty' => 'Selecione um Estado'));
$tpl->radioList('genre', Sexo::getList());
$tpl->checkbox('terms');
$tpl->checkbox('is_notification_enabled');

$tpl->set('redirect', $redirect);

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>