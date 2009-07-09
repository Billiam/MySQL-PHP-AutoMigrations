<?php
namespace ReflexSolutions\MysqlPhpMigrations;
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
     * Returns the total number of migrations available.
     *
     * @return int
     */
    static function getTotalMigrations()
    {
        try
        {
            $sql = "SELECT COUNT(*) AS total FROM `mpm_migrations`";
            $pdo = Db::getPdo();
            $stmt = $pdo->query($sql);
            $obj = $stmt->fetch(PDO::FETCH_OBJ);
        }
        catch (Exception $e)
        {
            echo "\n\nError: " . $e->getMessage();
        }
        return $obj->total;
    }
    
    /**
     * Returns a full list of all migrations.
     *
     * @param int $startIdx the start index number
     * @param int $total    total number of records to return
     *
     * @return arrays
     */
    static function getFullList($startIdx = 0, $total = 30)
    {
        $list = array();
        $sql = "SELECT * FROM `mpm_migrations` ORDER BY `timestamp`";
        if ($total > 0)
        {
            $sql .= " LIMIT $startIdx,$total";
        }
        $pdo = Db::getPdo();
        try
        {
            $stmt = $pdo->query($sql);
            while ($obj = $stmt->fetch(PDO::FETCH_OBJ))
            {
                $list[] = $obj;
            }
        }
        catch (Exception $e)
        {
            echo "\n\nError: " . $e->getMessage() . "\n\n";
            exit;            
        }
        return $list;
    }
    
    /**
     * Fetches a list of files and adds migrations to the database migrations table.
     * 
     * @return void
     */
    static function mergeFilesWithDb()
    {
        $pdo = Db::getPdo();
        $pdo->beginTransaction();
        // add any new files to the database
        try
        {
            $files = MpmListHelper::getListOfFiles();
            foreach ($files as $file)
            {
                $sql = "INSERT IGNORE INTO `mpm_migrations` ( `timestamp`, `active`, `is_current` ) VALUES ( '{$file->timestamp}', 0, 0 )";
                $pdo->exec($sql);
            }
        }
        catch (Exception $e)
        {
            $pdo->rollback();
            echo "\n\nError: " . $e->getMessage();
            echo "\n\n";
            exit;
        }
        $pdo->commit();
        $pdo->beginTransaction();
        // remove migrations from the database which no longer have a corresponding file and are not active yet
        try
        {
            $total_migrations = MpmListHelper::getTotalMigrations();
            $db_list = MpmListHelper::getFullList(0, $total_migrations);
            $files = MpmListHelper::getListOfFiles();
            $file_timestamps = MpmListHelper::getTimestampArray($files);
            foreach ($db_list as $obj)
            {
                if (!in_array($obj->timestamp, $file_timestamps) && $obj->active == 0)
                {
                    $sql = "DELETE FROM `mpm_migrations` WHERE `id` = '{$obj->id}'";
                    $pdo->exec($sql);
                }
            }
        }
        catch (Exception $e)
        {
            $pdo->rollback();
            echo "\n\nError: " . $e->getMessage();
            echo "\n\n";
            exit;
        }
        $pdo->commit();
    }
    
    /**
     * Given an array of objects (from the getFullList() or getListOfFiles() methods), returns an array of timestamps.
     *
     * @return array
     */
    static function getTimestampArray($obj_array)
    {
        $timestamp_array = array();
        foreach ($obj_array as $obj)
        {
            $timestamp_array[] = str_replace('T', ' ', $obj->timestamp);
        }
        return $timestamp_array;
    }
	
	/**
	 * Returns an array of objects which hold data about a migration file (timestamp, file, etc.).
	 *
	 * @param string $sort should either be old or new; determines how the migrations are sorted in the array
	 *
	 * @return array
	 */
	static public function getListOfFiles($sort = 'old')
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
		$files = scandir(MPM_DB_PATH, $sort_order);
		foreach ($files as $file)
		{
			$full_file = MPM_DB_PATH . $file;
			if ($file != '.' && $file != '..' && !is_dir($full_file) && stripos($full_file, '.php') !== false)
			{
                $timestamp = MpmStringHelper::getTimestampFromFilename($file);
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
		$list = MpmListHelper::getListOfFiles();
		foreach ($list as $obj)
		{
			$files[] = $obj->filename;
		}
		return $files;
	}
	
	/**
	 * Fetches a list of migrations which have already been run.
	 *
	 * @param string $latestTimestamp the current timestamp of the migration run last
	 * @param string $direction the way we are migrating; should either be up or down
	 *
	 * @return array
	 */
	static public function getListFromDb($latestTimestamp, $direction = 'up')
	{
		$pdo = Db::getPdo();
		$list = array();
		try
		{
			if ($direction == 'down')
			{
				$sql = "SELECT * FROM `mpm_migrations` WHERE `timestamp` <= '$latestTimestamp' AND `active` = 1";
				$countSql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `timestamp` <= '$latestTimestamp' AND `active` = 1";
			}
			else
			{
				$sql = "SELECT * FROM `mpm_migrations` WHERE `timestamp` >= '$latestTimestamp' AND `active` = 1";
				$countSql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `timestamp` >= '$latestTimestamp' AND `active` = 1";
			}
			$stmt = $pdo->query($countSql);
    	    // Resolution to Issue #1 - PDO::rowCount is not reliable
			$count = $stmt->fetchColumn();
			unset($stmt);
			$stmt = $pdo->query($sql);
			if ($count > 0)
			{
				while ($obj = $stmt->fetch(PDO::FETCH_OBJ))
				{
					$list[] = $obj->timestamp;
				}
			}
		}
		catch (Exception $e)
		{
			echo "\n\nERROR -- " . $e->getMessage();
			echo "\n\n";
			exit;
		}
		return $list;
	}
	
	
}

?>