<?php
require '../inc.includes.php';
require ROOT_PATH . '/dao/CityDAO.php';

json_header();
	
$state_id = input_int(post('state_id'));

$cityDAO = new CityDAO();

$cityList = $cityDAO->listByState($state_id);

print json_encode($cityList);
?>