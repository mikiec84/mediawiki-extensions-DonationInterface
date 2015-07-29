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
 * 
 * @group Fundraising
 * @group DonationInterface
 * @group GlobalCollect
 * @group Recurring
 */
class DonationInterface_Adapter_GlobalCollect_RecurringTest extends DonationInterfaceTestCase {

	/**
	 * @param $name string The name of the test case
	 * @param $data array Any parameters read from a dataProvider
	 * @param $dataName string|int The name or index of the data set
	 */
	function __construct( $name = null, array $data = array(), $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );
		$this->testAdapterClass = 'TestingGlobalCollectAdapter';
	}

	public function setUp() {
		parent::setUp();

		$this->setMwGlobals( array(
			'wgGlobalCollectGatewayEnabled' => true,
		) );
	}

	function tearDown() {
		TestingGlobalCollectAdapter::clearGlobalsCache();
		parent::tearDown();
	}

	/**
	 * Can make a recurring payment
	 *
	 * @covers GlobalCollectAdapter::transactionRecurring_Charge
	 */
	public function testRecurringCharge() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency_code' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		// FIXME: I don't understand whether the literal code should correspond to anything in GC
		$gateway->setDummyGatewayResponseCode( 'recurring' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertTrue( $result->getCommunicationStatus() );
		$this->assertRegExp( '/SET_PAYMENT/', $result->getRawResponse() );
	}

	/**
	 * Can make a recurring payment
	 *
	 * @covers GlobalCollectAdapter::transactionRecurring_Charge
	 */
	public function testDeclinedRecurringCharge() {
		$init = array(
			'amount' => '2345',
			'effort_id' => 2,
			'order_id' => '9998890004',
			'currency_code' => 'EUR',
			'payment_product' => '',
		);
		$gateway = $this->getFreshGatewayObject( $init );

		$gateway->setDummyGatewayResponseCode( 'recurring-NOK' );

		$result = $gateway->do_transaction( 'Recurring_Charge' );

		$this->assertEquals( 1, count( $gateway->curled ), 'Should not make another reqest after DO_PAYMENT fails' );
		$this->assertEquals( FinalStatus::FAILED, $gateway->getFinalStatus() );
	}
}