<?php
require 'inc.includes.php';

html_header();

try {

	$file = $_FILES['file'];
	
	sleep(2);
	
	if (!is_array($file) || $file['size'] == 0) 
	{
		throw new Exception('Selecione uma imagem');	
	}
	
	if (mb_substr_count('image/jpeg,image/pjpe', $file['type']) == 0) //image/x-png,image/png
	{
		throw new Exception('Somente arquivos .jpg são aceitos');
	}

	$file_name = date('YmdHis') . '-' . FileUtil::urlName($file['name']);

	switch ($file['type'])
	{
		case 'image/png':
			image_resize_png($file['tmp_name'], FILES_PATH . '/temp/', $file_name, 1000, 1000, 94); 
			image_resize_png($file['tmp_name'], FILES_PATH . '/temp/thumbnail/', $file_name, 200, 200, 94);
		break;
		default:
			image_resize_jpg($file['tmp_name'], FILES_PATH . '/temp/', $file_name, 1000, 1000, 94);
			image_resize_jpg($file['tmp_name'], FILES_PATH . '/temp/thumbnail/', $file_name, 200, 200, 94);	
	}
	
	print 'file:' . $file_name;
	exit;

} catch (Exception $e) 
{
	print $e->getMessage();
}
?>