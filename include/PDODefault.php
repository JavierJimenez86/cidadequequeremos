<?php
class PDODefault extends PDO {
	
	private $host = '127.0.0.1';
	private $database = 'icoletiva';
	private $user = 'root';
	private $password = '123456'; //3GR9gHi8tymm
	private $persistent = false;

	public function __construct()
	{
		set_exception_handler(array(__CLASS__, 'exceptionHandler'));
		
		$options = array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
			PDO::ATTR_PERSISTENT => $this->persistent, 
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
		);
		
		parent::__construct('mysql:host=' . $this->host . ';dbname=' . $this->database, $this->user, $this->password,  $options); // . ';charset=utf8'

		restore_exception_handler();
	}

    public static function getInstance()
	{
        static $instance = NULL;

        if ($instance == NULL)
		{
	    	$instance = new PDODefault();
        }
		
        return $instance;
    }

	public static function exceptionHandler($exception)
	{
		die($exception->getMessage());
	}
}
?>