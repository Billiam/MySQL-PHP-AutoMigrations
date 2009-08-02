<?php

define('MPM_PATH', realpath(dirname(__FILE__) . '/../'));

require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

require_once(dirname(__FILE__) . '/mpm_string_helper_test.php');

class UnitTests extends TestSuite
{
	
	public function __construct()
	{
		parent::__construct();
		$this->_label = "All Unit Tests";
		$this->addTestCase(new TestOfMpmStringHelper());
	}
	
}

?>