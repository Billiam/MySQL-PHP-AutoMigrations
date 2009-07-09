<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file houses the UpController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The UpController is used to migrate up to a new version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class UpController extends Controller
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses UpController::displayHelp()
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
		
		$up_to = $this->arguments[0];
		
		if (!is_numeric($up_to))
		{
			return $this->displayHelp();
		}
		
		// are we forcing this?
		$forced = false;
		if (isset($this->arguments[1]) && strcasecmp($this->arguments[1], '--force') == 0)
		{
		    $forced = true;
		}

        // what migrations need to be done?
        $list = MigrationHelper::getListOfMigrations($up_to);
        
		if (count($list) == 0)
		{
		    echo 'All needed migrations have already been run or no migrations exist.';
		    $clw->writeFooter();
		    exit;
		}
		
		$to = MigrationHelper::getTimestampFromId($up_to);
		
		echo "Migrating to " . $to . ' (ID '.$up_to.')... ';
		
		foreach ($list as $id => $obj)
		{
		    MigrationHelper::runMigration($obj, 'up', $forced);
		}
		
		MigrationHelper::setCurrentMigration($up_to);
		
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
		$obj->addText('./migrate.php up [migration #] [--force]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate up to a newer version.  You can get a list of all of the migrations available by using the list command.');
		$obj->addText(' ');
		$obj->addText('You must specify a migration # (as provided by the list command)');
		$obj->addText(' ');
		$obj->addText('If the --force option is provided, then the script will automatically skip over any migrations which cause errors and continue migrating forward.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php up 14', 4);
		$obj->addText('./migrate.php up 12 --force', 4);
		$obj->write();
	}
	
}

?>