<?php
/**
 * This file houses the MpmDownController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmDownController is used to migrate down to an older version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmDownController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmDownController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		$pdo = MpmDb::getPdo();
		
		if (count($this->arguments) == 0)
		{
			return $this->displayHelp();
		}
		
		$down_to = $this->arguments[0];
		if (!is_numeric($down_to))
		{
			return $this->displayHelp();
		}
		if ($down_to == 0)
		{
		    $down_to = -1;
		}

		$list = MpmMigrationHelper::getListOfMigrations($down_to, 'down');
		$total = count($list);
		$current = MpmMigrationHelper::getCurrentMigrationNumber();

		if ($down_to == '-1')
		{
			echo "Removing all migrations... ";
			$down_to = 0;
		}
		else
		{
			echo "Migrating to " . MpmMigrationHelper::getTimestampFromId($down_to) . ' (ID '.$down_to.')... ';
		}
		
		foreach ($list as $id => $obj)
		{
			MpmMigrationHelper::runMigration($obj, 'down');
		}
		
		MpmMigrationHelper::setCurrentMigration($down_to);
		
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
		$obj->addText('./migrate.php down [migration #]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate down to a previous version.  You can get a list of all of the migrations available by using the list command.');
		$obj->addText(' ');
		$obj->addText('You must specify a migration # (as provided by the list command)');
		$obj->addText(' ');
		$obj->addText('If you enter a migration number of 0 or -1, all migrations will be removed.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php down 14', 4);
		$obj->addText('./migrate.php down 12', 4);
		$obj->write();
	}
	
}

?>