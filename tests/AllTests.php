<?php
/**
 * Wikimedia Foundation
 *
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 */

/**
 * @see DonationInterface_Adapter_AllTests
 */
require_once 'Adapter/AllTests.php';
require_once 'DonationDataTestCase.php';
require_once 'DataValidatorTestCase.php';
require_once 'FormChooserTestCase.php';
require_once 'IntegrationTestCase.php';
require_once 'FraudFiltersTestCase.php';

/**
 * AllTests
 */
class DonationInterface_AllTests
{

	/**
	 * Run the main test and load any parameters if needed.
	 *
	 */
	public static function main()
	{
		$parameters = array();

		PHPUnit_TextUI_TestRunner::run( self::suite(), $parameters );
	}

	/**
	 * Run test suites
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite( 'Donation Interface Suite' );

		$suite->addTestSuite( 'DonationInterface_Adapter_AllTests' );
		$suite->addTestSuite( 'DonationInterface_DonationDataTestCase' );
		$suite->addTestSuite( 'DonationInterface_FormChooserTestCase' );
		$suite->addTestSuite( 'DonationInterface_IntegrationTestCase' );
		$suite->addTestSuite( 'DonationInterface_FraudFiltersTestCase' );
//		$suite->addTest(DonationInterface_Adapter_AllTests::suite());

		return $suite;
	}
}
