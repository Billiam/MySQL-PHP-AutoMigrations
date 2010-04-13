<?php
/**
 * This file houses the MpmAutoController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmAutoController is used to down and up to the newest available migration.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmAutoController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmLatestController::displayHelp()
	 * @uses MpmDbHelper::test()
	 * @uses MpmMigrationHelper::getMigrationCount()
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmMigrationHelper::getLatestMigration()
	 * @uses MpmUpController::doAction()
	 * 
	 * @param bool $quiet supresses certain text when true
	 *
	 * @return void
	 */
	public function doAction($quiet = false)
	{
		  // make sure we're init'd
		  MpmDbHelper::test();
			
			$clw = MpmCommandLineWriter::getInstance();
			$clw->writeHeader();

      // are we forcing this?
      $forced = '';
      if (isset($this->arguments[0]) && strcasecmp($this->arguments[0], '--force') == 0)
      {
          $forced = '--force';
      }
      //get completed migrations from database
      //get migrations from file
      $oldest_migration_id = MpmMigrationHelper::getOldestMigration();
      
      $current_timestamp = MpmMigrationHelper::getCurrentMigrationTimestamp();
      $current_num = MpmMigrationHelper::getCurrentMigrationNumber();
      
      //$latest_num =  MpmMigrationHelper::getLatestMigration();
      //$latest_timestamp = MpmMigrationHelper::getTimestampFromId($latest_num);
      
      $db_migrations =  MpmListHelper::getListFromDb($current_timestamp,'down');

      $files = MpmListHelper::getListOfFiles();

      $all_file_timestamps = MpmListHelper::getTimestampArray($files);
      $file_timestamps = array();
      
      foreach($all_file_timestamps as $timestamp) {
        if($timestamp<=$current_timestamp) {
          $file_timestamps[]=$timestamp;
        }
      }
      end($file_timestamps);
      $latest_timestamp = current($file_timestamps);
      
      //compare timestamps that are in either array to timestamps that are in both arrays to find missing timestamps in either
      //$missing_merges = array_diff(array_unique( array_merge($file_timestamps,$db_migrations) ), array_intersect($file_timestamps,$db_migrations) );
      $missing_database = array_diff($file_timestamps,$db_migrations);
      $missing_files = array_diff($db_migrations,$file_timestamps);
      $missing_merges = array_merge($missing_files,$missing_database);
            
      sort($missing_merges);
      reset($missing_merges);
      $oldest_missing=current($missing_merges);

      try
      {
        if($oldest_missing && $oldest_missing<=$current_timestamp) {
          $previous_migration = MpmMigrationHelper::getNextTimestamp($oldest_missing,'down');
          if($previous_migration) {
            $target_down = $previous_migration->id;
          } else {
            $target_down = -1;
          }
          $down = new MpmDownController('down', array ( $target_down, $forced, true ));
        	$down->doAction($quiet);
        }
        //merge files with database
				MpmListHelper::mergeFilesWithDb();
				
        $newest_id = MpmMigrationHelper::getLatestMigration();
        if($newest_id) {
					$newest_timestamp = MpmMigrationHelper::getTimestampFromId($newest_id);
					$current_timestamp = MpmMigrationHelper::getCurrentMigrationTimestamp();
	       	if($newest_timestamp > $current_timestamp) {
		        $obj = new MpmUpController('up', array ( $newest_id, $forced, true ));
		        $obj->doAction($quiet);
					} else {
						echo "\nUp to Date";
					}
	      } else {
					echo "\nUp to Date";
				}
			}
      catch (Exception $e)
      {
        echo "\n\nERROR: " . $e->getMessage() . "\n\n";
        exit;
      }
      $clw->writeFooter();
		
	}
	
	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses MpmCommandLineWriter::getInstance()
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
		$obj->addText('./migrate.php migrate [--force]');
		$obj->addText(' ');
		$obj->addText('This command is used to migrate down to the nearest shared patch, then back up to the latest.');
		$obj->addText('It is designed to be most useful when checking out branched code');
    $obj->addText('If the --force option is provided, then the script will automatically skip over any migrations which cause errors and continue migrating backward.');
		$obj->write();
	}
	
}

?>