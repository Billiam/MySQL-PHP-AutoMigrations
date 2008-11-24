<?php
/**
 * This file houses the MpmUpController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmUpController is used to migrate up to a new version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmUpController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmUpController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		
		$this->rollBackInterleaves();
		
		$pdo = MpmDb::getPdo();
		
		if (count($this->arguments) == 0)
		{
			return $this->displayHelp();
		}
		
		$up_to = $this->arguments[0];
		if (!is_numeric($up_to))
		{
			return $this->displayHelp();
		}

        // what migrations need to be done?
        $list = MpmMigrationHelper::getListOfMigrations($up_to);
        
        // get latest timestamp
        $latest = MpmMigrationHelper::getCurrentMigrationTimestamp();
        if ($latest === false)
        {
            $latest = 'no migrations run';
        }
        
        // get current migration number
        $current = MpmMigrationHelper::getCurrentMigrationNumber();
        
		if (count($list) == 0)
		{
		    echo 'No migrations need to be run.  Latest migration: ' . $latest;
		    $clw->writeFooter();
		    exit;
		}
		
		$to = MpmMigrationHelper::getTimestampFromId($up_to);
		
		echo "Migrating to " . $to . '... ';
		
		foreach ($list as $id => $obj)
		{
		    MpmMigrationHelper::runMigration($obj);
		}
		
		$clw->writeFooter();
	}

    /**
     * If interleaved migrations are found, this method rolls back to that migration.  Called before the up command runs.
     *
     * @return void
     */
    private function rollBackInterleaves()
    {
        // any new migrations prior to current?
        $pdo = MpmDb::getPdo();
		$currentTimestamp = MpmMigrationHelper::getCurrentMigrationTimestamp();
        $sql = "SELECT `id` FROM `mpm_migrations` WHERE `timestamp` < '$currentTimestamp' AND `active` = 0 ORDER BY `timestamp` LIMIT 0,1";
        try
        {
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() == 1)
            {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
                $timestamp = MpmMigrationHelper::getTimestampFromId($result->id);
                echo "Interleaved Migration(s) Found... Rolling Back...";
				$list = MpmMigrationHelper::getListOfMigrations($result->id, 'down');
				foreach ($list as $obj)
				{
					MpmMigrationHelper::runMigration($obj, 'down');
				}
				echo "\n\n";
            }
        }
		catch (Exception $e)
		{
			echo "\n\nERROR: " . $e->getMessage() . "\n\n";
			exit;
		}
    }

	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
		$obj->addText('./migrate.php up [migration #]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate up to a newer version.  You can get a list of all of the migrations available by using the list command.');
		$obj->addText(' ');
		$obj->addText('You must specify a migration # (as provided by the list command)');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php up 14', 4);
		$obj->addText('./migrate.php up 12', 4);
		$obj->write();
	}
	
}

?>