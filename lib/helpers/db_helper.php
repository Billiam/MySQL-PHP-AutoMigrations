<?php
/**
 * This file houses the MpmDbHelper class.
 *
 * @package mysql-php-migrations
 * @subpackage Helpers
 */

/**
 * The MpmDbHelper class is used to fetch database objects (PDO or Mysqli right now) and perform basic database actions.
 *
 * @package mysql-php-migrations
 * @subpackage Helpers
 */
class MpmDbHelper
{

    /**
     * Returns the correct database object based on the database configuration file.
     *
     * @throws Exception if database configuration file is missing or method is incorrectly defined
     *
     * @return object
     */
    static public function getDbObj()
    {
		if (!isset($GLOBALS['db_config']))
		{
			throw new Exception('Missing database configuration.');
		}
		$db_config = $GLOBALS['db_config'];
		switch ($db_config->method)
		{
		    case MPM_METHOD_PDO:
        	    return MpmDbHelper::getPdoObj();
            case MPM_METHOD_MYSQLI:
                return MpmDbHelper::getMysqliObj();
            default:
                throw new Exception('Unknown database connection method defined in database configuration.');
		}
    }
    
    /**
     * Returns a PDO object with connection in place.
     *
     * @throws MpmDatabaseConnectionException if unable to connect to the database
     *
     * @return PDO
     */
    static public function getPdoObj()
    {
		$pdo_settings = array
		(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
		);
		return new PDO("mysql:host={$db_config->host};port={$db_config->port};dbname={$db_config->name}", $db_config->user, $db_config->pass, $pdo_settings);
    }
    
    /**
     * Returns a mysqli object with connection in place.
     *
     * @throws MpmDatabaseConnectionException if unable to connect to the database
     *
     * @return mysqli
     */
    static public function getMysqliObj()
    {
        $db_config = $GLOBALS['db_config'];
        $mysqli = new mysqli($db_config->host, $db_config->user, $db_config->pass, $db_config->name, $db_config->port);
        if (mysqli_connect_error())
        {
            throw new MpmDatabaseConnectionException(mysqli_connect_error());
        }
        return $mysqli;
    }

}


?>
