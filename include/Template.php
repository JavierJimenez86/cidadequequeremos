<?php
/**
 * Classe responsável por construir a camada de interface com o usuário (xhtml)
 * Para incluir arquivos no template uilize: <!-- include:{file_name} -->
 * Para blocos dinâmicos utilize: <!-- bb_{block_name} -->{content}<!-- eb_{block_name} -->
 *
 * @date     26/07/2014
 * @version  2.0.0
 * @author   Hedi Minin
 * @charset  utf-8
 */ 
class Template {

	private $tpl = NULL; 
	private $tplExt = '.html';
	private $tplDir = 'tpl/'; //tpl/
	private $vars = array(); 
	private $blocks = array();
	private $childBlocks = array();
	private $registeredFormats = array();
	
	private static $instance;

	private function __construct($template_path = NULL)
	{
		defined('ROOT_PATH') or die('Template: ROOT_PATH not defined');	 
		/*
		$this->tpl = $this->readTemplateFile($template_path);
		
		$this->identifyIncludes();
		$this->identifyFormats();
		$this->identifyBlocks();
		*/
    }
	
    public static function instance()
    {
        if (!isset(self::$instance)) 
		{
            self::$instance = new Template();
        }

        return self::$instance;
    }
	
	public function load($template_path = NULL)
	{
		$this->tpl = $this->readTemplateFile($template_path);
		
		$this->identifyIncludes();
		$this->identifyFormats();
		$this->identifyBlocks();	
	}

	private function readTemplateFile($template_path)
	{
		$template_file = $this->parseTemplatePath($template_path); 
		
		file_exists($template_file) or die('Template: Template file &lt;' . $template_file . '&gt; not found');	
		
		return file_get_contents($template_file); 
	}

	private function parseTemplatePath($template_path)
	{	
		if (!$template_path)
		{
			$template_path = $this->tplDir;
			$current_path = explode('/', $_SERVER['SCRIPT_FILENAME']);
			$template_path .= str_replace('.php', $this->tplExt, array_pop($current_path));
			return $template_path;
		}
		
		if (strpos($template_path, 'root') !== false)
		{
			$template_path = str_replace('root', ROOT_PATH, $template_path);
			$template_path .= $this->tplExt;
			return $template_path;	
		}

		return $this->tplDir . $template_path . $this->tplExt;	
	}

	private function identifyFormats()
	{
		preg_match_all('/{([a-z_\-]+):([a-z_\-]+)}/smi', $this->tpl, $matches);
		
		$size = sizeof($matches[0]);
		
		for ($i = 0; $i < $size; $i++)
		{
			$match = $matches[0][$i];
			$label = $matches[1][$i];
			
			$this->registeredFormats[$label] = $matches[2][$i]; //$matches[2] = format 
			
			$this->tpl = str_replace($match, '{' . $label . '}', $this->tpl);	
		}
	}

	private function identifyIncludes()
	{
		preg_match_all('/<!-- include:([a-z_.:\-\/]+) -->/smi', $this->tpl, $includes);

		foreach ($includes[1] as $include_path)
		{
			$this->tpl = str_replace('<!-- include:' . $include_path . ' -->', $this->readTemplateFile($include_path), $this->tpl);
		}
	}
	
	private function identifyBlocks()
	{
		/* extract blocks names */
		preg_match_all('/<!-- bb_([a-z_]+) -->/smi', $this->tpl, $block_names);
				
		/* extract blocks content */
		foreach ($block_names[1] as $block_name)
		{
			$block_search = '/<!-- bb_' . $block_name . ' -->(.*)<!-- eb_' . $block_name . ' -->/smi';
			preg_match($block_search, $this->tpl, $block);
			
			isset($block[1]) or die('Template: Block &lt;' . $block_name . '&gt; badly formed');
			
			$this->blocks[$block_name] = $block[1];
		}

		foreach ($block_names[1] as $block_name)
		{
			/* replaces blocks for your reference */
			$block_search = '/<!-- bb_' . $block_name . ' -->(.*)<!-- eb_' . $block_name . ' -->/smi';
			$this->tpl = preg_replace($block_search, '<!-- block_' . $block_name . ' -->', $this->tpl);	
			
			/* searches children blocks to replace for your reference */
			if (mb_strpos($this->blocks[$block_name], '<!-- eb_') > 0)
			{		
				preg_match_all('/<!-- bb_([a-z_]+) -->/smi', $this->blocks[$block_name], $child_names);

				foreach ($child_names[1] as $child_name)
				{					
					$this->childBlocks[$block_name][] = $child_name;
					$block_search = '/<!-- bb_' . $child_name . ' -->(.*)<!-- eb_' . $child_name . ' -->/smi';
					$this->blocks[$block_name] = preg_replace($block_search, '<!-- block_' . $child_name . ' -->', $this->blocks[$block_name]);
				}
			}
		}
	}
	
