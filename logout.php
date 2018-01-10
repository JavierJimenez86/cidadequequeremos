<?php
require 'inc.includes.php';

$securityUtil = new SecurityUtil();

$securityUtil->destroySession();

redirect(ROOT_URL);
?>