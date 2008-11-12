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
	 * Displays results of an up, down, or latest migration.
	 *
	 * @param string &$latest               the timestamp of the latest run migration before the migrations were performed
	 * @param string &$new_latest           the timestamp of the latest run migration; will be saved
	 * @param int    &$total_migrations_run a running total of migrations run
	 *
	 * @return void
	 */
	static public function showMigrationResult(&$latest, &$total_migrations_run)
	{
		// if no migrations run, we're finished
		if ($total_migrations_run == 0)
		{
			echo "\n\nYou are currently at the latest migration ({$latest}).\n\nNo migrations performed.\n";
		}
		else
		{
			echo "\n\nMigration complete.  {$total_migrations_run} migrations performed.\n";
		}
	}
	
	/**
	 * Performs a single migration.
	 *
	 * @param string  $method               the migration method to run (up or down)
	 * @param object  $obj        		    a simple object with migration information (from a migration list)
	 * @param PDO    &$pdo        	        a PDO object
	 * @param string &$new_latest           the timestamp of the latest run migration; will be saved
	 * @param int    &$total_migrations_run a running total of migrations run
	 *
	 * @return void
	 */
	static public function runMigration($method, $obj, PDO &$pdo, &$new_latest, &$total_migrations_run)
	{
		echo "\n\tPerforming migration " . $obj->timestamp . ' ... ';
		$pdo->beginTransaction();
		$classname = 'Migration_' . str_replace('.php', '', $obj->filename);
		require_once($obj->full_file);
		$migration = new $classname();
		try
		{
			$migration->$method($pdo);
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
		$new_latest = $obj->timestamp;
		MpmMigrationHelper::saveLatest($new_latest);
		$total_migrations_run++;
		echo "done.";
	}

	/**
	 * Returns the timestamp of the migration currently rolled to.
	 *
	 * @return string
	 */
	static public function getCurrentMigrationTimestamp()
	{
		// what migration are we on?
		$sql = "SELECT `latest` FROM `mpm_schema`";
		$pdo = MpmDb::getPdo();
		$stmt = $pdo->query($sql);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$latest = $row['latest'];
		return $latest;
	}

	/**
	 * Returns the number of the migration currently rolled to.
	 *
	 * @return string
	 */
	static public function getCurrentMigrationNumber()
	{
		$timestamp = MpmMigrationHelper::getCurrentMigrationTimestamp();
		$list = MpmListHelper::getList();
		$number = 0;
		$total = count($list);
		for ($i = 0; $i < $total; $i++)
		{
			if ($list[$i]->timestamp == $timestamp)
			{
				$number = $i;
				break;
			}
		}
		return $number;
	}

	/**
	 * Saves the latest migration to the schema table.
	 *
	 * @param string $latest the timestamp of the latest migration
	 *
	 * @return void
	 */
	static public function saveLatest($latest)
	{
		$pdo = MpmDb::getPdo();
		$pdo->beginTransaction();
		try
		{
			$sql = "INSERT INTO `mpm_schema` SET `id` = 1, `latest` = '$latest' ON DUPLICATE KEY UPDATE `latest` = '$latest'";
			$pdo->exec($sql);
		}
		catch (Exception $e)
		{
			$pdo->rollback();
			echo "\n\nERROR -- " . $e->getMessage();
			echo "\n\n";
			exit;
		}
		$pdo->commit();
	}
	
}

?>