<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file houses the DownController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The DownController is used to migrate down to an older version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class DownController extends Controller
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses DownController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$clw = CommandLineWriter::getInstance();
		$clw->writeHeader();
		$pdo = Db::getPdo();
		
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
		
		// are we forcing this?
		$forced = false;
		if (isset($this->arguments[1]) && strcasecmp($this->arguments[1], '--force') == 0)
		{
		    $forced = true;
		}
		
		$list = MigrationHelper::getListOfMigrations($down_to, 'down');
		$total = count($list);
		$current = MigrationHelper::getCurrentMigrationNumber();

		if ($down_to == '-1')
		{
			echo "Removing all migrations... ";
			$down_to = 0;
		}
		else
		{
			echo "Migrating to " . MigrationHelper::getTimestampFromId($down_to) . ' (ID '.$down_to.')... ';
		}
		
		foreach ($list as $id => $obj)
		{
			MigrationHelper::runMigration($obj, 'down', $forced);
		}
		
		MigrationHelper::setCurrentMigration($down_to);
		
		$clw->writeFooter();
	}

	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses CommandLineWriter::addText()
	 * @uses CommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = CommandLineWriter::getInstance();
		$obj->addText('./migrate.php down [migration #] [--force]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate down to a previous version.  You can get a list of all of the migrations available by using the list command.');
		$obj->addText(' ');
		$obj->addText('You must specify a migration # (as provided by the list command)');
		$obj->addText(' ');
		$obj->addText('If you enter a migration number of 0 or -1, all migrations will be removed.');
		$obj->addText(' ');
		$obj->addText('If the --force option is provided, then the script will automatically skip over any migrations which cause errors and continue migrating backward.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php down 14', 4);
		$obj->addText('./migrate.php down 12 --force', 4);
		$obj->write();
	}
	
}

?>