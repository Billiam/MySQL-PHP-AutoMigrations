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
		$obj = MpmCommandLineWriter::getInstance();
		
		// does db.php exist?
		if (!file_exists(MPM_PATH . '/config/db_config.php'))
		{
			$msg = 'You have not yet run the init command.  You must run this command before you can use any other commands.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		// PDO available or mysqli available?
		if (!class_exists('PDO') && !class_exists('mysqli'))
		{
			$msg = 'It does not appear that the PDO and mysqli extensions are installed.  This script requires at least one of these extensions.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		// check for specific extension
		$db_config = $GLOBALS['db_config'];
		if ($db_config->method == MPM_METHOD_PDO && !class_exists('PDO'))
		{
			$msg = 'It does not appear that the PDO extension is installed.  Re-run the init command and select mysqli instead.';
			$obj->addText($msg);
			$obj->write();
			exit;
		}
		if ($db_config->method == MPM_METHOD_MYSQLI && !class_exists('mysqli'))
		{
			$msg = 'It does not appear that the mysqli extension is installed.  Re-run the init command and select PDO instead.';
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
		$db_config = $GLOBALS['db_config'];
		if ($db_config->method == MPM_METHOD_PDO)
		{
    	    try
    	    {
        		$pdo = MpmDb::getPdo();
    	    }
    	    catch (Exception $e)
    	    {
    	        return false;
    	    }
        }
        else if ($db_config->method == MPM_METHOD_PDO)
        {
    	    try
    	    {
        		$mysqli = MpmMysqliHelper::getMysqli();
    	    }
    	    catch (Exception $e)
    	    {
    	        return false;
    	    }
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
		$db_config = $GLOBALS['db_config'];
		if ($db_config->method == MPM_METHOD_PDO)
		{
    		$pdo = MpmDb::getPdo();
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
        }
		if (count($tables) == 0 || !in_array('mpm_migrations', $tables))
	    {
	        return false;
	    }
	    return true;
	}
	
}


?>