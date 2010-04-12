<?php
/**
 * This file houses the MpmLatestController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmLatestController is used to migrate up to the latest version.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmProposeController extends MpmController
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
				
      //get completed migrations from database
      //get migrations from file
      $oldest_migration_id = MpmMigrationHelper::getOldestMigration();
      
      $current_timestamp = MpmMigrationHelper::getCurrentMigrationTimestamp();
      $current_num = MpmMigrationHelper::getCurrentMigrationNumber();
      
      $latest_num =  MpmMigrationHelper::getLatestMigration();
      $latest_timestamp = MpmMigrationHelper::getTimestampFromId($latest_num);
      
      $db_migrations =  MpmListHelper::getListFromDb($current_timestamp,'down');

      $files = MpmListHelper::getListOfFiles();

      $all_file_timestamps = MpmListHelper::getTimestampArray($files);
      $file_timestamps = array();
      
      foreach($all_file_timestamps as $timestamp) {
        if($timestamp<=$current_timestamp) {
          $file_timestamps[]=$timestamp;
        }
      }
      
      //compare timestamps that are in either array to timestamps that are in both arrays to find missing timestamps in either
      //$missing_merges = array_diff(array_unique( array_merge($file_timestamps,$db_migrations) ), array_intersect($file_timestamps,$db_migrations) );
      $missing_database = array_diff($file_timestamps,$db_migrations);
      $missing_files = array_diff($db_migrations,$file_timestamps);
      $missing_merges = array_merge($missing_files,$missing_database);
            
      sort($missing_merges);
      reset($missing_merges);
      $oldest_missing=current($missing_merges);
      
      $clw = MpmCommandLineWriter::getInstance();
      $clw->writeHeader();
      if(!$current_num) {
        echo 'You have not run any migrations';
      } else {
        echo "You are currently on migration $current_num -- " . $current_timestamp . '.';
      }
      
      if(!empty($missing_files)) {
        echo "\n\nCompleted migrations that are no longer in migrations directory\n----------\n";
        foreach($missing_files as $file) {
          echo " $file\n";
        }
      }
     
      if(!empty($missing_database)) {
        echo "\n\nOld migrations that have not been run\n----------\n";
        foreach($missing_database as $db) {
          echo " $db\n";
        }
      }
      
      
      if($current_timestamp<$latest_timestamp) {
        echo "\nLatest migration is: $latest_num -- $latest_timestamp.";
      }
      
      if(($oldest_missing && $oldest_missing<$current_timestamp) || $current_timestamp<$latest_timestamp) {
        echo "\n\n--- Migration Path --------------------------\n";

        if($oldest_missing && $oldest_missing<=$current_timestamp) {
          //find target down timestamp
          $previous_migration = MpmMigrationHelper::getNextTimestamp($oldest_missing,'down');

          if($previous_migration) {
            echo "  Migrate down to $previous_migration->id -- $previous_migration->timestamp\n";
          } else {
            echo "  Remove all migrations\n";
          }
        }
        
        if($current_timestamp<$latest_timestamp) {
          echo "  Update to latest: $latest_num -- $latest_timestamp";
        }
      } else {
        echo "\n\n  You are up to date";
      }
			/*$total_migrations = MpmMigrationHelper::getMigrationCount();
			if ($total_migrations == 0)
			{
				$clw = MpmCommandLineWriter::getInstance();
				$clw->addText('No migrations exist.');
				$clw->write();
				exit;
			}
			$to_id = MpmMigrationHelper::getLatestMigration();
			$obj = new MpmUpController('up', array ( $to_id, $forced ));
    		$obj->doAction($quiet);
        */
     echo "\n";
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
		$obj->addText('./migrate.php propose');
		$obj->addText(' ');
		$obj->addText('This command is used check the suggested migration path, especially after a merge or checkout.');
		$obj->addText(' ');
		$obj->write();
	}
	
}

?>
