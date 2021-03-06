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
use SmashPig\Tests\TestingContext;
use SmashPig\Tests\TestingProviderConfiguration;

/**
 *
 * @group Fundraising
 * @group DonationInterface
 * @group Ingenico
 * @group RealTimeBankTransfer
 */
class DonationInterface_Adapter_Ingenico_RealTimeBankTransferIdealTest extends BaseIngenicoTestCase {
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $bankPaymentProvider;

	public function setUp() {
		parent::setUp();
		$this->markTestSkipped( 'RTBT not implemented' );
		$config = TestingProviderConfiguration::createForProvider(
			'ingenico',
			$this->smashPigGlobalConfig
		);
		TestingContext::get()->providerConfigurationOverride = $config;

		$this->bankPaymentProvider = $this->getMockBuilder(
			'\SmashPig\PaymentProviders\Ingenico\BankPaymentProvider'
		)->disableOriginalConstructor()->getMock();

		$config->overrideObjectInstance( 'payment-provider/rtbt', $this->bankPaymentProvider );

		$this->bankPaymentProvider->method( 'getBankList' )
			->willReturn(
				array(
					'Test1234' => 'Test Bank 1234',
					'Test5678' => 'Test Bank 5678',
				)
			);

		$this->setMwGlobals(
			array(
				'wgIngenicoGatewayEnabled' => true,
				'wgDonationInterfaceAllowedHtmlForms' => array(
					'rtbt-ideal' => array(
						'gateway' => 'ingenico',
						'payment_methods' => array( 'rtbt' => 'rtbt_ideal' ),
						'countries' => array( '+' => 'NL' ),
						'currencies' => array( '+' => 'EUR' ),
					),
				),
			)
		);
	}

	/**
	 * Test for ideal form loading
	 */
	public function testIngenicoFormLoad_rtbt_Ideal() {
		$init = $this->getDonorTestData( 'NL' );
		unset( $init['order_id'] );
		$init['payment_method'] = 'rtbt';
		$init['ffname'] = 'rtbt-ideal';

		$assertNodes = array(
			'amount' => array(
				'nodename' => 'input',
				'value' => '1.55',
			),
			'currency' => array(
				'nodename' => 'select',
				'selected' => 'EUR',
			),
			'country' => array(
				'nodename' => 'input',
				'value' => 'NL',
			),
			'issuer_id' => array(
				'innerhtmlmatches' => '/Test Bank 1234/'
			)
		);

		$this->verifyFormOutput( 'IngenicoGateway', $init, $assertNodes, true );
	}

	/**
	 * testBuildRequestXmlWithIssuerId21
	 *
	 * Rabobank: 21
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId21() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 21,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId31
	 *
	 * ABN AMRO: 31
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId31() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 31,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId91
	 *
	 * Rabobank: 21
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId91() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 21,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId161
	 *
	 * Van Lanschot Bankiers: 161
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId161() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 161,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId511
	 *
	 * Triodos Bank: 511
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId511() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 511,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId721
	 *
	 * ING: 721
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId721() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 721,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId751
	 *
	 * SNS Bank: 751
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId751() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 751,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId761
	 *
	 * ASN Bank: 761
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId761() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 761,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	/**
	 * testBuildRequestXmlWithIssuerId771
	 *
	 * RegioBank: 771
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 * @covers GatewayAdapter::getData_Unstaged_Escaped
	 */
	public function testBuildRequestXmlWithIssuerId771() {
		$optionsForTestData = array(
			'form_name' => 'TwoStepAmount',
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'payment_product_id' => 809,
			'issuer_id' => 771,
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );
		unset( $options['payment_product_id'] );

		$this->buildRequestXmlForIngenico( $optionsForTestData, $options );
	}

	public function testFormAction() {
		$optionsForTestData = array(
			'payment_method' => 'rtbt',
			'payment_submethod' => 'rtbt_ideal',
			'issuer_id' => 771,
			// Email is required for RTBT.
			'email' => 'nobody@wikimedia.org',
		);

		// somewhere else?
		$options = $this->getDonorTestData( 'ES' );
		$options = array_merge( $options, $optionsForTestData );

		$this->gatewayAdapter = $this->getFreshGatewayObject( $options );

		$this->assertTrue( $this->gatewayAdapter->validatedOK() );

		$this->gatewayAdapter->do_transaction( "INSERT_ORDERWITHPAYMENT" );
		$action = $this->gatewayAdapter->getTransactionDataFormAction();
		$this->assertEquals( "url_placeholder", $action, "The formaction was not populated as expected (ideal)." );
	}

}
