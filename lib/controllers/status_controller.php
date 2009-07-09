<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file houses the StatusController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The StatusController is used to display the latest migration.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class StatusController extends Controller
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @return void
	 */
	public function doAction()
	{
		// make sure we're init'd
		$this->checkIfReady();
		
		// need a pdo object
		$pdo = Db::getPdo();
		
		// get latest timestamp
		$latest = MigrationHelper::getCurrentMigrationTimestamp();
		
		// get latest number
		$num = MigrationHelper::getCurrentMigrationNumber();
		
		// get list of migrations
		$list = MpmListHelper::getFullList();
		
		// get command line writer
		$clw = CommandLineWriter::getInstance();
		$clw->writeHeader();
		
		if (empty($latest))
		{
			echo "You have not performed any migrations yet.";
		}
		else
		{
			echo "You are currently on migration $num -- " . $latest . '.';
		}
		echo "\n";
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
		$obj->addText('./migrate.php status');
		$obj->addText(' ');
		$obj->addText('This command is used to display the current migration you are on and lists any pending migrations which would be performed if you migrated to the most recent version of the database.');
		$obj->addText(' ');
		$obj->addText('Valid Example:');
		$obj->addText('./migrate.php status', 4);
		$obj->write();
	}
	
}

?>