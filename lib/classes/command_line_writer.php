<?php
/**
 * This file houses the MpmCommandLineWriter class.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmCommandLineWriter is a Singleton class used to display output to the terminal.
 *
 * @package    mysql_php_migrations
 * @subpackage Classes
 */
class MpmCommandLineWriter
{
	/**
	 * A single, static instance of this class.  Singleton pattern.
	 *
	 * @var MpmCommandLineWriter
	 */
	static private $instance;
	
	/** 
	 * A collection of text items to write to the terminal.
	 *
	 * @var array
	 */
	private $text;
	
	/**
	 * The maximum width to wrap the text displayed.
	 *
	 * @var int
	 */
	public $maxWidth;
	
	/**
	 * Object constructor.
	 *
	 * @return MpmCommandLineWriter
	 */
	private function __construct()
	{
		$this->text = array();
		$this->maxWidth = 80;
	}
	
    /**
     * Returns a single static instance of this object.
     *
	 * @return MpmCommandLineWriter
     */
    static public function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new MpmCommandLineWriter();
        }
        return self::$instance;
    }

	/**
	 * Adds text to the collection to be displayed to the terminal.
	 *
	 * @param string $text   the text to add to the collection
	 * @param int    $indent the number of spaces to indent each line of this text
	 *
	 * @return void 
	 */
	public function addText($text, $indent = 0)
	{
		$obj = (object) array();
		$obj->text = $text;
		$obj->indent = $indent;
		$this->text[] = $obj;
	}
	
	/**
	 * Adds the header to the $text property.
	 *
	 * @return void
	 */
	private function addHeader()
	{
		$blank = (object) array();
		$blank->text = ' ';
		$blank->indent = 0;
		array_unshift($this->text, $blank);
		
		$max_line_len = $this->maxWidth;
		$bar = '';
		for ($i = 0; $i < $max_line_len; $i++)
		{
			$bar .= "*";
		}
		$bar_obj = (object) array();
		$bar_obj->text = $bar;
		$bar_obj->indent = 0;
		array_unshift($this->text, $bar_obj);
		
		$lines = array();
		$lines[] = ' ';
		$lines[] = "       __ __         __      __                             ";
		$lines[] = "|\/|  (_ /  \|   __ |__)|__||__) __ |\/|. _  _ _ |_. _  _  _";
		$lines[] = "|  |\/__)\_\/|__    |   |  ||       |  ||(_)| (_||_|(_)| )_)";
		$lines[] = "    /                                    _/                 ";
		
		$start = count($lines) - 1;
		for ($i = $start; $i >=0; $i--)
		{
			$line = $lines[$i];
			$a = $this->maxWidth - strlen($line);
			$indent = floor($a / 2);
			$txt = (object) array();
			$txt->text = $line;
			$txt->indent = 0;
			array_unshift($this->text, $txt);
		}
		//array_unshift($this->text, $bar_obj);
	}
	
	/**
	 * Adds the footer to the $text property.
	 *
	 * @return void
	 */
	private function addFooter()
	{
		$this->addText(' ');
		$max_line_len = $this->maxWidth;
		$bar = '';
		for ($i = 0; $i < $max_line_len; $i++)
		{
			$bar .= "*";
		}
		$bar_obj = (object) array();
		$bar_obj->text = $bar;
		$bar_obj->indent = 0;
		array_push($this->text, $bar_obj);
	}
	
	public function write()
	{
		$this->writeHeader();
		$this->writeText();
		$this->writeFooter();
	}
	
	/**
	 * Generates and echos the text to the terminal.
	 *
	 * @return string
	 */
	private function writeText()
	{
		$body = '';
		$all_lines = array();
		$max_line_len = $this->maxWidth;
		foreach ($this->text as $obj)
		{
			$wrap_point = $max_line_len - $obj->indent - 1;
			$indent = '';
			for ($i = 0; $i < $obj->indent; $i++)
			{
				$indent .= " ";
			}
			$lines_str = wordwrap($obj->text, $wrap_point, "---");
			$lines = explode("---", $lines_str);
			foreach ($lines as $line)
			{
				$all_lines[] = $indent . $line;
			}
		}
		foreach ($all_lines as $line)
		{
			//$body .= '* ';
		    $body .= $line;
			for ($i = 0; $i < $max_line_len - strlen($line); $i++)
			{
				$body .= " ";
			}
			//$body .= " *\n";
			$body .= "\n";
		}
		echo $body;
	}
	
	public function writeHeader()
	{
		$text = $this->text;
		$this->text = array();
		$this->addHeader();
		$this->writeText();
		$this->text = $text;
	}
	
	public function writeFooter()
	{
		$text = $this->text;
		$this->text = array();
		$this->addFooter();
		$this->writeText();
		$this->text = $text;
	}
		
}

?>