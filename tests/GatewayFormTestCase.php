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
 * @see DonationInterfaceTestCase
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'DonationInterfaceTestCase.php';

class TestingGatewayForm extends GatewayForm {
	public function __construct() {
		//nothing!
	}
	protected function handleRequest() {
		//also nothing!
	}
}

/**
 * @group Fundraising
 * @group DonationInterface
 * @group GatewayForm
 */
class GatewayFormTestCase extends MediaWikiTestCase {

	protected $form;
	protected $adapter;

	public function setUp() {
		$this->form = new TestingGatewayForm();
		$this->adapter = new TestingGenericAdapter();
		$this->adapter->addData( array(
			'amount' => '120',
			'currency_code' => 'SGD' ) );
		$this->adapter->errorsForRevalidate[0] = array( 'currency_code' => 'blah' );
		$this->adapter->errorsForRevalidate[1] = array();
		$this->form->adapter = $this->adapter;
		TestingGenericAdapter::$fakeGlobals = array ( 'FallbackCurrency' => 'USD' );
		parent::setUp();
	}

	public function testFallbackWithNotification() {
		TestingGenericAdapter::$fakeGlobals['NotifyOnConvert'] = true;

		$this->form->validateForm();

		$manualErrors = $this->adapter->getManualErrors();
		$msg = $this->form->msg( 'donate_interface-fallback-currency-notice', 'USD' )->text();
		$this->assertEquals( $msg, $manualErrors['general'] );
		$this->assertEquals( 100, $this->adapter->getData_Unstaged_Escaped( 'amount' ) );
		$this->assertEquals( 'USD', $this->adapter->getData_Unstaged_Escaped( 'currency_code' ) );
	}

	public function testFallbackIntermediateConversion() {
		TestingGenericAdapter::$fakeGlobals['FallbackCurrency'] = 'NZD';
		TestingGenericAdapter::$fakeGlobals['NotifyOnConvert'] = true;

		$this->form->validateForm();

		$manualErrors = $this->adapter->getManualErrors();
		$msg = $this->form->msg( 'donate_interface-fallback-currency-notice', 'NZD' )->text();
		$this->assertEquals( $msg, $manualErrors['general'] );
		$this->assertEquals( 110, $this->adapter->getData_Unstaged_Escaped( 'amount' ) );
		$this->assertEquals( 'NZD', $this->adapter->getData_Unstaged_Escaped( 'currency_code' ) );
	}

	public function testFallbackWithoutNotification() {
		TestingGenericAdapter::$fakeGlobals['NotifyOnConvert'] = false;

		$this->form->validateForm();

		$manualErrors = $this->adapter->getManualErrors();
		$this->assertEquals( null, $manualErrors['general'] );
		$this->assertEquals( 100, $this->adapter->getData_Unstaged_Escaped( 'amount' ) );
		$this->assertEquals( 'USD', $this->adapter->getData_Unstaged_Escaped( 'currency_code' ) );
	}

	public function testFallbackAlwaysNotifiesIfOtherErrors() {
		TestingGenericAdapter::$fakeGlobals['NotifyOnConvert'] = false;
		$this->adapter->errorsForRevalidate[1] = array( 'amount' => 'bad amount' );

		$this->form->validateForm();

		$manualErrors = $this->adapter->getManualErrors();
		$msg = $this->form->msg( 'donate_interface-fallback-currency-notice', 'USD' )->text();
		$this->assertEquals( $msg, $manualErrors['general'] );
		$this->assertEquals( 100, $this->adapter->getData_Unstaged_Escaped( 'amount' ) );
		$this->assertEquals( 'USD', $this->adapter->getData_Unstaged_Escaped( 'currency_code' ) );
	}

	public function testNoFallbackForSupportedCurrency() {
		$this->adapter->errorsForRevalidate[0] = array( 'address' => 'blah' );

		$this->form->validateForm();

		$manualErrors = $this->adapter->getManualErrors();
		$this->assertEquals( null, $manualErrors['general'] );
		$this->assertEquals( 120, $this->adapter->getData_Unstaged_Escaped( 'amount' ) );
		$this->assertEquals( 'SGD', $this->adapter->getData_Unstaged_Escaped( 'currency_code' ) );
	}
}
