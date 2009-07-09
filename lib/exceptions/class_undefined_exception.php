<?php
namespace ReflexSolutions\MysqlPhpMigrations;

/**
 * This file houses the ClassUndefinedException class.
 *
 * @package    mysql_php_migrations
 * @subpackage Exceptions
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

use \Exception;

/**
 * ClassUndefinedException should be thrown when a class is being instantiated but it is not yet defined.
 *
 * @package    mysql_php_migrations
 * @subpackage Exceptions
 */
class ClassUndefinedException extends Exception
{
	
}

?>