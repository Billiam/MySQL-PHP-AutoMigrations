<?php
/**
 * This file houses the MpmMigrationHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmMigrationHelper contains a number of static functions which are used during the migration process.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmMigrationHelper
{
    
    static public function setCurrentMigration($id)
    {
	    $pdo = MpmDb::getPdo();
		$pdo->beginTransaction();
		try
		{
			$sql = "UPDATE `mpm_migrations` SET `is_current` = '0'";
			$pdo->exec($sql);
			$sql = "UPDATE `mpm_migrations` SET `is_current` = '1' WHERE `id` = {$id}";
			$pdo->exec($sql);
		}
		catch (Exception $e)
		{
			$pdo->rollback();
			echo "\n\tQuery failed!";
			echo "\n\t--- " . $e->getMessage();
			exit;
		}
		$pdo->commit();
    }
    
	
	/**
	 * Performs a single migration.
	 *
	 * @param object  $obj        		    a simple object with migration information (from a migration list)
	 * @param int    &$total_migrations_run a running total of migrations run
	 * @param bool    $forced               if true, exceptions will not cause the script to exit
	 *
	 * @return void
	 */
	static public function runMigration(&$obj, $method = 'up', $forced = false)
	{
		$filename = MpmStringHelper::getFilenameFromTimestamp($obj->timestamp);
		$classname = 'Migration_' . str_replace('.php', '', $filename);
		
	    // make sure the file exists; if it doesn't, skip it but display a message
	    if (!file_exists(MPM_DB_PATH . $filename))
	    {
	        echo "\n\tMigration " . $obj->timestamp . ' (ID '.$obj->id.') skipped - file missing.';
	        return;
	    }
	    
	    // file exists -- run the migration
		echo "\n\tPerforming " . strtoupper($method) . " migration " . $obj->timestamp . ' (ID '.$obj->id.')... ';
	    $pdo = MpmDb::getPdo();
		$pdo->beginTransaction();
		require_once(MPM_DB_PATH . $filename);
		$migration = new $classname();
		if ($method == 'down')
		{
			$active = 0;
		}
		else
		{
			$active = 1;
		}
		try
		{
			$migration->$method($pdo);
			$sql = "UPDATE `mpm_migrations` SET `active` = '$active' WHERE `id` = {$obj->id}";
			$pdo->exec($sql);
		}
		catch (Exception $e)
		{
			$pdo->rollback();
			echo "failed!";
			echo "\n";
		    $clw = MpmCommandLineWriter::getInstance();
    		$clw->writeLine($e->getMessage(), 12);
			if (!$forced)
			{
        		echo "\n\n";
			    exit;
			}
			else
			{
			    return;
		    }
		}
		$pdo->commit();
		echo "done.";
	}

	/**
	 * Returns the timestamp of the migration currently rolled to.
	 *
	 * @return string
	 */
	static public function getCurrentMigrationTimestamp()
	{
	    // Resolution to Issue #1 - PDO::rowCount is not reliable
	    $sql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `is_current` = 1";
		$pdo = MpmDb::getPdo();
		$stmt = $pdo->query($sql);
		if ($stmt->fetchColumn() == 0)
		{
		    return false;
		}
	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `is_current` = 1";
		$stmt = $pdo->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$latest = $row['timestamp'];
		return $latest;
	}
	
	/**
	 * Returns an array of migrations which need to be run (in order).
	 *
	 * @param int    $toId      the ID of the migration to stop on
	 * @param string $direction the direction of the migration; should be 'up' or 'down'
	 *
	 * @return array
	 */
	static public function getListOfMigrations($toId, $direction = 'up')
	{
		$pdo = MpmDb::getPdo();
	    $list = array();
	    $timestamp = MpmMigrationHelper::getTimestampFromId($toId);
	    if ($direction == 'up')
	    {
	        $sql = "SELECT `id`, `timestamp` FROM `mpm_migrations` WHERE `active` = 0 AND `timestamp` <= '$timestamp' ORDER BY `timestamp`";
	    }
	    else
	    {
	        $sql = "SELECT `id`, `timestamp` FROM `mpm_migrations` WHERE `active` = 1 AND `timestamp` > '$timestamp' ORDER BY `timestamp` DESC";
	    }
        try
        {
            $stmt = $pdo->query($sql);
            while ($obj = $stmt->fetch(PDO::FETCH_OBJ))
            {
                $list[$obj->id] = $obj;
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
     * Returns a timestamp when given a migration ID number.
     *
     * @param int $id the ID number of the migration
     *
     * @return string
     */
    static public function getTimestampFromId($id)
    {
	    $pdo = MpmDb::getPdo();
	    try
	    {
    	    // Resolution to Issue #1 - PDO::rowCount is not reliable
    	    $sql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `id` = '$id'";
    	    $stmt = $pdo->query($sql);
    	    if ($stmt->fetchColumn() == 1)
    	    {
        	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `id` = '$id'";
        	    $stmt = $pdo->query($sql);
    	        $result = $stmt->fetch(PDO::FETCH_OBJ);
    	        $timestamp = $result->timestamp;
	        }
	        else
	        {
	            $timestamp = false;
	        }
        }
        catch (Exception $e)
        {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
        }
	    return $timestamp;
    }

	/**
	 * Returns the number of the migration currently rolled to.
	 *
	 * @return string
	 */
	static public function getCurrentMigrationNumber()
	{
		$pdo = MpmDb::getPdo();
	    // Resolution to Issue #1 - PDO::rowCount is not reliable
	    $sql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `is_current` = 1";
		$stmt = $pdo->query($sql);
		if ($stmt->fetchColumn() == 0)
		{
		    return false;
		}
	    $sql = "SELECT `id` FROM `mpm_migrations` WHERE `is_current` = 1";
		$stmt = $pdo->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$latest = $row['id'];
		return $latest;
	}
	
}

?>