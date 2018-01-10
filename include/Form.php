<?php
/**
 * Classe responsável por validar dados do formulário utilizando xml
 *
 * @date     04/03/2014
 * @version  2.0.3
 * @author   Hedi Minin
 * @charset  utf-8
 */ 
class Form {
	
	private $formFields = array();
	private $errors = array();
	private $warnings = array();
	private $messages = array();
	private $info = array();
	private $xmlDir = 'validate/'; 
	private $fieldRequiredMessage = 'O campo [field] deve ser informado';
	private $fieldInvalidMessage = 'O valor informado no campo [field] é inválido';
	private $scriptFileName = NULL;
	private $formId = NULL;
	
	function __construct($form_id = NULL)
	{ 
		if (!session_id())
		{
			session_start();
		}
		
		$script_filename = explode('/', $_SERVER['SCRIPT_FILENAME']);
		$this->scriptFileName = array_pop($script_filename);
		$this->formId = is_null($form_id) ? str_replace('.php', '', $this->scriptFileName) : $form_id;
	}
	
	public function token()
	{
		$token = md5(microtime(true));
			
		$_SESSION[$this->formId] = $token;
		
		return '<input type="hidden" name="ftoken" value="' . $token . '" />';
	}
	
	public function validate($xml_file = NULL)
	{
		/* check token */
		if (isset($_SESSION[$this->formId]))
		{
			if (!isset($_POST['ftoken']) || $_SESSION[$this->formId] != $_POST['ftoken'])
			{
				$this->addError('Invalid token');
				return false;			
			}	
			
			unset($_SESSION[$this->formId]);
		}
		
		/* load xml file */
		if (is_null($xml_file))
		{
			$xml_file = $this->scriptFileName;
		}
	
		$xml_file = $this->xmlDir . str_replace('.php', '.xml', $xml_file);
		
		if (!file_exists($xml_file))
		{
			$this->addError('Validate: XML file not found (' . $xml_file . ')');
			return false;	
		}

		$xml_obj = simplexml_load_string(file_get_contents($xml_file), NULL, LIBXML_NOERROR | LIBXML_NOWARNING);
		
		if (!$xml_obj)
		{
			$this->addError('Validate: XML parse failed');
			return false;	
		}
		
		foreach ($xml_obj->children() as $field)
		{
			$attributes = $field->attributes();
			$field_name = (string) $attributes['name'];			
			
			/* validate post fields */
			if($field->getName() == 'field')
			{
				$field_value = $this->getPostField($field_name);
	
				if (is_array($field_value))
				{
					foreach ($field_value as $f_value)
					{
						$this->validatePost($field, $f_value);	
					}		
				}
				else
				{
					$this->validatePost($field, $field_value);		
				}
			}

			/* validate file fields */
			if($field->getName() == 'file')
			{
				$field_value = $this->getFileField($field_name);	
				$this->validateFile($field, $field_value);		
			}
		}
		
		return $this->hasErrors() ? false : true;
	}
	
	private function validateFile($field, $field_value)
	{		
		$attributes = $field->attributes();
		$field_name = (string) $attributes['name'];
		$field_label = (string) $attributes['label'];
		$field_required = isset($attributes['required']) ? true : false;
		$field_type = isset($attributes['type']) ? (string) $attributes['type'] : NULL;
		$field_max_size = isset($attributes['max-size']) ? (int) $attributes['max-size'] : NULL;
		$field_message = isset($attributes['message']) ? (string) $attributes['message'] : NULL;
	
		$field_message = $field_message ? $field_message : $this->getFieldMessage($field_type);
		$error_message = str_replace('[field]', $field_label, $field_message);
	
		if ($field_required)
		{
			if (!is_array($field_value) || $field_value['size'] == 0) 
			{
				$this->errors[] = str_replace('[field]', $field_label, $this->fieldRequiredMessage);
				return false;
			}	
		}
		
		if (!is_array($field_value) || $field_value['size'] == 0) 
		{
			return true;	
		}
		
		if ($field_max_size)
		{
			if (($field_value['size'] / 1024) > $field_max_size)
			{
				$this->errors[] = str_replace(array('[field]', '[max-size]'), array($field_label, $field_max_size), 'O arquivo informado no campo [field] é muito grande (tamanho máximo permitido: [max-size]KB)');	
			}		
		}

		if ($field_type)
		{
			switch ($field_type)
			{
				case 'image':
	
					if (mb_substr_count('image/jpeg,image/pjpe,image/x-png,image/png', $field_value['type']) == 0)
					{
						$this->errors[] = $error_message;	
					}	
					
				break;	
				case 'png':
	
					if (mb_substr_count('image/x-png,image/png', $field_value['type']) == 0)
					{
						$this->errors[] = $error_message;	
					}	
					
				break;	
				case 'jpg':
	
					if (mb_substr_count('image/jpeg,image/pjpeg', $field_value['type']) == 0)
					{
						$this->errors[] = $error_message;	
					}	
					
				break;
				default:
					if (mb_substr_count($field_type, $field_value['type']) == 0) 
					{
						$this->errors[] = $error_message;	
					}
			}	
		}
	}
	
