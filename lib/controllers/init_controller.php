<?php
/**
 * This file houses the MpmInitController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmInitController initializes the system so that migrations can start happening.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmInitController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmInitController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$user = '';
		$dbname = '';
		$port = '';
		
		if (file_exists(MPM_PATH . '/config/db_config.php'))
		{
			echo "\nWARNING:  IF YOU CONTINUE, YOUR EXISTING MIGRATION SETUP WILL BE ERASED!";
			echo "\nThis can damage your database, cause conflicts, and create errors!";
			echo "\nDO YOU WANT TO CONTINUE? [y/N] ";
			$answer = fgets(STDIN);
			$answer = trim($answer);
			$answer = strtolower($answer);
			if (empty($answer) || substr($answer, 0, 1) == 'n')
			{
				echo "\nABORTED!\n\n";
				exit;
			}
		}
		
		echo "\nEnter your MySQL database hostname or IP address [localhost]: ";
		$host = fgets(STDIN);
		$host = trim($host);
		if (empty($host))
		{
			$host = 'localhost';
		}

		while (empty($port))
		{
			echo "\nEnter your MySQL database port [3306]: ";
			$port = fgets(STDIN);
			$port = trim($port);
			if (empty($port))
			{
				$port = 3306;
			}
			if (!is_numeric($port))
			{
				$port = '';
			}
		}
		
		while (empty($user))
		{
			echo "\nEnter your MySQL database username: ";
			$user = fgets(STDIN);
			$user = trim($user);
		}
		
		echo "\nEnter your MySQL database password []: ";
		$pass = fgets(STDIN);
		$pass = trim($pass);
		
		while (empty($dbname))
		{
			echo "\nEnter your MySQL database name: ";
			$dbname = fgets(STDIN);
			$dbname = trim($dbname);
		}
		
		$file = '<?php' . "\n\n";
		$file .= '$db_config = (object) array();' . "\n";
		$file .= '$db_config->host = ' . "'" . $host . "';" . "\n";
		$file .= '$db_config->port = ' . "'" . $port . "';" . "\n";
		$file .= '$db_config->user = ' . "'" . $user . "';" . "\n";
		$file .= '$db_config->pass = ' . "'" . $pass . "';" . "\n";
		$file .= '$db_config->name = ' . "'" . $dbname . "';" . "\n";
		$file .= "\n?>";
		
		if (file_exists(MPM_PATH . '/config/db_config.php'))
		{
			unlink(MPM_PATH . '/config/db_config.php');
		}
		
		$fp = fopen(MPM_PATH . '/config/db_config.php', "w");
		if ($fp == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		$success = fwrite($fp, $file);
		if ($success == false)
		{
			echo "\nUnable to write to file.  Initialization failed!\n\n";
			exit;
		}
		fclose($fp);
		
		require(MPM_PATH . '/config/db_config.php');
		$GLOBALS['db_config'] = $db_config;
		
		echo "\nConfiguration saved... looking for existing migrations table... ";
		
		try
		{
			if (false === $this->checkForDbTable())
			{
				echo "not found.\n";
				echo "Creating migrations table... ";
				$sql1 = "CREATE TABLE IF NOT EXISTS `mpm_migrations` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `timestamp` DATETIME NOT NULL, `active` TINYINT(1) NOT NULL DEFAULT 0, `is_current` TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY ( `id` ) ) ENGINE=InnoDB";
				$sql2 = "CREATE UNIQUE INDEX `TIMESTAMP_INDEX` ON `mpm_migrations` ( `timestamp` )";
				$pdo = MpmDb::getPdo();
				$pdo->beginTransaction();
				try
				{
					$pdo->exec($sql1);
					$pdo->exec($sql2);
				}
				catch (Exception $e)
				{
					$pdo->rollback();
					echo "failure!\n\n" . 'Unable to create required mpm_migrations table:' . $e->getMessage();
					echo "\n\n";
					exit;
				}
				$pdo->commit();
				echo "done.\n\n";
			}
			else
			{
				echo "found.\n";
			}
			
		}
		catch (Exception $e)
		{
			echo "failure!\n\nUnable to complete initialization: " . $e->getMessage() . "\n\n";
			echo "Check your database settings and re-run init.\n\n";
			exit;
		}
		
		echo "Initalization complete!\n\n";
		exit;
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
		$obj->addText('./migrate.php init');
		$obj->addText(' ');
		$obj->addText('This command is used to initialize the migration system for use with your particular deployment.  After you have modified the /config/db.php configuration file appropriately, you should run this command to setup the initial tracking schema and add your username to the migraiton archive.');
		$obj->addText(' ');
		$obj->addText('Example:');
		$obj->addText('./migrate.php init jdoe', 4);
		$obj->write();
	}
	
}

?>