<?php

define('MPM_PATH', realpath(dirname(__FILE__) . '/../'));

require_once(dirname(__FILE__) . '/../simpletest/autorun.php');

// include all unit tests
require_once(dirname(__FILE__) . '/unit/unit_tests.php');


class AllTests extends TestSuite
{
	
	public function __construct()
	{
		parent::__construct();
		$this->_label = "ALL TESTS";
		$this->addTestCase(new UnitTests());
	}
	
}

?>