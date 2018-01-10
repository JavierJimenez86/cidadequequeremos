<?php
/*
input_int
input_date
input_decimal
input_string
input_string_lower
input_string_upper

filter_num

format_date
format_datetime
format_time
format_decimal
format_zip_code
format_phone
format_person_id
*/

/* Request */
function get($field_name, $default_value = NULL)
{
	return isset($_GET[$field_name]) ? $_GET[$field_name] : $default_value;	
}

function post($field_name, $default_value = NULL)
{
	return isset($_POST[$field_name]) ? $_POST[$field_name] : $default_value;	
}

function redirect($url)
{
	header('Location:' . $url);
	exit;
}

/* Date and time */
function current_datetime()
{
	return date('Y-m-d H:i:s');
}

function current_date()
{
	return date('Y-m-d');
}

function current_time()
{
	return date('H:i:s');
}

function current_year()
{
	return date('Y');
}

function current_month()
{
	return date('m');	
}

/* Input */
function input_date($input)
{
	return $input ? implode('-', array_reverse(explode('/', $input))) : NULL;
}

function input_int($input)
{
	return (int) $input;	
}

function input_decimal($number)
{
	$number = explode('.', str_replace(',', '.', $number));
	$number_decimal = array_pop($number);
	
	$formatted_number = sizeof($number) == 0 ? $number_decimal : implode('', $number);
	$formatted_decimal = sizeof($number) == 0 ? 0 : $number_decimal;
	return number_format($formatted_number . '.' . $formatted_decimal, 2, '.', '');
}

function input_string($input)
{
	return empty($input) ? NULL : ltrim(rtrim($input));		
}

function input_string_lower($input)
{
	return input_string(mb_strtolower($input));	
}

function input_string_upper($input)
{
	return input_string(mb_strtoupper($input));	
}

/* Format */
function format_date($input)
{
	return strpos($input, '-') !== false ? strftime('%d/%m/%Y', strtotime($input)) : $input;	
}

function format_date_time($input)
{
	return strpos($input, '-') !== false ? strftime('%d/%m/%Y %H:%M:%S', strtotime($input)) : $input;	
}

function format_time($input)
{
	return $input ? strftime('%H:%M:%S', strtotime($input)) : $input;	
}

function format_person_id($input)
{
	return str_replace(array('$1', '$2', '$3', '$4'), sscanf($input, '%3s%3s%3s%2s'), '$1.$2.$3-$4');
}

function format_zip_code($input)
{
	return str_replace(array('$1', '$2'), sscanf($input, '%5s%3s'), '$1-$2');
}

function format_phone($input)
{
	$result = strlen($input) == 11 ? sscanf($input, '%2s%5s%4s') : sscanf($input, '%2s%4s%4s');
	return str_replace(array('$1', '$2', '$3'), $result, '($1) $2-$3');
}

function format_decimal($input)
{
	return number_format($input, 2, ',', NULL);	
}

function format_float($input)
{
	return number_format($input, 2, '.', NULL);
}

/* filter */
function filter_num($input)
{
	return preg_replace('/[^0-9]/', '', $input);
}

function nl2p($text)
{
	return '<p>' . str_replace("\n", '</p><p>', $text) . '</p>';	
}

/* operations with date */
function date_sum($date, $days = 0, $months = 0, $years = 0)
{
	list($year, $month, $day) = explode('-', $date);
	return date('Y-m-d', mktime(0, 0, 0, $month + $months, $day + $days, $year + $years));
}

function date_diff2($start, $end)
{
	return round((strtotime($start) - strtotime($end)) / 86400); //dias
}

function age($birthdate)
{
	return floor(((((time() - strtotime($birthdate)) / 60) / 60) / 24) / 365.25);
}

function welcome_message()
{
	$current_hour = (int) date('H');
	$message = $current_hour >= 5 ? 'Bom dia' : 'Boa noite';
	$message = $current_hour >= 12 ? 'Boa tarde' : $message;
	$message = $current_hour >= 19 ? 'Boa noite' : $message;	
	return $message;
}

/* headers */
function json_header()
{
	$gmtDate = gmdate("D, d M Y H:i:s"); 
	
	header("Expires: {$gmtDate} GMT"); 
	header("Last-Modified: {$gmtDate} GMT"); 
	header("Cache-Control: no-cache, must-revalidate"); 
	header("Pragma: no-cache"); 
	header("Content-Type: text/json; charset=utf-8");
}

