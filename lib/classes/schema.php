<?php
/**
 * This file houses the MpmSchema class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmSchema class is used to build an initial database structure.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
abstract class MpmSchema
{

    /**
     * Either a PDO or an ExceptionalMysqli object used to talk to the MySQL database.
     *
     * @var PDO|ExceptionalMysqli
     */
    protected $dbObj;
    
    /**
     * The timestamp of the migration which, when the schema is built, will be considered the current migration.
     *
     * All migrations prior to this timestamp will be ignored when building the database.
     *
     * Timestamp should be in CCYY-MM-DD HH:MM:SS format.
     *
     * @var string
     */
    protected $initialMigrationTimestamp;
    
    /**
     * Object constructor.
     *
     * @uses MpmDbHelper::getDbObj()
     *
     * @return MpmSchema
     */
    public function __construct()
    {
        $this->dbObj = MpmDbHelper::getDbObj();
        $this->initialMigrationTimestamp = null;
    }
    
    /**
     * Removes all of the tables in the database.
     *
     * @uses MpmDbHelper::getTables()
     *
     * @return void
     */
    public function destroy()
    {
		echo 'Looking for existing tables... ';
        $tables = MpmDbHelper::getTables($this->dbObj);
        $totalTables = count($tables);
        $displayTotal = $totalTables > 1 ? $totalTables - 1 : 0;
		echo 'found '.$displayTotal.'.';
        if ($totalTables > 1)
        {
            echo '  Removing:', "\n";
		    foreach ($tables as $table)
		    {
		        if ($table != 'mpm_migrations')
		        {
            		echo '        ', $table, "\n";
		            $this->dbObj->exec('DROP TABLE IF EXISTS `' . $table . '`');
                }
		    }
		}
		else
		{
		    echo '  No tables need to be removed.', "\n";
		}
    }

    /**
     * Clears the migrations table and then rebuilds it.
     *
     * @uses MpmListHelper::mergeFilesWithDb()
     * @uses MpmDbHelper::doSingleRowSelect()
     *
     * @return void
     */
    public function reloadMigrations()
    {
		echo 'Clearing out existing migration data... ';
        $this->dbObj->exec('TRUNCATE TABLE `mpm_migrations`');
		echo 'done.', "\n\n", 'Rebuilding migration data... ';
        MpmListHelper::mergeFilesWithDb();
        echo 'done.', "\n";
        if ($this->initialMigrationTimestamp != null)
        {
            echo "\n", 'Updating initial migration timestamp to ', $this->initialMigrationTimestamp, '... ';
            $result = MpmDbHelper::doSingleRowSelect('SELECT COUNT(*) AS total FROM `mpm_migrations` WHERE `timestamp` = "'.$this->initialMigrationTimestamp.'"', $this->dbObj);
            if ($result->total == 1)
            {
                $this->dbObj->exec('UPDATE `mpm_migrations` SET `is_current` = 0');
                $this->dbObj->exec('UPDATE `mpm_migrations` SET `is_current` = 1 WHERE `timestamp` = "'.$this->initialMigrationTimestamp.'"');
                $this->dbObj->exec('UPDATE `mpm_migrations` SET `active` = 1 WHERE `timestamp` <= "'.$this->initialMigrationTimestamp.'"');
            }
            echo 'done.', "\n";
        }
    }

    /**
     * Used to build the schema.  All SQL statements needed to create the initial database structure should be run here.
     *
     * @return void
     */
    abstract public function build();

}

?>
