<?php
$securityUtil = new SecurityUtil();
/*
$profile = array(
	'profile_name' => 'Hedi Carlos Minin',
	'profile_picture' => 'https://lh3.googleusercontent.com/-e_dtNaxE-vw/AAAAAAAAAAI/AAAAAAAAB3E/nQMz1YNPkBs/photo.jpg?sz=111'
);

$securityUtil->registerUser($profile);
*/
if ($securityUtil->isUserRegistered())
{
	$current_user = $securityUtil->getCurrentUser();
	
	$tpl->block('logged', $current_user);
}
else
{
	$tpl->block('login');	
}
?>