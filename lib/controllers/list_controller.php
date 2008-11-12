<?php
/**
 * This file houses the MpmListController class.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmListController is used to display a list of the migrations.
 *
 * @package    mysql_php_migrations
 * @subpackage Controllers
 */
class MpmListController extends MpmController
{
	
	/**
	 * Determines what action should be performed and takes that action.
	 *
	 * @uses MpmListController::displayHelp()
	 * 
	 * @return void
	 */
	public function doAction()
	{
		$page = 1;
		$per_page = 30;
		
		if (isset($this->arguments[0]))
		{
			$page = $this->arguments[0];
		}
		if (isset($this->arguments[1]))
		{
			$per_page = $this->arguments[1];
		}
		
		if (!is_numeric($per_page))
		{
			$per_page = 30;
		}
		if (!is_numeric($page))
		{
			$page = 1;
		}
		
		$list = MpmListHelper::getList();
		$total = count($list);
		$total_pages = ceil($total / $per_page);
		
		$clw = MpmCommandLineWriter::getInstance();
		
		if ($total == 0)
		{
			$clw->addText('No migrations exist.');
		}
		else
		{
			$clw->addText("\t#\t\tTimestamp");
			$clw->addText("\t=========================================");
			$cur_index = ($page - 1) * $per_page;
			$last_index = $cur_index + $per_page;
			if ($last_index > $total)
			{
				$last_index = $total;
			}
			for ($i = $cur_index; $i < $last_index; $i++)
			{
				$obj = $list[$i];
				$clw->addText("\t" . $i . "\t\t" . $obj->timestamp);
			}
			$clw->addText(" ");
			$clw->addText("\tPage $page of $total_pages, $total migrations in all.");
		}
		
		$clw->write();
	}

	/**
	 * Displays the help page for this controller.
	 * 
	 * @uses MpmCommandLineWriter::addText()
	 * @uses MpmCommandLineWriter::write()
	 * 
	 * @return void
	 */
	public function displayHelp()
	{
		$obj = MpmCommandLineWriter::getInstance();
		$obj->addText('./migrate.php list [page] [per page]');
		$obj->addText(' ');
		$obj->addText('This command is used to display a list of all the migrations available.  Each migration is listed by number and timestamp.  You will need the migration number in order to perform an up or down migration.');
		$obj->addText(' ');
		$obj->addText('Since a project may have a large number of migrations, this command is paginated.  The page number is required.  If you do not enter it, the command will assume you want to see page 1.');
		$obj->addText(' ');
		$obj->addText('If you do not provide a per page argument, this command will default to 30 migrations per page.');
		$obj->addText(' ');
		$obj->addText('Valid Examples:');
		$obj->addText('./migrate.php list', 4);
		$obj->addText('./migrate.php list 2', 4);
		$obj->addText('./migrate.php list 1 15', 4);
		$obj->write();
	}
	
}

?>