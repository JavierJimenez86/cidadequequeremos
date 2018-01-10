<?php
mb_language('Neutral'); 
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
mb_http_input('UTF-8');
mb_http_output('UTF-8');

setlocale(LC_CTYPE, 'pt_BR.utf8');			
setlocale(LC_TIME, 'pt_BR', 'ptb'); 
date_default_timezone_set('America/Manaus');

define('APP_NAME', 'Cidade que Queremos');
define('APP_TITLE', 'Cidade que Queremos - Colorado do Oeste');
define('APP_EMAIL', 'naoresponder@ifrocolorado.com.br');

define('ROOT_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos'); 
define('FILES_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos/files'); 
define('RES_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos/res');

define('ROOT_SEC_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos'); 
define('FILES_SEC_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos/files'); 
define('RES_SEC_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/cidadequequeremos/res');

/*
define('ROOT_URL', 'http://www.ifrocolorado.com.br/cidadequequeremos'); 
define('PICTURE_URL', 'http://www.ifrocolorado.com.br/cidadequequeremos/files'); 
define('RES_URL', 'http://www.ifrocolorado.com.br/cidadequequeremos/res');

define('ROOT_SEC_URL', 'https://ifrocolora.sslblindado.com/cidadequequeremos');
define('PICTURE_SEC_URL', 'https://ifrocolora.sslblindado.com/cidadequequeremos/files'); 
define('RES_SEC_URL', 'https://ifrocolora.sslblindado.com/cidadequequeremos/res'); 
*/
define('SESSION_ID', 'app-user');

define('DEFAULT_PROFILE_PICTURE', '/profile.png');
define('DEFAULT_PROFILE_PICTURE_SIZE', 111);

define('DATE_FORMAT', '%d/%m/%Y');
define('DATETIME_FORMAT', '%d/%m/%Y %H:%M:%S');
?>