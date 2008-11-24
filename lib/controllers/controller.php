<?php
/**
 * This file houses the MpmController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmController is the abstract parent class to all other controllers.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
abstract class MpmController
{

	/**
	 * An array of command line arguments (minus the first two elements which should already be shifted off from the MpmControllerFactory).
	 *
	 * @var array
	 */
	protected $arguments;
	
	/** 
	 * The current command being issued.
	 *
	 * @var string
	 */
	protected $command;
	
	/** 
	 * Object constructor.
	 * 
	 * @param array $arguments an array of command line arguments (minus the first two elements which should already be shifted off from the MpmControllerFactory)
	 *
	 * @return MpmController
	 */
	public function __construct($command = 'help', $arguments = array())
	{
		$this->arguments = $arguments;
		$this->command = $command;
		if ($command != 'help' && $command != 'init')
		{
			$this->checkIfReady();
		}
		MpmListHelper::mergeFilesWithDb();
	}
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @return void
	 */
	abstract public function doAction();
	
	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses MpmCommandLineWriter
	 * 
	 * @return void
	 */
	abstract public function displayHelp();
	
	/**
	 * Checks to make sure the db config script exists.
	 *
	 * If no conditions are met, will display error page to terminal and exit.
	 *
	 * @return void
	 */
	protected function checkIfReady()
	{
		$msg = '';
		// PDO available?
		if (!class_exists('PDO'))
		{
			$msg = 'It does not appear that the PDO extension is installed.  This script requires this extension.';
		}
		// does db.php exist?
		if (!file_exists(MPM_PATH . '/config/db_config.php'))
		{
			$msg = 'You have not yet run the init command.  You must run this command before you can use any other commands.';
		}
		if (!empty($msg))
		{
			$obj = MpmCommandLineWriter::getInstance();
			$obj->addText($msg);
			$obj->write();
			exit;
		}
	}
	
}


?>