<?php
require '../../include/functions.php';
require '../../include/SecurityUtil.php';

require '../../include/GenericPDO.php';
require '../../include/PDODefault.php';

require '../../dao/ProfileDAO.php';
require '../../entity/Profile.php';

require 'facebook.php';

$config = array(
  'appId' => '168751066534331',
  'secret' => '4c029dc5874f0ae3937307eed228dc00',
  'fileUpload' => false, 
  'allowSignedRequest' => false 
);

$facebook = new Facebook($config);

$social_id = $facebook->getUser();

$social_profile = $facebook->api('/me', 'GET');

if ($social_profile)
{
	try
	{
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
				$profile->picture = 'https://graph.facebook.com/' . $social_id . '/picture?height=111&width=111';
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
else
{
	//error	
}
?>