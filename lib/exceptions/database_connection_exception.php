<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file houses the DatabaseConnectionException class.
 *
 * @package    mysql_php_migrations
 * @subpackage Exceptions
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

use \Exception;

/**
 * DatabaseConnectionException should be thrown if we can't connect to the database.
 *
 * @package    mysql_php_migrations
 * @subpackage Exceptions
 */
class DatabaseConnectionException extends Exception
{
	
}

?>