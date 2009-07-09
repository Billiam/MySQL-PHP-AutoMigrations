<?php
namespace ReflexSolutions\MysqlPhpMigrations;
/**
 * This file is included by the migrate.php script; it includes the MpmStringHelper and MpmAutoloadHelper classes and sets up the auto-class loading.
 *
 * @package    mysql_php_migrations
 * @subpackage Globals
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

if (file_exists(MPM_PATH . '/config/db_config.php'))
{
	/**
	 * Include the database connection info.
	 */
	require_once(MPM_PATH . '/config/db_config.php');
}

if (!defined('MPM_DB_PATH'))
{
    if (isset($db_config->db_path) && strlen($db_config->db_path) > 0)
    {
        /**
         * Defines the MPM_DB_PATH if specified.  Allows this to be outside of the main migration script library.
         */
        define('MPM_DB_PATH', $db_config->db_path);
    }
    else
    {
        /**
         * @ignore
         */
        define('MPM_DB_PATH', MPM_PATH . '/db/');
    }
}

/**
 * Include the ClassUndefinedException class.
 */
require_once(MPM_PATH . '/lib/exceptions/class_undefined_exception.php');

/** 
 * Include the MpmStringHelper class.
 */
require_once(MPM_PATH . '/lib/helpers/string_helper.php');

/** 
 * Include the MpmAutoloadHelper class.
 */
require_once(MPM_PATH . '/lib/helpers/autoload_helper.php');

// add default autoloader function to the autoload stack
if (function_exists('__autoload'))
{
    spl_autoload_register('__autoload');
}

// add custom library autoloader to the stack
spl_autoload_register('\ReflexSolutions\MysqlPhpMigrations\MpmAutoloadHelper::load');

?>