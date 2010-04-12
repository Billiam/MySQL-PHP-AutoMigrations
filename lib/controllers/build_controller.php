<?php
/**
* This file houses the MpmBuildController class.
*
* @package    mysql_php_migrations
* @subpackage Controllers
* @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
* @link       http://code.google.com/p/mysql-php-migrations/
*/

/**
* The MpmBuildController is used to build a database schema from the ground up.
*
* @package    mysql_php_migrations
* @subpackage Controllers
*/
class MpmBuildController extends MpmController
{

  /**
  * Determines what action should be performed and takes that action.
  *
  * @uses MpmDbHelper::test()
  * @uses MpmCommandLineWriter::getInstance()
  * @uses MpmCommandLineWriter::addText()
  * @uses MpmCommandLineWriter::write()
  * @uses MpmCommandLineWriter::writeHeader()
  * @uses MpmCommandLineWriter::writeFooter()
  * @uses MpmBuildController::build()
  * @uses MPM_DB_PATH
  * 
  * @return void
  */
  public function doAction()
  {
    // make sure system is init'ed
    MpmDbHelper::test();

    $clw = MpmCommandLineWriter::getInstance();

    $forced = false;

    // are we adding a schema file?
    if (isset($this->arguments[0]) && $this->arguments[0] == 'add')
    {
      // make sure the schema file doesn't exist
      if (file_exists(MPM_DB_PATH . 'schema.php'))
      {
        $clw->addText('The schema file already exists.  Delete it first if you want to use this option.');
        $clw->write();
        exit;
      }

      $db = MpmDbHelper::getDbObj();                               
      $result = $db->exec("show tables");                          
      $schema_queries = array();                                   
      while($row = $result->fetch_array(MYSQLI_NUM))               
      {                                                            
        if($row[0]==='mpm_migrations') continue;                   
        $tabres = $db->exec("show create table {$row[0]}");        
        $tabrow = $tabres->fetch_array(MYSQLI_NUM);                
        $schema_queries[] = $tabrow[1];                            
      }         

      $file = "<?php\n";
      $file .= "/**\n";
      $file .= " * This file houses the MpmInitialSchema class.\n";
      $file .= " *\n";
      $file .= " * This file may be deleted if you do not wish to use the build command or build on init features.\n";
      $file .= " *\n";
      $file .= " * @package    mysql_php_migrations\n";
      $file .= " * @subpackage Classes\n";
      $file .= " * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License\n";
      $file .= " * @link       http://code.google.com/p/mysql-php-migrations/\n";
      $file .= " */\n";
      $file .= "\n";
      $file .= "/**\n";
      $file .= " * The MpmInitialSchema class is used to build an initial database structure.\n";
      $file .= " *\n";
      $file .= " * @package    mysql_php_migrations\n";
      $file .= " * @subpackage Classes\n";
      $file .= " */\n";
      $file .= "class MpmInitialSchema extends MpmSchema\n";
      $file .= "{\n";
      $file .= "\n";
      $file .= "\t".'protected $tables = array('."\n";             
      $file .= "\t\"".implode("\",\n\t\"",$schema_queries);        
      $file .= "\"\n\t);\n\n";       
      $file .= "\tpublic function __construct()\n";
      $file .= "\t{\n";
      $file .= "\t\tparent::__construct();\n";
      $file .= "\n";
      $file .= "\t\t/* If you build your initial schema having already executed a number of migrations,\n";
      $file .= "\t\t* you should set the initial migration timestamp.\n";
      $file .= "\t\t*\n";
      $file .= "\t\t* The initial migration timestamp will be set to active and this migration and all\n";
      $file .= "\t\t* previous will be ignored when the build command is used.\n";
      $file .= "\t\t*\n";
      $file .= "\t\t* EX:\n";
      $file .= "\t\t*\n";
      $file .= "\t\t* " . '$' . "this->initialMigrationTimestamp = '2009-08-01 15:23:44';\n";
      $file .= "\t\t*/\n";
      $file .= "\t\t" . '$' . "this->initialMigrationTimestamp = date('Y-m-d H:i:s');\n";
      $file .= "\t}\n";
      $file .= "\n";
      $file .= "\tpublic function build()\n";
      $file .= " \t{\n";
      $file .= "\t\t/* Add the queries needed to build the initial structure of your database.\n";
      $file .= "\t\t*\n";
      $file .= "\t\t* EX:\n";
      $file .= "\t\t*\n";
      $file .= "\t\t* " . '$' . "this->dbObj->exec('CREATE TABLE `testing` ( `id` INT(11) AUTO_INCREMENT NOT NULL, `vals` INT(11) NOT NULL, PRIMARY KEY ( `id` ))');\n";
      $file .= "\t\t*/\n";
      $file .= "\t\tforeach(\$this->tables as \$table)\n";         


      $file .= "\t\t{\n";
      $file .= "\t\t\t\$this->dbObj->exec(\$table);\n";
      $file .= "\t\t}\n";
      $file .= "\t}\n";
      $file .= "\n";
      $file .= "}\n";
      $file .= "\n";
      $file .= "?>";

      $fp = fopen(MPM_DB_PATH . 'schema.php', "w");
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

      $clw->addText('File ' . MPM_DB_PATH . 'schema.php has been created.');
      $clw->write();
      exit;

    }
    else if (isset($this->arguments[0]) && $this->arguments[0] == '--force')
      {
        $forced = true;
      }

      // make sure the schema file exists
      if (!file_exists(MPM_DB_PATH . 'schema.php'))
    {
      $clw->addText('The schema file does not exist.  Run this command with the "add" argument to create one (only a stub).');
      $clw->write();
      exit;
    }

    $clw->writeHeader();

    if (!$forced)
    {
      echo "\nWARNING:  IF YOU CONTINUE, ALL TABLES IN YOUR DATABASE WILL BE ERASED!";
      echo "\nDO YOU WANT TO CONTINUE? [y/N] ";
      $answer = fgets(STDIN);
      $answer = trim($answer);
      $answer = strtolower($answer);
      if (empty($answer) || substr($answer, 0, 1) == 'n')
      {
        echo "\nABORTED!\n\n";
        $clw->writeFooter();
        exit;
      }
    }

    echo "\n";
    $this->build();

    $clw->writeFooter();
    exit;

  }

