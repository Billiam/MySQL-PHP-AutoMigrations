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

		$list = MpmListHelper::getList();
		$total = count($list);
		$last_possible_num = $total - 1;
		$current = MpmMigrationHelper::getCurrentMigrationNumber();

		$clw->writeHeader();
		
		if ($up_to > $last_possible_num)
		{
			echo "Unable to migrate up to this migration number.\n";
			echo "You are already on that migration, past that migration, or it does not exist.\n";
			$clw->writeFooter();
			exit;
		}
		if ($up_to == $last_possible_num)
		{
			echo "Unable to migrate up to this migration number.\n";
			echo "Use the latest command instead.\n";
			$clw->writeFooter();
			exit;
		}
		if ($up_to == $current)
		{
			echo "Unable to migrate up to this migration number.\n";
			echo "You are already at this migration.\n";
			$clw->writeFooter();
			exit;
		}
		
		$start = ($current != 0) ? $current + 1 : 0;
		$total_migrations_run = 0;
		$new_latest = '';
		
		echo "Migrating to " . $list[$up_to]->timestamp . '... ';
		
		for ($i = $start; $i <= $up_to; $i++)
		{
			$obj = $list[$i];
			MpmMigrationHelper::runMigration('up', $obj, $pdo, $new_latest, $total_migrations_run);
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