<?php
require '../../include/functions.php';
require '../../include/SecurityUtil.php';

require '../../include/GenericPDO.php';
require '../../include/PDODefault.php';

require '../../dao/ProfileDAO.php';
require '../../entity/Profile.php';

require 'Google_Client.php';
require 'contrib/Google_Oauth2Service.php';

$client = new Google_Client();
$client->setClientId('277579686639.apps.googleusercontent.com');
$client->setClientSecret('h9EKQDJDnbzJBB4ZJTTp4FKS');
$client->setRedirectUri('http://www.ifrocolorado.com.br/cidadequequeremos/api/google/return.php');
$client->setDeveloperKey('AIzaSyDdMVleG5xTN0rmyIqvqLM1YgE9pwY1q8o');
		
$auth = new Google_Oauth2Service($client);

if (isset($_GET['code'])) 
{
	try
	{
		$client->authenticate();
	
		$social_profile = $auth->userinfo->get();
		$social_id = $social_profile['id'];
		
		$profileDAO = new ProfileDAO();
		$profile = NULL;
		
		/* if registered, find in db */
		if ($profileDAO->countBySocialId($social_id) > 0)
		{
			$profile = $profileDAO->findBySocialId($social_id);	
		}
		else
		{
			/* if email not registered, register in db */
			if ($profileDAO->countByEmail($social_profile['email']) == 0)
			{
				$profile = new Profile();
				$profile->name = input_string($social_profile['name']);
				$profile->email = input_string($social_profile['email']);
				$profile->picture = $social_profile['picture'] . '?sz=111';
				$profile->password = sha1(microtime());
				$profile->validation_key = sha1(microtime());
				
				$profile->profile_id = $profileDAO->insert($profile);	
			}
			else
			{
				throw new Exception('Já existe um cadastro com o e-mail vinculado a esta conta.');	
			}
		}
		
		/* register session */
		$securityUtil = new SecurityUtil();
	
		$securityUtil->registerUser(array(
			'profile_id' => $profile->profile_id,
			'profile_name' => $profile->name,
			'profile_email' => $profile->email,
			'profile_picture' => $profile->picture
		));
		
		redirect('../../');
	
	}
	catch (Exception $e)
	{
		print $e->getMessage();	
	}
}
?>