function html_header()
{
	$gmtDate = gmdate("D, d M Y H:i:s"); 
	
	header("Expires: {$gmtDate} GMT"); 
	header("Last-Modified: {$gmtDate} GMT"); 
	header("Cache-Control: no-cache, must-revalidate"); 
	header("Pragma: no-cache"); 
	header("Content-Type: text/html; charset=utf-8");
}

//check
function http_status_code($code)
{
	switch ($code)
	{
		case 400:
			header("HTTP/1.1 400 Bad Request");
		break;
		case 401:
			header("HTTP/1.1 401 Unauthorized");
		break;	
		case 404:
			header("HTTP/1.1 404 Not Found");
		break;	
	}	
}

/* images */
function image_resize_jpg($src_file, $dst_folder, $dst_name, $max_w, $max_h, $dst_quality = 97)
{
	$info = getimagesize($src_file);

	$src = imagecreatefromjpeg($src_file); 
	$src_w = imagesx($src);
	$src_h = imagesy($src); 
	
	$src_aspect = $src_w >= $src_h ? $src_w / $src_h : $src_h / $src_w;
	
	if ($max_h == 0)
	{
		$max_h = $max_w; 		
	}
	
	if ($dst_quality == 0)
	{
		$dst_quality = 97; 		
	}

	$dst_w = $max_w > $src_w ? $src_w : $max_w;
	$dst_h = $src_w >= $src_h ? ceil($dst_w / $src_aspect) : ceil($dst_w * $src_aspect); 

	if ($dst_h > $max_h)
	{
		$dst_h = $max_h;
		$dst_w = ceil($dst_h / $src_aspect); 		
	}

	$dst = imagecreatetruecolor($dst_w, $dst_h); 

	imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h); 

	imagejpeg($dst, $dst_folder . $dst_name, $dst_quality); 	
	imagedestroy($dst);
	imagedestroy($src);
}

function image_resize_png($src_file, $dst_folder, $dst_name, $max_w, $max_h, $dst_quality = 97)
{
	$info = getimagesize($src_file);
	
	$src = imagecreatefrompng($src_file);
	$src_w = imagesx($src);
	$src_h = imagesy($src); 
	
	$src_aspect = $src_w >= $src_h ? $src_w / $src_h : $src_h / $src_w;
	
	if ($max_h == 0)
	{
		$max_h = $max_w; 		
	}
	
	if ($dst_quality == 0)
	{
		$dst_quality = 97; 		
	}

	$dst_w = $max_w > $src_w ? $src_w : $max_w;
	$dst_h = $src_w >= $src_h ? ceil($dst_w / $src_aspect) : ceil($dst_w * $src_aspect); 

	if ($dst_h > $max_h)
	{
		$dst_h = $max_h;
		$dst_w = ceil($dst_h / $src_aspect); 		
	}

	$dst = imagecreatetruecolor($dst_w, $dst_h); 
	imagealphablending($dst, false);
	
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h); 

	imagesavealpha($dst, true);
	imagepng($dst, $dst_folder . $dst_name); 		
	imagedestroy($dst);
	imagedestroy($src);
}

function thumbnail($src_file, $dst_folder, $dst_name, $thumb_w, $thumb_h, $dst_quality = 97)
{
	$src = imagecreatefromjpeg($src_file); 
	$src_w = imagesx($src);
	$src_h = imagesy($src); 	
	
	$src_aspect = $src_w / $src_h;
	
	if ($thumb_h == 0)
	{
		$thumb_h = $thumb_w; 		
	}
	
	if ($dst_quality == 0)
	{
		$dst_quality = 97; 		
	}

	$dst_w = $thumb_w > $src_w ? $src_w : $thumb_w;
	$dst_h = ceil($dst_w / $src_aspect);                 

	if ($dst_h < $thumb_h)
	{
		$dst_h = $thumb_h;
		$dst_w = ceil($dst_h * $src_aspect); 		
	}
	
	$dst_x = ($thumb_w - $dst_w) / 2;
	$dst_y = ($thumb_h - $dst_h) / 2;

	$dst = imagecreatetruecolor($thumb_w, $thumb_h); 
	imagecopyresampled($dst, $src, $dst_x , $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h); 

	imagejpeg($dst, $dst_folder . $dst_name, $dst_quality); 
	
	imagedestroy($dst);
	imagedestroy($src);
}
?>