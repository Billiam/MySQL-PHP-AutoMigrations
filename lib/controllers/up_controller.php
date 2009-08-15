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
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::writeHeader()
	 * @uses MpmCommandLineWriter::writeFooter()
	 * @uses MpmMigrationHelper::getListOfMigrations()
	 * @uses MpmMigrationHelper::getTimestampFromId()
	 * @uses MpmMigrationHelper::runMigration()
	 * @uses MpmMigrationHelper::setCurrentMigration
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$clw = MpmCommandLineWriter::getInstance();
		$clw->writeHeader();
		
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
        $list = MpmMigrationHelper::getListOfMigrations($up_to);
        
		if (count($list) == 0)
		{
		    echo 'All needed migrations have already been run or no migrations exist.';
		    $clw->writeFooter();
		    exit;
		}
		
		$to = MpmMigrationHelper::getTimestampFromId($up_to);
		
		echo "Migrating to " . $to . ' (ID '.$up_to.')... ';
		
		foreach ($list as $id => $obj)
		{
		    MpmMigrationHelper::runMigration($obj, 'up', $forced);
		}
		
		MpmMigrationHelper::setCurrentMigration($up_to);
		
		$clw->writeFooter();
	}

	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
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
