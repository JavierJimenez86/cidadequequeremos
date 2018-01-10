<?php
require '../br/com/ifrocolorado/settings.php';
require '../br/com/ifrocolorado/util/functions.php';
require '../br/com/ifrocolorado/class/Auth.php';

require '../br/com/ifrocolorado/api/GoogleAuth.php';

require 'src/Google_Client.php';
require 'src/contrib/Google_Oauth2Service.php';

$googleAuth = new GoogleAuth();
$googleAuth->logout();
?>