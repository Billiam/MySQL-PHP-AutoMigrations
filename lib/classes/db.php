<?php
/**
 * This file houses the MpmDb class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmDb class encapsulates the creation of a PDO mysql object.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class MpmDb
{

	/**
	 * A PDO object.
	 * 
	 * @var PDO
	 */
	private $dbh;
  
	/**
	 * Object constructor.
	 * 
	 * @return MpmDb
	 */
	private function __construct()
	{
		if (!isset($GLOBALS['db_config']))
		{
			throw new Exception('Missing database configuration.');
		}
		$db_config = $GLOBALS['db_config'];
		$pdo_settings = array
		(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
		);
		$this->dbh = new PDO("mysql:host={$db_config->host};port={$db_config->port};dbname={$db_config->name}", $db_config->user, $db_config->pass, $pdo_settings);
	}

	/** 
	 * Returns a PDO object.
	 *
	 * @return PDO
	 */
	public static function getPdo()
	{
		static $db = NULL;
		if (is_null($db))
		{
			$db = new MpmDb();
		}
		return $db->dbh;
	}
	
}

?>