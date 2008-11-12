<?php
/**
 * This file houses the MpmLatestController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmLatestController is used to migrate up to the latest version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmLatestController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmLatestController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		// make sure we're init'd
		$this->checkIfReady();
		
		// need a pdo object
		$pdo = MpmDb::getPdo();
		
		// get latest timestamp
		$latest = MpmMigrationHelper::getCurrentMigrationTimestamp();
		
		// get list of migrations
		$list = MpmListHelper::getList();
		
		// get command line writer
		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		
		// setup PDO transactions
		$pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
		
		echo "Looking for migrations...";
		
		// loop through, running the migrations that are after the current migration
		$new_latest = '';
		$total_migrations_run = 0;
		foreach ($list as $obj)
		{
			if ($obj->timestamp > $latest)
			{
				MpmMigrationHelper::runMigration('up', $obj, $pdo, $new_latest, $total_migrations_run);
			}
		}
		
		MpmMigrationHelper::showMigrationResult($latest, $total_migrations_run);
		$clw->writeFooter();
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
		$obj->addText('./migrate.php latest');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate up to the most recent version.  No arguments are required.');
		$obj->addText(' ');
		$obj->addText('Valid Example:');
		$obj->addText('./migrate.php latest', 4);
		$obj->write();
	}
	
}

?>