	public function blockEach($block_name, $blocks = array())
	{	
		foreach ($blocks as $block)
		{
			$this->block($block_name, $block);	
		}
	}

	public function block($block_name, $block_vars = array())
	{	
		isset($this->blocks[$block_name]) or die('Template: Block &lt;' . $block_name . '&gt; not found');	
		
		$block = $this->blocks[$block_name];	
		
		/* assign block vars */
		$block_vars = is_object($block_vars) ? get_object_vars($block_vars) : $block_vars;
		
		foreach ($block_vars as $label => $value)
		{
			/* parse format */
			if (isset($this->registeredFormats[$label]))
			{
				$value = $this->format($value, $this->registeredFormats[$label]);	
			}
			
			$block = str_replace('{' . $label . '}', $value, $block);
		}
		
		/* clean children blocks */
		if (isset($this->childBlocks[$block_name]))
		{
			foreach ($this->childBlocks[$block_name] as $child_name)
			{
				$this->tpl = str_replace('<!-- block_' . $child_name . ' -->', NULL, $this->tpl);
			}
		}

		$this->tpl = str_replace('<!-- block_' . $block_name . ' -->', $block . '<!-- block_' . $block_name . ' -->', $this->tpl);
	}
	
	public function show()
	{
		/* parse format */
		if (sizeof($this->registeredFormats) > 0)
		{
			$labels = array_keys($this->registeredFormats);
			foreach ($labels as $label)
			{
				if (isset($this->vars[$label]))
				{
					$this->vars[$label] = $this->format($this->vars[$label], $this->registeredFormats[$label]);	
				}
			}
		}
		
		/* parse template */
		$labels = array_keys($this->vars);	
		foreach ($labels as $label)
		{
			if (!is_array($this->vars[$label]))
			{
				$this->tpl = str_replace('{' . $label . '}', $this->vars[$label], $this->tpl);
			}
		}
		
		echo $this->tpl;
	}
	
	public function set($mixed, $value = NULL)
	{
		if (is_string($mixed))
		{
			$this->vars[$mixed] = $value;	
		}
		else if (is_array($mixed))
		{
			$this->vars = array_merge($this->vars, $mixed);
		}
		else if (is_object($mixed))
		{
			$this->vars = array_merge($this->vars, get_object_vars($mixed));
		}
	}
	
	public function setCount($label, $count, $list = array())
	{
		if ($count == 0)
		{
			$this->vars[$label] = $list[0];		
		}
		
		if ($count == 1)
		{
			$this->vars[$label] = $count . ' ' . $list[1];		
		}
		
		if ($count > 1)
		{
			$this->vars[$label] = $count . ' ' . $list[2];		
		}
	}

	private function format($input, $format)
	{	
		switch ($format)
		{
			case 'date':
				return format_date($input);	
				
			case 'datetime':
				return format_date_time($input);
				
			case 'phone':
				return format_phone($input);	
				
			case 'zipcode':
				return format_zip_code($input);
					
			case 'decimal':
				return format_decimal($input);	
				
			case 'float':
				return format_float($input);	
				
			case 'personid':
				return format_person_id($input);
				
			default:
				return $input;
		}
	}
	
	/* --- Form methods --- */
	private function getRequestVar($field_name)
	{
		if (isset($this->vars[$field_name]))
		{
			return $this->vars[$field_name];
		}

		if (isset($_POST[$field_name]))
		{
			return $_POST[$field_name];
		}
		
		if (isset($_GET[$field_name]))
		{
			return $_GET[$field_name];
		}
		
		return NULL;
	}

	public function checkbox($field_name, $field_value = 1)
	{
		$this->vars[$field_name] = $this->getRequestVar($field_name) == $field_value ? 'checked="checked"' : NULL;
	}

