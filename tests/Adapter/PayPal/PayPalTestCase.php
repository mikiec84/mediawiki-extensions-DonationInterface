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
 */

/**
 * @see DonationInterfaceTestCase
 */
require_once dirname( dirname( dirname( __FILE__ ) ) ) . DIRECTORY_SEPARATOR . 'DonationInterfaceTestCase.php';

/**
 * 
 * @group Fundraising
 * @group DonationInterface
 * @group PayPal
 */
class DonationInterface_Adapter_PayPal_TestCase extends DonationInterfaceTestCase {

	public function __construct() {
		parent::__construct();
		$this->testAdapterClass = 'TestingPaypalAdapter';
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonate() {
		$init = $this->getDonorTestData();
		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->do_transaction( 'Donate' );
		parse_str( parse_url( $ret['redirect'], PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_donations',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => $init['country'], //this works because it's a US donor...
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "Donate" transaction not constructing the expected redirect URL' );
		$this->assertNull( $gateway->getData_Unstaged_Escaped( 'order_id' ), "Paypal order_id is not null, and we shouldn't be generating one" );
	}

	/**
	 * Integration test to verify that the DonateRecurring transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonateRecurring() {
		global $wgPaypalGatewayRecurringLength;

		$init = $this->getDonorTestData();
		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->do_transaction( 'DonateRecurring' );
		parse_str( parse_url( $ret['redirect'], PHP_URL_QUERY ), $res );

		$expected = array (
			'a3' => $init['amount'], //obviously.
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_xclick-subscriptions',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '0',
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
			'lc' => $init['country'], //this works because it's a US donor...
			't3' => 'M', //hard-coded in transaction definition
			'p3' => '1', //hard-coded in transaction definition
			'src' => '1', //hard-coded in transaction definition
			'srt' => $gateway->getGlobal( 'RecurringLength' ),
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
		);

		$this->assertEquals( $expected, $res, 'Paypal "DonateRecurring" transaction not constructing the expected redirect URL' );
	}

	/**
	 * Integration test to verify that the Donate transaction works as expected when all necessary data is present.
	 */
	function testDoTransactionDonateXclick() {
		$init = $this->getDonorTestData();
		$gateway = $this->getFreshGatewayObject( $init );

		$ret = $gateway->do_transaction( 'DonateXclick' );
		parse_str( parse_url( $ret['redirect'], PHP_URL_QUERY ), $res );

		$expected = array (
			'amount' => $init['amount'],
			'currency_code' => $init['currency_code'],
			'country' => $init['country'],
			'business' => 'phpunittesting@wikimedia.org',
			'cmd' => '_xclick',
			'item_name' => 'Donation to the Wikimedia Foundation',
			'item_number' => 'DONATE',
			'no_note' => '1', //hard-coded in transaction definition
			'custom' => $gateway->getData_Unstaged_Escaped( 'contribution_tracking_id' ),
//			'lc' => $init['country'], //Apparently, this was removed from our implementation, because 'CN' is weird.
			'cancel_return' => $gateway->getGlobal( 'ReturnURL' ),
			'return' => $gateway->getGlobal( 'ReturnURL' ),
			'no_shipping' => '1', //hard-coded in transaction definition
		);

		$this->assertEquals( $expected, $res, 'Paypal "DonateXclick" transaction not constructing the expected redirect URL' );
	}

}