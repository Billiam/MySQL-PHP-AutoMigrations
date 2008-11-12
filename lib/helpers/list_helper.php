<?php
/**
 * This file houses the MpmListHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmListHelper is used to obtain various lists related to migration files.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmListHelper
{
	
	/**
	 * Returns an array of objects which hold data about a migration (timestamp, file, etc.).
	 *
	 * @param string $sort should either be old or new; determines how the migrations are sorted in the array
	 *
	 * @return array
	 */
	static public function getList($sort = 'old')
	{
		$list = array();
		if ($sort == 'new')
		{
			$sort_order = 1;
		}
		else
		{
			$sort_order = 0;
		}
		$files = scandir(MPM_PATH . '/db/', $sort_order);
		foreach ($files as $file)
		{
			$full_file = MPM_PATH . '/db/' . $file;
			if ($file != '.' && $file != '..' && !is_dir($full_file) && stripos($full_file, '.php') !== false)
			{
				$time = substr($file, 0, strlen($file) - 4);
				$t = explode('_', $time);
				$timestamp = $t[0] . '-' . $t[1] . '-' . $t[2] . 'T' . $t[3] . ':' . $t[4] . ':' . $t[5] . '+00:00';
				$obj = (object) array();
				$obj->timestamp = $timestamp;
				$obj->filename = $file;
				$obj->full_file = $full_file;
				$list[] = $obj;
			}
		}
		return $list;
	}
	
	/**
	 * Returns an array of migration filenames.
	 *
	 * @return array
	 */
	static public function getFiles()
	{
		$files = array();
		$list = MpmListHelper::getList();
		foreach ($list as $obj)
		{
			$files[] = $obj->filename;
		}
		return $files;
	}
	
}

?>