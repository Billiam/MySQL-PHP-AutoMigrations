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
	
	/**
	 * Performs a single migration.
	 *
	 * @param object  $obj        		    a simple object with migration information (from a migration list)
	 * @param int    &$total_migrations_run a running total of migrations run
	 *
	 * @return void
	 */
	static public function runMigration(&$obj, $method = 'up')
	{
	    $pdo = MpmDb::getPdo();
		$pdo->beginTransaction();
		echo "\n\tPerforming migration " . $obj->timestamp . ' ... ';
		$filename = MpmStringHelper::getFilenameFromTimestamp($obj->timestamp);
		$classname = 'Migration_' . str_replace('.php', '', $filename);
		require_once(MPM_PATH . '/db/' . $filename);
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
			$sql = "UPDATE `mpm_migrations` SET `is_current` = 0";
			$pdo->exec($sql);
			$sql = "UPDATE `mpm_migrations` SET `active` = '$active', `is_current` = 1 WHERE `id` = {$obj->id}";
			$pdo->exec($sql);
		}
		catch (Exception $e)
		{
			$pdo->rollback();
			echo "failed!";
			echo "\n\t--- " . $e->getMessage();
			MpmMigrationHelper::saveLatest($new_latest);
			exit;
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
	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `is_current` = 1";
		$pdo = MpmDb::getPdo();
		$stmt = $pdo->query($sql);
		if ($stmt->rowCount() == 0)
		{
		    return false;
		}
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
	        $sql = "SELECT `id`, `timestamp` FROM `mpm_migrations` WHERE `timestamp` <= '$timestamp' AND `active` = 0 ORDER BY `timestamp`";
	    }
	    else
	    {
	        $sql = "SELECT `id`, `timestamp` FROM `mpm_migrations` WHERE `timestamp` >= '$timestamp' AND `active` = 1 ORDER BY `timestamp` DESC";
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
    	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `id` = '$id'";
    	    $stmt = $pdo->query($sql);
    	    if ($stmt->rowCount() == 1)
    	    {
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
	    $sql = "SELECT `id` FROM `mpm_migrations` WHERE `is_current` = 1";
		$pdo = MpmDb::getPdo();
		$stmt = $pdo->query($sql);
		if ($stmt->rowCount() == 0)
		{
		    return false;
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$latest = $row['id'];
		return $latest;
	}
	
}

?>