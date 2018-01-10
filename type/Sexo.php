<?php
class Sexo {
	
	const MASCULINO = 'M';
	const FEMININO = 'F';
	
	private static $list = array(
		'M' => 'Masculino',
		'F' => 'Feminino'
	);
	
	public static function getList()
	{
		return self::$list;
	}

	public static function labelOf($index)
	{
		return isset(self::$list[$index]) ? self::$list[$index] : NULL;
	}
}
?>