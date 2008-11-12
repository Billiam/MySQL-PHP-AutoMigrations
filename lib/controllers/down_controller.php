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

		$list = MpmListHelper::getList();
		$total = count($list);
		$last_possible_num = $total - 1;
		$current = MpmMigrationHelper::getCurrentMigrationNumber();

		$clw->writeHeader();
		
		if ($down_to > $last_possible_num)
		{
			echo "Unable to migrate down to this migration number.\n";
			echo "That migration number does not exist.\n";
			$clw->writeFooter();
			exit;
		}
		if ($down_to == $last_possible_num)
		{
			echo "Unable to migrate down to this migration number.\n";
			echo "Use the latest command instead.\n";
			$clw->writeFooter();
			exit;
		}
		if ($down_to >= $current)
		{
			echo "Unable to migrate down to this migration number.\n";
			echo "This migration is after your current migration.  Try the up command instead.\n";
			$clw->writeFooter();
			exit;
		}
		
		$start = ($current != 0) ? $current : 0;
		$total_migrations_run = 0;
		$new_latest = '';
		
		if ($down_to == '-1')
		{
			echo "Removing all migrations... ";
			$down_to = 0;
		}
		else
		{
			echo "Migrating to " . $list[$down_to]->timestamp . '... ';
		}
		
		for ($i = $start; $i >= $down_to; $i--)
		{
			$obj = $list[$i];
			MpmMigrationHelper::runMigration('down', $obj, $pdo, $new_latest, $total_migrations_run);
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
		$obj->addText('./migrate.php down [migration #]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate down to a previous version.  You can get a list of all of the migrations available by using the list command.');
		$obj->addText(' ');
		$obj->addText('You must specify a migration # (as provided by the list command)');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php down 14', 4);
		$obj->addText('./migrate.php down 12', 4);
		$obj->write();
	}
	
}

?>