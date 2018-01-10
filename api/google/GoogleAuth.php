<?php
class GoogleAuth {
	
	private $client_id = '277579686639.apps.googleusercontent.com';
	private $client_secret = 'h9EKQDJDnbzJBB4ZJTTp4FKS';
	private $redirect_uri = 'http://www.ifrocolorado.com.br/gapi/';
	private $developer_key = 'AIzaSyDdMVleG5xTN0rmyIqvqLM1YgE9pwY1q8o';
	
	function __construct() {}

	public function createAuthUrl()
	{
		$client = new Google_Client();
		$client->setClientId($this->client_id);
		$client->setClientSecret($this->client_secret);
		$client->setRedirectUri($this->redirect_uri);
		$client->setDeveloperKey($this->developer_key);
		
		$auth = new Google_Oauth2Service($client);
		
		$client->setApprovalPrompt('auto');
	
		return $client->createAuthUrl();
	}
	
	public function authenticate()
	{
		$client = new Google_Client();
		$client->setClientId($this->client_id);
		$client->setClientSecret($this->client_secret);
		$client->setRedirectUri($this->redirect_uri);
		$client->setDeveloperKey($this->developer_key);
		
		$auth = new Google_Oauth2Service($client);
		
		if (isset($_GET['code'])) 
		{
			$client->authenticate();
			
			set_timezone();
			
			$user = $auth->userinfo->get();
			
			if(mb_substr_count($user['email'], 'ifro.edu.br') == 0)
			{
				redirect('403.php?user=' . $user['email']);
			}
			
			$userManager = new UserManager();
			
			$r_user = $userManager->findByEmail($user['email']);
			
			if($r_user == NULL)
			{
				redirect('401.php');
			}
			
			$r_user->last_logon = date('Y-m-d H:i:s');
		
			$userManager->update($r_user);
			
			$user['level'] = $r_user->level;
			
			$auth = new Auth();
			$auth->setUser($user);
			//session_start();
			
			//$_SESSION['user'] = $user;
			
			redirect('http://www.ifrocolorado.com.br');
		}	
	}
	
	public function logout()
	{
		$auth = new Auth();
		$auth->clear();
		
		redirect('http://www.ifrocolorado.com.br');
	}
}
?>