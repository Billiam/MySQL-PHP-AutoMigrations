<?php

require_once(dirname(__FILE__) . '/../../lib/init.php');

class TestOfMpmStringHelper extends UnitTestCase
{
	
	public function testGetTimestampFromFilename()
	{
		$test_data = array
		(
			'2009_01_01_09_31_23.php' => '2009-01-01T09:31:23', // valid filename, valid date
			'2008_02_27_10_42_59.php' => '2008-02-27T10:42:59', // valid filename, valid date
			'1973_12_31_16_01_00' => '1973-12-31T16:01:00', // valid filename, valid date
			'2054_13_01_00_00_00.php' => null, // valid filename, invalid date
			'1922_06_31_10_23_19.php' => null, // valid filename, invalid date
			'2001_02_30_18_23_45.php' => null, // valid filename, invalid date
			'2323_23_23_23_23.php'    => null, // invalid filename, invalid date
			'3434_34_34_34_43.p'      => null, // invalid filename, invalid date
		);
		
		foreach ($test_data as $in => $out)
		{
			$this->assertEqual($out, MpmStringHelper::getTimestampFromFilename($in));
		}
	}
	
}


?>