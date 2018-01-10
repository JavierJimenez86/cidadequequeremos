<?php
class FileUtil {
	
	public static function unzip($file, $folder)
	{
		if (!file_exists($folder))
		{
			throw new Exception('Folder ' . $folder . ' not found');
		}
		
		$archive = new PclZip($file);
		
		$list = $archive->extract(PCLZIP_OPT_PATH, $folder);
		
		if ($list == 0)
		{
			throw new Exception('Unzip file error: ' . $archive->errorInfo(true));
		}
		
		return $list;
	}

	public static function deleteFile($file)
	{
		if (file_exists($file))
		{
			if (!unlink($file))
			{
				throw new Exception('Delete file (' . $file . ') failed');
			}
		}	
	}
	
	public static function deleteFolder($folder)
	{
		if (file_exists($folder))
		{
			if (!rmdir($folder))
			{
				 throw new Exception('Delete folder (' . $folder . ') failed');	
			}
		}
	}
	
	public static function renameFileIfNotExist($old_name, $new_name)
	{
		if (!file_exists($old_name))
		{
			throw new Exception('File/folder (' . $old_name . ') not found');
		}
		
		if (!file_exists($new_name))
		{
			if (!rename($old_name, $new_name))
			{
				throw new Exception('Rename file/folder (' . $new_name . ') failed');	 	
			}
		}
	}
	
	public static function renameFile($old_name, $new_name)
	{
		if (!file_exists($old_name))
		{
			throw new Exception('File/folder (' . $old_name . ') not found');
		}
		
		if (file_exists($new_name))
		{
			throw new Exception('File/folder (' . $new_name . ') already exists');
		}
		
		if (!rename($old_name, $new_name))
		{
			throw new Exception('Rename file/folder (' . $new_name . ') failed');	 	
		}
	}

	public static function createFolder($folder, $chmod = 0777)
	{
		if (file_exists($folder))
		{
			throw new Exception('Folder (' . $folder . ') already exists');
		}
		
		if (!mkdir($folder, $chmod))
		{
			throw new Exception('Create folder (' . $folder . ') failed');
		}
	}
	
	public static function createFolderIfNotExist($folder, $chmod = 0777)
	{
		if (!file_exists($folder))
		{
			if (!mkdir($folder, $chmod))
			{
				throw new Exception('Create folder (' . $folder . ') failed');
			}
		}
	}
	
	public static function readFiles($folder)
	{	
		$files = array();
		
		if (file_exists($folder))
		{
			$directory = opendir($folder);
			
			while ($file = readdir($directory))
			{
				if (is_file($folder . $file))
				{
					$files[] = $file;
				}
			}
			
			closedir($directory);
		}
		
		array_multisort($files, SORT_ASC);
		return $files;
	}	
	
	public static function readFolders($folder)
	{	
		$files = array();
		
		if (file_exists($folder))
		{
			
			$directory = opendir($folder);
			
			while ($file = readdir($directory))
			{
				if (is_dir($folder . $file))
				{
					$files[] = $file;
				}
			}
			
			closedir($directory);
		}
		
		array_multisort($files, SORT_ASC);
		return $files;
	}	
		
	public static function readFolder($folder)
	{	
		$files = array();
		
		if (file_exists($folder))
		{
			
			$directory = opendir($folder);
			
			while ($file = readdir($directory))
			{
				if (is_file($folder . $file) || is_dir($folder . $file))
				{
					$files[] = $file;
				}
			}
			
			closedir($directory);
		}
		
		array_multisort($files, SORT_ASC);
		return $files;
	}	
	
	public static function deleteFiles($folder)
	{
		if (file_exists($folder))
		{
			$directory = opendir($folder);
			
			while ($file = readdir($directory))
			{
				if (is_file($folder . $file))
				{
					FileUtil::deleteFile($folder . $file);
				}
			}
			
			closedir($directory);
		}
	}

	public static function urlName($file_name)
	{
		$file_name = iconv('UTF-8', 'ASCII//TRANSLIT', $file_name);	
		
		$file_name = mb_strtolower(ltrim(rtrim($file_name)));	
		/*	
		$file_name = preg_replace('/[àáâãä]/', 'a', $file_name);
		$file_name = preg_replace('/[èéêë]/', 'e', $file_name);
		$file_name = preg_replace('/[ìíîï]/', 'i', $file_name);
		$file_name = preg_replace('/[òóôõö]/', 'o', $file_name);
		$file_name = preg_replace('/[ùúûü]/', 'u', $file_name);
		$file_name = preg_replace('/[ç]/', 'c', $file_name);
		*/
		$file_name = preg_replace('/[^a-zA-Z0-9._\- ]/', '', $file_name);
		$file_name = preg_replace('/[_ ]/','-', $file_name);
		$file_name = preg_replace('/[-]{2,}/', '-', $file_name);
		
		return $file_name;
	}	
}
?>