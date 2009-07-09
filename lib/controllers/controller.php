<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file houses the Controller class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The Controller is the abstract parent class to all other controllers.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
abstract class Controller
{

	/**
	 * An array of command line arguments (minus the first two elements which should already be shifted off from the ControllerFactory).
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
	 * @param array $arguments an array of command line arguments (minus the first two elements which should already be shifted off from the ControllerFactory)
	 *
	 * @return Controller
	 */
	public function __construct($command = 'help', $arguments = array())
	{
		$this->arguments = $arguments;
		$this->command = $command;
		if ($command != 'help' && $command != 'init')
		{
			$this->checkIfReady();
    		MpmListHelper::mergeFilesWithDb();
		}
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
	 * @uses CommandLineWriter
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
		$obj = CommandLineWriter::getInstance();
		// PDO available?
		if (!class_exists('PDO'))
		{
			$msg = 'It does not appear that the PDO extension is installed.  This script requires this extension.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		// does db.php exist?
		if (!file_exists(MPM_PATH . '/config/db_config.php'))
		{
			$msg = 'You have not yet run the init command.  You must run this command before you can use any other commands.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		if (false === $this->checkDbConnection())
		{
		    $msg = 'There is a problem with your database configuration.  Please check your settings and re-run the init command.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		if (false === $this->checkForDbTable())
		{
		    $msg = 'The migrations tracking table does not exist.  Please run the init command.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
	}
	
	/**
	 * Checks to make sure it is possible to connect to the database.
	 *
	 * @return bool
	 */
	protected function checkDbConnection()
	{
	    try
	    {
    		$pdo = Db::getPdo();
	    }
	    catch (Exception $e)
	    {
	        return false;
	    }
	    return true;
	}
	
	/**
	 * Checks whether or not the mpm_migrations database table exists.
	 *
	 * @return bool
	 */
	protected function checkForDbTable()
	{
		$tables = array();
		$pdo = Db::getPdo();
		$sql = "SHOW TABLES";
	    try
	    {
    		foreach ($pdo->query($sql) as $row)
    		{
    			$tables[] = $row[0];
    		}
	    }
	    catch (Exception $e)
	    {
	        return false;
	    }
		if (count($tables) == 0 || !in_array('mpm_migrations', $tables))
	    {
	        return false;
	    }
	    return true;
	}
	
}


?>