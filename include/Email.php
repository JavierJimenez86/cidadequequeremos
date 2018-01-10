<?php
/**
 * Classe responsável por enviar e-mail em formato xhtml utilizando template
 * Para incluir arquivos no template uilize: <!-- include:{file_name} -->
 * Para blocos dinâmicos utilize: <!-- begin_block_{block_name} -->{content}<!-- end_block_{block_name} -->
 *
 * @date     01/05/2013
 * @version  1.5 
 * @author   Hedi Minin
 * @charset  utf-8
 */ 
class Email {
	
	private $tpl = NULL; 
	private $tplExt = '.html';
	private $tplDir = 'tpl/email/';
	private $vars = array(); 
	private $blocks = array();
	private $childBlocks = array();

	function __construct($template_file = NULL)
	{ 
		$template_file = $this->tplDir . $template_file . $this->tplExt;
		
		file_exists($template_file) or die('Email: Template file &lt;' . $template_file . '&gt; not found');
		
		$this->tpl = file_get_contents($template_file); 

		$this->identifyIncludes();
		$this->identifyBlocks();
    }
	
	private function identifyIncludes()
	{
		preg_match_all('/<!-- include:([a-z_.:]*) -->/smi', $this->tpl, $includes);

		foreach ($includes[1] as $inc)
		{		
			$include = $this->tplDir . str_replace('.:', '/', $inc) . $this->tplExt;
			
			file_exists($include) or die('Email: Include file &lt;' . $inc . '&gt; not found');
			
			$this->tpl = str_replace('<!-- include:' . $inc . ' -->', file_get_contents($include), $this->tpl);
		}
	}
	
	private function identifyBlocks()
	{
		/* extract blocks names */
		preg_match_all('/<!-- begin_block_([a-z_]*) -->/smi', $this->tpl, $block_names);
				
		/* extract blocks content */
		foreach ($block_names[1] as $block_name)
		{
			$block_search = '/<!-- begin_block_' . $block_name . ' -->(.*)<!-- end_block_' . $block_name . ' -->/smi';
			preg_match($block_search, $this->tpl, $block);
			
			isset($block[1]) or die('Email: Block &lt;' . $block_name . '&gt; badly formed');
			
			$this->blocks[$block_name] = $block[1];
		}

		foreach ($block_names[1] as $block_name)
		{
			/* replaces blocks for your reference */
			$block_search = '/<!-- begin_block_' . $block_name . ' -->(.*)<!-- end_block_' . $block_name . ' -->/smi';
			$this->tpl = preg_replace($block_search, '<!-- block_' . $block_name . ' -->', $this->tpl);	
			
			/* searches children blocks to replace for your reference */
			if (mb_strpos($this->blocks[$block_name], '<!-- end_block_') > 0)
			{		
				preg_match_all('/<!-- begin_block_([a-z_]*) -->/smi', $this->blocks[$block_name], $child_names);

				foreach ($child_names[1] as $child_name)
				{					
					$this->childBlocks[$block_name][] = $child_name;
					$block_search = '/<!-- begin_block_' . $child_name . ' -->(.*)<!-- end_block_' . $child_name . ' -->/smi';
					$this->blocks[$block_name] = preg_replace($block_search, '<!-- block_' . $child_name . ' -->', $this->blocks[$block_name]);
				}
			}
		}
	}
	
	public function block($block_name, $block_vars = array())
	{	
		isset($this->blocks[$block_name]) or die('Email: Block &lt;' . $block_name . '&gt; not found');	
		
		$block = $this->blocks[$block_name];	
		
		/* assign block vars */
		$block_vars = is_object($block_vars) ? get_object_vars($block_vars) : $block_vars;
		
		foreach ($block_vars as $label => $value)
		{
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

	public function set($labels, $value = NULL)
	{
		if (is_array($labels))
		{
			foreach ($labels as $label => $value)
			{
				$this->vars['{' . $label . '}'] = $value; 
			}
		} 
		else 
		{
			$this->vars['{' . $labels . '}'] = $value; 
		}
	}	
	
	public function getHTML()
	{
		return $this->tpl = str_replace(array_keys($this->vars), $this->vars, $this->tpl);
	}	

	public function send($from_name, $from_email, $to, $subject)
	{
		$this->tpl = str_replace(array_keys($this->vars), $this->vars, $this->tpl);

		$header = array(
			'From: ' . $from_name . ' <' . $from_email . '>', 
			'MIME-Version: 1.0', 
			'Content-type: text/html; charset=utf-8'
		);
		
		return mail($to, $subject, $this->tpl, implode("\r\n", $header)); 
	}
}
?>