  /**
  * Does the actual task of destroying and rebuilding the database from the ground up.
  *
  * @uses MpmSchema::destroy()
  * @uses MpmSchema::reloadMigrations()
  * @uses MpmSchema::build()
  * @uses MpmLatestController::doAction()
  * @uses MPM_DB_PATH
  *
  * @return void
  */
  public function build()
  {
    require_once(MPM_DB_PATH . 'schema.php');
    $obj = new MpmInitialSchema();
    $obj->destroy();
    echo "\n";
    $obj->reloadMigrations();
    echo "\n", 'Building initial database schema... ';
    $obj->build();
    echo 'done.', "\n\n", 'Applying migrations... ';
    $obj = new MpmLatestController();
    $obj->doAction(true);
    echo "\n\n", 'Database build complete.', "\n";
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
    $obj->addText('./migrate.php build [--force|add]');
    $obj->addText(' ');
    $obj->addText('This command is used to build the database.  If a schema.php file is found in the migrations directory, the MpmSchema::Build() method will be called.  Then, all migrations will be run against the database.');
    $obj->addText(' ');
    $obj->addText('Use the "add" argument to create an empty stub for the schema.php file.  You can then add your own query statements.');
    $obj->addText(' ');
    $obj->addText('If you use the "--force" argument instead of the "add" argument, you will not be prompted to confirm the action (good for scripting a build process).');
    $obj->addText(' ');
    $obj->addText('WARNING: THIS IS A DESTRUCTIVE ACTION!!  BEFORE THE DATABASE IS BUILT, ALL TABLES CURRENTLY IN THE DATABASE ARE REMOVED!');
    $obj->addText(' ');
    $obj->addText('Valid Example:');
    $obj->addText('./migrate.php add', 4);
    $obj->write();
  }

}

?>