	private function validatePost($field, $field_value)
	{
		$attributes = $field->attributes();
		$field_name = (string) $attributes['name'];
		$field_label = (string) $attributes['label'];
		$field_required = isset($attributes['required']) ? true : false;
		$field_type = isset($attributes['type']) ? (string) $attributes['type'] : NULL;
		$field_message = isset($attributes['message']) ? (string) $attributes['message'] : NULL;
		
		if ($field_required)
		{
			$check_value = trim($field_value);
			if (empty($check_value)) 
			{
				$this->errors[] = str_replace('[field]', $field_label, $this->fieldRequiredMessage);
				return false;
			}	
		}
		
		if (!$field_value || !$field_type)
		{	
			return true;
		}	
		
		$field_message = $field_message ? $field_message : $this->getFieldMessage($field_type);
		$error_message = str_replace('[field]', $field_label, $field_message);
			
		switch ($field_type)
		{
			case 'compare':
					
				$field_compare_value = $this->getPostField((string) $attributes['field']);
	
				if ($field_value != $field_compare_value) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'length':
				
				$field_length = mb_strlen($field_value);

				$min = isset($attributes['min']) ? (int) $attributes['min'] : $field_length;
				$max = isset($attributes['max'])? (int) $attributes['max'] : $field_length;
				
				if ($field_length < $min || $field_length > $max) 
				{
					$this->errors[] = str_replace(array('{min}', '{max}'), array($min, $max), $error_message);
				}
			
			break;
			case 'regex':
	
				if (!empty($field_value) && !preg_match((string) $attributes['exp'], $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'email':
	
				if (!preg_match('/^[a-zA-Z0-9\_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'range':
	
				$min = isset($attributes['min']) ? $attributes['min'] : $field_value;
				$max = isset($attributes['max'])? $attributes['max'] : $field_value;
	
				if ($field_value < $min || $field_value > $max) 
				{
					$this->errors[] = str_replace(array('{min}', '{max}'), array($min, $max),  $error_message);
				}
	
			break;
			case 'numeric':
	
				if (!is_numeric($field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'decimal':
		
				if (!preg_match('/^[0-9.,]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'integer':
	
				if (!preg_match('/^[0-9]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
				
			break;
			case 'date':
	
				if (!preg_match('/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/', $field_value))  ///(\d{4})-(\d{2})-(\d{2})/
				{
					$this->errors[] = $error_message;
					break;	
				} 
				
				list($day, $month, $year) = explode('/', $field_value);
				
				if(!checkdate((int) $month, (int) $day, (int) $year))
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'time':
	
				if (!preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $field_value)) 
				{
					$this->errors[] = $error_message;
				}
				
			break;
			case 'folder':
	
				if (!preg_match('/^[a-zA-Z0-9_\-]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;
				}
				
			break;
			case 'url':
	
				if (!preg_match('/^[a-zA-Z0-9\-]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
				
			break;
			case 'alpha-pt':
	
				if (!preg_match('/^[a-zA-ZãÃáÁàÀêÊéÉèÈíÍìÌôÔõÕóÓòÒúÚùÙûÛçÇ]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'alpha':
	
				if (!preg_match('/^[a-zA-Z]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;
			case 'alphanum':
	
				if (!preg_match('/^[a-zA-Z0-9]*$/', $field_value)) 
				{
					$this->errors[] = $error_message;		
				}
	
			break;	
			case 'phone':
	
				if (!preg_match('/^\([0-9]{2}\) ([0-9]{8,9})*$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;	
			case 'password':
	
				if (!preg_match('/^[a-zA-Z0-9!\*\$#@&]{7,13}$/', $field_value)) 
				{
					$this->errors[] = $error_message;	
				}
	
			break;				
			case 'cpf':
			
				$field_value = preg_replace('/[^0-9]/', '', $field_value);	
	
				if (!preg_match('/^[0-9]{11}$/', $field_value)) 
				{
					$this->errors[] = $error_message;
					break;
				}
				
				if (preg_match('/^([0]{11})|([1]{11})|([2]{11})|([3]{11})|([4]{11})|([5]{11})|([6]{11})|([7]{11})|([8]{11})|([9]{11})$/', $field_value)) 
				{
					$this->errors[] = $error_message;
					break;
				}
	
				for ($t = 9; $t < 11; $t++)
				{
					for ($d = 0, $c = 0; $c < $t; $c++)
					{
						$d += $field_value{$c} * (($t + 1) - $c);
					}
					$d = ((10 * $d) % 11) % 10;
					if ($field_value{$c} != $d)
					{
						$this->errors[] = $error_message;
					}
				}
	
			break;
		}
	}
	
	private function getFieldMessage($field_type)
	{
		$field_message = array(
			'integer' => 'Somente números inteiros são aceitos no campo [field]',
			'length' => 'O campo [field] deve ter de {min} a {max} caracteres',
			'range' => 'O campo [field] deve estar dentro do intervalo {min} e {max}',
			'numeric' => 'Somente valores numéricos são aceitos no campo [field]',
			'decimal' => 'Somente números decimais são aceitos no campo [field]',
			'date' => 'O campo [field] deve conter uma data válida e ser informado no formato DD/MM/AAAA',
			'time' => 'O campo [field] deve conter uma hora válida e ser informado no formato HH:MM:SS',
			'url' => 'Somente letras, números e traços são aceitos no campo [field]',
			'alpha' => 'Somente letras são aceitas no campo [field]',
			'alphanum' => 'Somente letras e números são aceitos no campo [field]',
			'password' => 'Somente letras e números são aceitos no campo [field] (de 7 a 13 caracteres)',
			'phone' => 'O campo [field] deve ser informado no formato (99) 99999999',
			'image' => 'Somente arquivos jpg e png são aceitos no campo [field]',
			'png' => 'Somente arquivos png são aceitos no campo [field]',
			'jpg' => 'Somente arquivos jpg são aceitos no campo [field]'
		);
		
		return isset($field_message[$field_type]) ? $field_message[$field_type] : $this->fieldInvalidMessage;
	}
	
	private function getPostField($field_name)
	{
		return isset($_POST[$field_name]) ? $_POST[$field_name] : NULL;
	}
	
	private function getFileField($field_name)
	{
		return isset($_FILES[$field_name]) ? $_FILES[$field_name] : NULL;
	}

	public function send($field_name = 'submit')
	{
		return isset($_POST[$field_name]);
	}
	
	public function clear()
	{	
		$_POST = array_merge($_POST, $this->formFields);
	}
		
	public function initialize($fields)
	{
		if (is_object($fields))
		{
			$this->formFields = array_merge($this->formFields, get_object_vars($fields));
				
			$_POST = $_POST + get_object_vars($fields);			
		}
		
		if (is_array($fields))
		{
			$this->formFields = array_merge($this->formFields, $fields);
				
			$_POST = $_POST + $fields;
		}
	}

	public function hasErrors()
	{
		return sizeof($this->errors) == 0 ? false : true; 
	}
	
	public function hasMessages()
	{
		return sizeof($this->messages) == 0 ? false : true; 
	}
	
	public function getErrors()
	{
		return $this->errors;
	}
	
	public function getWarnings()
	{
		return $this->warnings;
	}
	
	public function getMessages()
	{
		return $this->messages;
	}
	
	public function getInfo()
	{
		return $this->info;
	}
	
	public function addError($error)
	{
		$this->errors[] = $error;
	}
	
	public function addWarning($warning)
	{
		$this->warnings[] = $warning;
	}

	public function addMessage($message)
	{
		$this->messages[] = $message;
	}
	
	public function addInfo($info)
	{
		$this->info[] = $info;
	}
	
	public function getErrorsAsHtml()
	{
		return sizeof($this->getErrors()) == 0 ? NULL : '<ul class="jform-errors" id="jform-errors"><li>' . implode('</li><li>', $this->getErrors()). '</li></ul>';	
	}
	
	public function getWarningsAsHtml()
	{
		return sizeof($this->getWarnings()) == 0 ? NULL : '<ul class="jform-warnings" id="jform-warnings"><li>' . implode('</li><li>', $this->getWarnings()). '</li></ul>';	
	}
	
	public function getMessagesAsHtml()
	{
		return sizeof($this->getMessages()) == 0 ? NULL : '<ul class="jform-messages" id="jform-messages"><li>' . implode('</li><li>', $this->getMessages()) . '</li></ul>';	
	}
	
	public function getInfoAsHtml()
	{
		return sizeof($this->getInfo()) == 0 ? NULL : '<ul class="jform-info" id="jform-info"><li>' . implode('</li><li>', $this->getInfo()) . '</li></ul>';	
	}
	
	public function getErrorsAsJson()
	{
		return  json_encode(array('errors' => $this->getErrors()));
	}
	
	public function getMessagesAsJson()
	{
		return  json_encode(array('messages' => $this->getMessages()));
	}
	
	public function jsonResult()
	{
		return json_encode(array('errors' => $this->getErrors(), 'messages' => $this->getMessages()));
	}
	
	public function htmlResult()
	{
		$result = NULL;
		/*
		if ($this->hasNotices())
		{
			$result .= '<ul class="jform-notices" id="jform-notices"><li>' . implode('</li><li>', $this->getNotices()). '</li></ul>';	
		}
		
		if ($this->hasWarnings())
		{
			$result .= '<ul class="jform-warnings" id="jform-warnings"><li>' . implode('</li><li>', $this->getWarnings()). '</li></ul>';	
		}
		
		if ($this->hasErrors())
		{
			$result .= '<ul class="jform-errors" id="jform-errors"><li>' . implode('</li><li>', $this->getErrors()). '</li></ul>';	
		}
		
		if ($this->hasMessages())
		{
			$result .= '<ul class="jform-messages" id="jform-messages"><li>' . implode('</li><li>', $this->getMessages()). '</li></ul>';	
		}
		*/
		return $result;
	}
}
?>