	public function select($field_name, $field_list, $options = array())
	{
		$options = array_merge(array(
			'required' => true,
			'empty' => NULL,
			'key' => NULL,
			'label' => NULL,
			'header-key' => 0,
			'header-label' => NULL,
			'tpl' => NULL
		), $options); 

		$request_value = $this->getRequestVar($field_name);
		
		$select = NULL;
		
		/* option header */
		$header_label = $options['required'] ? 'Selecione' : 'Não informado';
		
		if ($options['header-label'])
		{
			$header_label = $options['header-label'];
		}
		
		if ($options['empty'])
		{
			$header_label = sizeof($field_list) == 0 ? $options['empty'] : $header_label;
		} 
		//else if ($options['header-label'])
		//{
		//	$header_label = $options['header-label'];
		//}
		
		if ($options['header-label'] === false)
		{
			$header_label = NULL;	
		}

		if ($header_label)
		{
			$selected = $options['header-key'] == $request_value ? ' selected="selected"' : NULL;
			$select .= '<option value="' . $options['header-key'] . '"' . $selected . '>' . $header_label . '</option>';		
		}

		/* parse options */
		if ($options['key'])
		{
			if (mb_strpos($options['label'], ',') === false)
			{
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$selected = $list[$options['key']] == $request_value ? ' selected="selected"' : NULL;
					$select .= '<option value="' . $list[$options['key']] . '"' . $selected . '>' . $list[$options['label']] . '</option>';
				}
			}
			else
			{
				$label = explode(',', $options['label']);
				$label_flip = array_flip($label);
				$template = isset($options['tpl']) ? str_replace(' ', '&nbsp;', $options['tpl']) : $options['label'];
				
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$selected = $list[$options['key']] == $request_value ? ' selected="selected"' : NULL;
					$select .= '<option value="' . $list[$options['key']] . '"' . $selected . '>' . str_replace($label, array_intersect_key($list, $label_flip), $template) . '</option>';
				}		
			}
		}
		else
		{
			foreach ($field_list as $list_value => $list_label)
			{
				$selected = $list_value == $request_value ? ' selected="selected"' : NULL;
				$select .= '<option value="' . $list_value . '"' . $selected . '>' . $list_label . '</option>';
			}
		}

		$this->tpl = str_replace('{sl_' . $field_name . '}', $select, $this->tpl);		
	}
	
	public function radioList($field_name, $field_list, $options = array())
	{
		$options = array_merge(array(
			'key' => NULL,
			'label' => NULL,
			'tpl' => NULL,
			'empty' => NULL,
		), $options); 
		
		$request_value = $this->getRequestVar($field_name);
		
		$radio_list = NULL;
		
		if (sizeof($field_list) == 0)
		{
			$radio_list = $options['empty'];	
		}

		if ($options['key'])
		{
			if (mb_strpos($options['label'], ',') === false)
			{
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$checked = $list[$options['key']] == $request_value ? ' checked="checked"' : NULL;
					$radio_list .= '<label><input type="radio" name="' . $field_name . '" value="' . $list[$options['key']] . '"' . $checked . '/>' . $list[$options['label']] . '</label>';
				}
			}
			else
			{
				$label = explode(',', $options['label']);
				$label_flip = array_flip($label);
				$template = $options['tpl'] ? str_replace(' ', '&nbsp;', $options['tpl']) : $options['label'];
				
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$checked = $list[$options['key']] == $request_value ? ' checked="checked"' : NULL;
					$radio_list .= '<label><input type="radio" name="' . $field_name . '" value="' . $list[$options['key']] . '"' . $checked . '/>' . str_replace($label, array_intersect_key($list, $label_flip), $template) . '</label>';
				}
			}
		}
		else
		{
			foreach ($field_list as $list_value => $list_label)
			{
				$checked = $list_value == $request_value ? ' checked="checked"' : NULL;
				$radio_list .= '<label><input type="radio" name="' . $field_name . '" value="' . $list_value . '"' . $checked . '/>' . $list_label . '</label>';
			}
		}
		
		$this->tpl = str_replace('{rl_' . $field_name . '}', $radio_list, $this->tpl);
	}

	public function checkboxList($field_name, $field_list, $request_list = array(), $options = array())
	{
		$options = array_merge(array(
			'key' => NULL,
			'label' => NULL,
			'tpl' => NULL,
			'empty' => NULL
		), $options); 
		
		//$request_value = $this->getRequestVar($field_name);
		
		//$request_value = is_array($request_value) ? $request_value : array();
		
		$checkbox_list = NULL;
		
		if (sizeof($field_list) == 0)
		{
			$checkbox_list = $options['empty'];	
		}

		if ($options['key'])
		{
			if (mb_strpos($options['label'], ',') === false)
			{
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$checked = array_search($list[$options['key']], $request_list) === false ? NULL : 'checked="checked"';
					$checkbox_list .= '<label><input type="checkbox" name="' . $field_name . '[]" value="' . $list[$options['key']] . '"' . $checked . '/>' . $list[$options['label']] . '</label>';
				}
			}
			else
			{
				$label = explode(',', $options['label']);
				$label_flip = array_flip($label);
				$template = $options['tpl'] ? str_replace(' ', '&nbsp;', $options['tpl']) : $options['label'];
				
				foreach ($field_list as $list)
				{
					$list = is_object($list) ? get_object_vars($list) : $list;
					$checked = array_search($list[$options['key']], $request_list) === false ? NULL : 'checked="checked"';
					$checkbox_list .= '<label><input type="checkbox" name="' . $field_name . '[]" value="' . $list[$options['key']] . '"' . $checked . '/>' . str_replace($label, array_intersect_key($list, $label_flip), $template) . '</label>';
				}
			}
		}
		else
		{
			foreach ($field_list as $list_value => $list_label)
			{
				$checked = array_search($list_value, $request_list) === false ? NULL : 'checked="checked"';
				$checkbox_list .= '<label><input type="checkbox" name="' . $field_name . '[]" value="' . $list_value . '"' . $checked . '/>' . $list_label . '</label>';
			}
		}
		
		$this->tpl = str_replace('{cl_' . $field_name . '}', $checkbox_list, $this->tpl);
	}
}
?>