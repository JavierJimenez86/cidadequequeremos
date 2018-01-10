<?php
class SecurityUtil {
	
	//private $usersessid = 'user-id';
	private $seckey = '3GR9gHi8=tymm^]L/59[AvdtZRA*&';
	
	function __construct()
	{
		$this->startSession();	
	}
	
	public function encrypt($text)
	{  
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
		$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->seckey, $text, MCRYPT_MODE_ECB, $iv); 
		return base64_encode($crypttext); 
	}
	
	public function decrypt($crypttext)
	{ 
		$crypttext = base64_decode($crypttext);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB); 
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND); 
		$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->seckey, $crypttext, MCRYPT_MODE_ECB, $iv); 
		return trim($decrypttext); 
	}
	
	/* session */
	public function startSession()
	{
		if (!session_id())
		{
			session_start();
			session_regenerate_id();
		}
	}
	
	public function registerSession($session_name, $session_value)
	{
		$_SESSION[$session_name] = $session_value;
	}
	
	public function unregisterSession($session_name)
	{
		unset($_SESSION[$session_name]);
	}
	
	public function isSessionRegistered($session_name)
	{
		return isset($_SESSION[$session_name]);	
	}
	
	public function getSession($session_name)
	{
		return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : NULL;	
	}
	
	public function destroySession()
	{
		session_destroy();
	}
	
	/* user */
	public function getCurrentUser()
	{
		return $this->getSession('user-id');	
	}
	
	public function registerUser($user)
	{
		$this->registerSession('user-id', $user);
		$this->registerSession('user-agent', sha1($_SERVER['HTTP_USER_AGENT']));	
	}	
	
	public function isUserRegistered()
	{
		if (!$this->isSessionRegistered('user-id') || $this->getSession('user-agent') != sha1($_SERVER['HTTP_USER_AGENT']))
		{
			return false;
		}	
		
		return true;
	}
}
?>