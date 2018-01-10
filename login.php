<?php
require 'inc.includes.php';
require 'inc.tpl.php';
require ROOT_PATH . '/api/facebook/facebook.php';
require ROOT_PATH . '/api/google/Google_Client.php';
require ROOT_PATH . '/api/google/contrib/Google_Oauth2Service.php';
require ROOT_PATH . '/entity/Profile.php';
require ROOT_PATH . '/dao/ProfileDAO.php';

$tpl->load();

require 'inc.auth.php';

$frm = new Form();

$redirect = get('redirect');

$frm->initialize(new Profile());
$frm->initialize(array(
	'recaptcha_challenge_field' => NULL,
	'recaptcha_response_field' => NULL,
	'terms' => NULL
));

if($frm->send() && $frm->validate())
{
	try 
	{
		$profileDAO = new ProfileDAO();
		
		extract($_POST, EXTR_PREFIX_ALL, 'p');

		if ($profileDAO->countByEmailAndPassword($p_email, sha1($p_password)) == 0)
		{
			throw new Exception('Nenhum cadastro encontrado com os dados informados');	
		}
		
		/* find in db */
		$profile = $profileDAO->findByEmailAndPassword($p_email, sha1($p_password));
		
		if (!$profile)
		{
			throw new Exception('Perfil não encontrado');	
		}

		/* register session */
		$securityUtil = new SecurityUtil();
		
		$user = array(
			'profile_id' => $profile->profile_id,
			'profile_name' => $profile->name,
			'profile_email' => $profile->email,
			'is_judge' => $profile->is_judge
			//'profile_picture' => $profile->picture ? $profile->picture : PICTURE_URL . DEFAULT_PROFILE_PICTURE
		);

		$securityUtil->registerUser($user);
		
		if ($redirect)
		{
			redirect(ROOT_URL . '/' .$redirect);	
		}
		
		redirect(ROOT_URL . '/ideias');

	} catch (Exception $e) 
	{
		$frm->addError($e->getMessage());	
	} 
}


/* facebook 
$config = array(
  'appId' => '168751066534331',
  'secret' => '4c029dc5874f0ae3937307eed228dc00',
  'fileUpload' => false, 
  'allowSignedRequest' => false 
);

$facebook = new Facebook($config);

$params = array(
  'scope' => 'email,public_profile',
  'redirect_uri' => 'http://www.ifrocolorado.com.br/cidadequequeremos/api/facebook/return.php'
);

$facebook_login_url = $facebook->getLoginUrl($params);
*/
/* google 
$client = new Google_Client();
$client->setClientId('277579686639.apps.googleusercontent.com');
$client->setClientSecret('h9EKQDJDnbzJBB4ZJTTp4FKS');
$client->setRedirectUri('http://www.ifrocolorado.com.br/cidadequequeremos/api/google/return.php');
$client->setDeveloperKey('AIzaSyDdMVleG5xTN0rmyIqvqLM1YgE9pwY1q8o');

$client->setScopes(array(
	'https://www.googleapis.com/auth/userinfo.profile', 
	'https://www.googleapis.com/auth/userinfo.email', 
	'https://www.googleapis.com/auth/plus.me'
));

$auth = new Google_Oauth2Service($client);

$client->setApprovalPrompt('auto');

$google_login_url = $client->createAuthUrl();

$tpl->set('facebook_login_url', $facebook_login_url);
$tpl->set('google_login_url', $google_login_url);
*/

$tpl->set('redirect', $redirect);

$tpl->set('token', $frm->token());
$tpl->set('errors', $frm->getErrorsAsHtml());
$tpl->set('messages', $frm->getMessagesAsHtml());
$tpl->show();
?>