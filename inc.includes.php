<?php
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/cidadequequeremos');
define('FILES_PATH', $_SERVER['DOCUMENT_ROOT'] . '/cidadequequeremos/files');

require ROOT_PATH . '/inc.settings.php';
require ROOT_PATH . '/include/Template.php';
require ROOT_PATH . '/include/Form.php';
require ROOT_PATH . '/include/functions.php';
require ROOT_PATH . '/include/SecurityUtil.php';
require ROOT_PATH . '/include/FileUtil.php';
require ROOT_PATH . '/include/GenericPDO.php';
require ROOT_PATH . '/include/PDODefault.php';
?>