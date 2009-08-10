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
        $sql1 = "UPDATE `mpm_migrations` SET `is_current` = '0'";
        $sql2 = "UPDATE `mpm_migrations` SET `is_current` = '1' WHERE `id` = {$id}";
        $obj = MpmDbHelper::getDbObj();
        $obj->beginTransaction();
        try
        {
	        $obj->exec($sql1);
	        $obj->exec($sql2);
        }
        catch (Exception $e)
        {
	        $obj->rollback();
	        echo "\n\tQuery failed!";
	        echo "\n\t--- " . $e->getMessage();
	        exit;
        }
        $obj->commit();
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
	    $dbObj = MpmDbHelper::getDbObj();
		$dbObj->beginTransaction();
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
			$migration->$method($dbObj);
			$sql = "UPDATE `mpm_migrations` SET `active` = '$active' WHERE `id` = {$obj->id}";
			$dbObj->exec($sql);
		}
		catch (Exception $e)
		{
			$dbObj->rollback();
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
		$dbObj->commit();
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
	    $sql1 = "SELECT COUNT(*) as total FROM `mpm_migrations` WHERE `is_current` = 1";
        $sql2 = "SELECT `timestamp` FROM `mpm_migrations` WHERE `is_current` = 1";
		$dbObj = MpmDbHelper::getDbObj();
		switch (MpmDbHelper::getMethod())
		{
		    case MPM_METHOD_PDO:
		        $stmt = $dbObj->query($sql1);
		        if ($stmt->fetchColumn() == 0)
		        {
		            return false;
		        }
	            unset($stmt);
		        $stmt = $dbObj->query($sql2);
		        $row = $stmt->fetch(PDO::FETCH_ASSOC);
		        $latest = $row['timestamp'];
		        break;
            case MPM_METHOD_MYSQLI:
                $result = $dbObj->query($sql1);
                $row = $result->fetch_object();
                if ($row->total == 0)
                {
                    return false;
                }
                $result->close();
                unset($result);
                $result = $dbObj->query($sql2);
                $row = $result->fetch_object();
                $latest = $row->timestamp;
                break;
		}
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
        switch(MpmDbHelper::getMethod())
        {
            case MPM_METHOD_PDO:
                try
                {
            		$pdo = MpmDbHelper::getPdoObj();
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
                break;
            case MPM_METHOD_MYSQLI:
                try
                {
                    $mysqli = MpmDbHelper::getMysqliObj();
                    $results = $mysqli->query($sql);
                    while ($row = $results->fetch_object())
                    {
                        $list[$row->id] = $obj;
                    }
                }
                catch (Exception $e)
                {
                    echo "\n\nError: " . $e->getMessage() . "\n\n";
                    exit;
                }
                break;
            
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
        try
        {
	        switch (MpmDbHelper::getMethod())
	        {
	            case MPM_METHOD_PDO:
            	    // Resolution to Issue #1 - PDO::rowCount is not reliable
               	    $pdo = MpmDbHelper::getPdoObj();
            	    $sql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `id` = '$id'";
            	    $stmt = $pdo->query($sql);
            	    if ($stmt->fetchColumn() == 1)
            	    {
            	        unset($stmt);
                	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `id` = '$id'";
                	    $stmt = $pdo->query($sql);
            	        $result = $stmt->fetch(PDO::FETCH_OBJ);
            	        $timestamp = $result->timestamp;
                    }
                    else
                    {
                        $timestamp = false;
                    }
                    break;
                case MPM_METHOD_MYSQLI:
               	    $mysqli = MpmDbHelper::getMysqliObj();
            	    $sql = "SELECT COUNT(*) as total FROM `mpm_migrations` WHERE `id` = '$id'";
            	    $stmt = $mysqli->query($sql);
            	    $row = $stmt->fetch_object();
            	    if ($row->total == 1)
            	    {
            	        $stmt->close();
            	        unset($stmt);
                	    $sql = "SELECT `timestamp` FROM `mpm_migrations` WHERE `id` = '$id'";
                	    $stmt = $mysqli->query($sql);
            	        $result = $stmt->fetch_object();
            	        $timestamp = $result->timestamp;
            	        $stmt->close();
            	        $mysqli->close();
                    }
                    else
                    {
                        $timestamp = false;
                    }
                    break;
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
	    try
	    {
            switch (MpmDbHelper::getMethod())
            {
                case MPM_METHOD_PDO:
		            $pdo = MpmDbHelper::getDbObj();
	                // Resolution to Issue #1 - PDO::rowCount is not reliable
	                $sql = "SELECT COUNT(*) FROM `mpm_migrations` WHERE `is_current` = 1";
		            $stmt = $pdo->query($sql);
		            if ($stmt->fetchColumn() == 0)
		            {
		                return false;
		            }
	                $sql = "SELECT `id` FROM `mpm_migrations` WHERE `is_current` = 1";
	                unset($stmt);
		            $stmt = $pdo->query($sql);
		            $row = $stmt->fetch(PDO::FETCH_ASSOC);
		            $latest = $row['id'];
		            break;
                case MPM_METHOD_MYSQLI:
		            $mysqli = MpmDbHelper::getDbObj();
	                $sql = "SELECT COUNT(*) as total FROM `mpm_migrations` WHERE `is_current` = 1";
            	    $stmt = $mysqli->query($sql);
            	    $row = $stmt->fetch_object();
            	    if ($row->total == 1)
		            {
		                return false;
		            }
	                $sql = "SELECT `id` FROM `mpm_migrations` WHERE `is_current` = 1";
	                $stmt->close();
	                unset($stmt);
		            $stmt = $mysqli->query($sql);
		            $row = $stmt->fetch_object();
		            $latest = $row->id;
		            $stmt->close();
		            $mysqli->close();
		            break;
            }	    
	    }
	    catch (Exception $e)
	    {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
        return $latest;
	}
	
	/**
	 * Returns the total number of migrations.
	 *
	 * @uses MpmDb::getPdo() 
	 *
	 * @return int
	 */
	static public function getMigrationCount()
	{
	    try
	    {
	        switch (MpmDbHelper::getMethod())
	        {
	            case MPM_METHOD_PDO:
			        $pdo = MpmDbHelper::getDbObj();
            	    // Resolution to Issue #1 - PDO::rowCount is not reliable
			        $sql = "SELECT COUNT(id) FROM `mpm_migrations`";
			        $stmt = $pdo->query($sql);
			        $count = $stmt->fetchColumn();
	                break;
	            case MPM_METHOD_MYSQLI:
			        $mysqli = MpmDbHelper::getDbObj();
			        $sql = "SELECT COUNT(id) AS total FROM `mpm_migrations`";
			        $stmt = $mysqli->query($sql);
			        $row = $stmt->fetch_object();
			        $count = $row->total;
	                break;
	        }
	    }
	    catch (Exception $e)
	    {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $count;
	}
	
	static public function getLatestMigration()
	{
        $sql = "SELECT `id` FROM `mpm_migrations` ORDER BY `timestamp` DESC LIMIT 0,1";
	    try
	    {
	        switch(MpmDbHelper::getMethod())
	        {
	            case MPM_METHOD_PDO:
	                $pdo = MpmDbHelper::getDbObj();
		            $stmt = $pdo->query($sql);
		            $result = $stmt->fetch(PDO::FETCH_OBJ);
		            $to_id = $result->id;
	                break;
	            case MPM_METHOD_MYSQLI:
	                $mysqli = MpmDbHelper::getDbObj();
		            $stmt = $mysqli->query($sql);
		            $result = $stmt->fetch_object();
		            $to_id = $result->id;
	                break;
	        }
	    }
	    catch (Exception $e)
	    {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $to_id;
	}
	
	static public function doesMigrationExist($id)
	{
        $sql = "SELECT COUNT(*) as total FROM `mpm_migrations` WHERE `id` = '$id'";
        $return = false;
	    try
	    {
	        switch(MpmDbHelper::getMethod())
	        {
	            case MPM_METHOD_PDO:
	                $pdo = MpmDbHelper::getDbObj();
		            $stmt = $pdo->query($sql);
		            $result = $stmt->fetch(PDO::FETCH_OBJ);
		            if ($result->total > 0)
		            {
		                $return = true;
		            }
	                break;
	            case MPM_METHOD_MYSQLI:
	                $mysqli = MpmDbHelper::getDbObj();
		            $stmt = $mysqli->query($sql);
		            $result = $stmt->fetch_object();
		            if ($result->total > 0)
		            {
		                $return = true;
		            }
	                break;
	        }
	    }
	    catch (Exception $e)
	    {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $return;
	}
	
	static public function getMigrationObject($id)
	{
		$sql = "SELECT * FROM `mpm_migrations` WHERE `id` = '$id'";
		$obj = null;
	    try
	    {
	        switch(MpmDbHelper::getMethod())
	        {
	            case MPM_METHOD_PDO:
	                $pdo = MpmDbHelper::getDbObj();
		            $stmt = $pdo->query($sql);
		            $obj = $stmt->fetch(PDO::FETCH_OBJ);
	                break;
	            case MPM_METHOD_MYSQLI:
	                $mysqli = MpmDbHelper::getDbObj();
		            $stmt = $mysqli->query($sql);
		            $obj = $stmt->fetch_object();
	                break;
	        }
	    }
	    catch (Exception $e)
	    {
            echo "\n\nERROR: " . $e->getMessage() . "\n\n";
            exit;
	    }
	    return $obj;
	}
}

?>
