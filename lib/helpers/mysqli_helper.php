<?php
/**
 * This file houses the MpmMysqliHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

 /**
  * This MpmMysqliHelper class is used to create a mysqli object.
  *
  * @package    mysql_php_migrations
  * @subpackage Helpers
  */
class MpmMysqliHelper
{
    
    /**
     * Creates a mysqli object.
     *
     * @return mysqli
     */
    static public function getMysqli()
    {
        echo "\n\n[Mysqli!]\n\n";
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