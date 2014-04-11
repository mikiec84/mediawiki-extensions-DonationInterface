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

require_once __DIR__ . '/TestConfiguration.php';
require_once dirname( __FILE__ ) . '/includes/test_gateway/test.adapter.php';
require_once dirname( __FILE__ ) . '/includes/test_form/test.gateway.forms.php';
require_once dirname( __FILE__ ) . '/includes/test_request/test.request.php';

/**
 * @group		Fundraising
 * @group		QueueHandling
 * @group		ClassMethod
 * @group		ListenerAdapter
 *
 * @category	UnitTesting
 * @package		Fundraising_QueueHandling
 */
abstract class DonationInterfaceTestCase extends MediaWikiTestCase {
	protected $backupGlobalsBlacklist = array(
		'wgHooks',
	);

	/**
	 * Returns an array of the vars we expect to be set before people hit payments.
	 * @var array
	 */
	public $initial_vars = array (
		'ffname' => 'testytest',
		'referrer' => 'www.yourmom.com', //please don't go there.
		'currency_code' => 'USD',
	);

	/**
	 * This will be set by a test method with the adapter object.
	 *
	 * @var GatewayAdapter	$gatewayAdapter
	 */
	protected $gatewayAdapter;

	public function __construct() {

		//Just in case you got here without running the configuration...
		global $wgDonationInterfaceTestMode;
		$wgDonationInterfaceTestMode = true;

		$adapterclass = TESTS_ADAPTER_DEFAULT;
		$this->testAdapterClass = $adapterclass;

		parent::__construct();
	}

	protected function setUp() {
		parent::setUp();
	}

	protected function tearDown() {
		$this->resetAllEnv();
		parent::tearDown();
	}

	/**
	 * buildRequestXmlForGlobalCollect
	 *
	 * @todo
	 * - there are many cases to this that need to be developed.
	 * - Do not consider this a complete test!
	 *
	 * @covers GatewayAdapter::__construct
	 * @covers GatewayAdapter::setCurrentTransaction
	 * @covers GatewayAdapter::buildRequestXML
	 */
	public function buildRequestXmlForGlobalCollect( $optionsForTestData, $options ) {

		global $wgDonationInterfaceTest;
		
		$wgDonationInterfaceTest = true;

		$this->gatewayAdapter = $this->getFreshGatewayObject( $options );

		$this->gatewayAdapter->setCurrentTransaction('INSERT_ORDERWITHPAYMENT');

		$request = trim( $this->gatewayAdapter->_buildRequestXML() );

		$expected = $this->getExpectedXmlRequestForGlobalCollect( $optionsForTestData, $options );
		
		$this->assertEquals( $expected, $request, 'The constructed XML for payment_method [' . $optionsForTestData['payment_method'] . '] and payment_submethod [' . $optionsForTestData['payment_submethod'] . '] does not match our expected request.' );
	}

	/**
	 *
	 * @param string $country The country we want the test user to be from.
	 * @return array Donor data to use
	 * @throws MWException when there is no data available for the requested country
	 */
	public function getDonorTestData( $country = '' ) {
		$donortestdata = array (
			'US' => array ( //default
				'city' => 'San Francisco',
				'state' => 'CA',
				'zip' => '94105',
				'currency_code' => 'USD',
				'street' => '123 Fake Street',
				'fname' => 'Firstname',
				'lname' => 'Surname',
				'amount' => '1.55',
				'language' => 'en',
			),
			'ES' => array (
				'city' => 'Barcelona',
				'state' => 'XX',
				'zip' => '0',
				'currency_code' => 'EUR',
				'street' => '123 Calle Fake',
				'fname' => 'Nombre',
				'lname' => 'Apellido',
				'amount' => '1.55',
				'language' => 'es',
			),
			'NO' => array (
				'city' => 'Oslo',
				'state' => 'XX',
				'zip' => '0',
				'currency_code' => 'EUR',
				'street' => '123 Fake Gate',
				'fname' => 'Fornavn',
				'lname' => 'Etternavn',
				'amount' => '1.55',
				'language' => 'no',
			),
		);
		//default to US
		if ( $country === '' ) {
			$country = 'US';
		}

		if ( array_key_exists( $country, $donortestdata ) ) {
			$donortestdata = array_merge( $this->initial_vars, $donortestdata[$country] );
			$donortestdata['country'] = $country;
			return $donortestdata;
		}
		throw new MWException( __FUNCTION__ . ": No donor data for country '$country'" );
	}

	/**
	 * Get the expected XML request from GlobalCollect
	 *
	 * @param $optionsForTestData
	 * @param array $options
	 * @return string    The expected XML request
	 */
	public function getExpectedXmlRequestForGlobalCollect( $optionsForTestData, $options = array() ) {
		global $wgRequest, $wgServer, $wgArticlePath, $wgDonationInterfaceThankYouPage;

		$orderId = $this->gatewayAdapter->getData_Unstaged_Escaped( 'order_id' );
		$merchantref = $this->gatewayAdapter->_getData_Staged( 'contribution_tracking_id' );
		//@TODO: WHY IN THE NAME OF ZARQUON are we building XML in a STRING format here?!?!?!!!1one1!?. Great galloping galumphing giraffes.
		$expected  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$expected .= '<XML>';
		$expected .= 	'<REQUEST>';
		$expected .= 		'<ACTION>INSERT_ORDERWITHPAYMENT</ACTION>';
		$expected .= 		'<META><MERCHANTID>' . $this->gatewayAdapter->getGlobal( 'MerchantID' ) . '</MERCHANTID>';

		if ( isset( $wgRequest ) ) {
			$expected .=		'<IPADDRESS>' . $wgRequest->getIP() . '</IPADDRESS>';
		}
		
		$expected .=			'<VERSION>1.0</VERSION>';
		$expected .=		'</META>';
		$expected .= 		'<PARAMS>';
		$expected .= 			'<ORDER>';
		$expected .= 				'<ORDERID>' . $orderId . '</ORDERID>';
		$expected .= 				'<AMOUNT>' . $options['amount'] * 100 . '</AMOUNT>';
		$expected .= 				'<CURRENCYCODE>' . $options['currency_code'] . '</CURRENCYCODE>';
		$expected .= 				'<LANGUAGECODE>' . $options['language'] . '</LANGUAGECODE>';
		$expected .= 				'<COUNTRYCODE>' . $options['country'] . '</COUNTRYCODE>';
		$expected .= '<MERCHANTREFERENCE>' . $merchantref . '</MERCHANTREFERENCE>';

		if ( isset( $wgRequest ) ) {
			$expected .=			'<IPADDRESSCUSTOMER>' . $wgRequest->getIP() . '</IPADDRESSCUSTOMER>';
		}

		$expected .=				'<EMAIL>' . TESTS_EMAIL . '</EMAIL>';
		$expected .= 			'</ORDER>';
		$expected .= 			'<PAYMENT>';
		$expected .= 				'<PAYMENTPRODUCTID>' . $optionsForTestData['payment_product_id'] . '</PAYMENTPRODUCTID>';
		$expected .= 				'<AMOUNT>' . $options['amount'] * 100 . '</AMOUNT>';
		$expected .= 				'<CURRENCYCODE>' . $options['currency_code'] . '</CURRENCYCODE>';
		$expected .= 				'<LANGUAGECODE>' . $options['language'] . '</LANGUAGECODE>';
		$expected .= 				'<COUNTRYCODE>' . $options['country'] . '</COUNTRYCODE>';
		$expected .= 				'<HOSTEDINDICATOR>1</HOSTEDINDICATOR>';
		$expected .= 				'<RETURNURL>' . $wgDonationInterfaceThankYouPage . '/' . $options['language'] . '</RETURNURL>';
		$expected .=				'<AUTHENTICATIONINDICATOR>0</AUTHENTICATIONINDICATOR>';
		$expected .= 				'<FIRSTNAME>' . $options['fname'] . '</FIRSTNAME>';
		$expected .= 				'<SURNAME>' . $options['lname'] . '</SURNAME>';
		$expected .= 				'<STREET>' . $options['street'] . '</STREET>';
		$expected .= 				'<CITY>' . $options['city'] . '</CITY>';
		$expected .= 				'<STATE>' . $options['state'] . '</STATE>';
		$expected .= 				'<ZIP>' . $options['zip'] . '</ZIP>';
		$expected .= '<EMAIL>' . TESTS_EMAIL . '</EMAIL>';

		// Set the issuer id if it is passed.
		if ( isset( $optionsForTestData['descriptor'] ) ) {
			$expected .= '<DESCRIPTOR>' . $optionsForTestData['descriptor'] . '</DESCRIPTOR>';
		}

		// Set the issuer id if it is passed.
		if ( isset( $optionsForTestData['issuer_id'] ) ) {
			$expected .= 				'<ISSUERID>' . $optionsForTestData['issuer_id'] . '</ISSUERID>';
		}


		// If we're doing Direct Debit...
		//@TODO: go ahead and split this out into a "Get the direct debit I_OWP XML block function" the second this gets even slightly annoying.
		if ( $optionsForTestData['payment_method'] === 'dd' ) {
			$expected .= '<DATECOLLECT>' . gmdate( 'Ymd' ) . '</DATECOLLECT>'; //is this cheating? Probably.
			$expected .= '<ACCOUNTNAME>' . $optionsForTestData['account_name'] . '</ACCOUNTNAME>';
			$expected .= '<ACCOUNTNUMBER>' . $optionsForTestData['account_number'] . '</ACCOUNTNUMBER>';
			$expected .= '<BANKCODE>' . $optionsForTestData['bank_code'] . '</BANKCODE>';
			$expected .= '<BRANCHCODE>' . $optionsForTestData['branch_code'] . '</BRANCHCODE>';
			$expected .= '<BANKCHECKDIGIT>' . $optionsForTestData['bank_check_digit'] . '</BANKCHECKDIGIT>';
			$expected .= '<DIRECTDEBITTEXT>' . $optionsForTestData['direct_debit_text'] . '</DIRECTDEBITTEXT>';
		}

		$expected .= 			'</PAYMENT>';
		$expected .= 		'</PARAMS>';
		$expected .= 	'</REQUEST>';
		$expected .= '</XML>';
		
		return $expected;
		
	}

	/**
	 * Get a fresh gateway object of the type specified in the variable
	 * $this->testAdapterClass.
	 * @param array $external_data If you want to shoehorn in some external
	 * data, do that here.
	 * @param array $setup_hacks An array of things that override stuff in
	 * the constructor of the gateway object that I can't get to without
	 * refactoring the whole thing. @TODO: Refactor the gateway adapter
	 * constructor.
	 * @return \class The new relevant gateway adapter object.
	 */
	function getFreshGatewayObject( $external_data = null, $setup_hacks = null ) {
		$p1 = null;
		if ( !is_null( $external_data ) ) {
			$p1 = array (
				'external_data' => $external_data,
			);
		}

		if ( !is_null( $setup_hacks ) ) {
			$p1 = array_merge( $p1, $setup_hacks );
		}

		$class = $this->testAdapterClass;
		$gateway = new $class( $p1 );

		return $gateway;
	}

	function resetAllEnv() {
		$_SESSION = array ( );
		$_GET = array ( );
		$_POST = array ( );

		$_SERVER = array ( );
		$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
		$_SERVER['HTTP_HOST'] = TESTS_HOSTNAME;
		$_SERVER['SERVER_NAME'] = TESTS_HOSTNAME;
		$_SERVER['SCRIPT_NAME'] = __FILE__;
	}

	/**
	 * Instantiates the $special_page_class with supplied $initial_vars,
	 * yoinks the html output from the output buffer, loads that into a
	 * DomDocument and performs asserts on the results per the checks
	 * supplied in $perform_these_checks.
	 * Optional: Asserts that the gateway has logged nothing at ERROR level.
	 *
	 * @param class $special_page_class A testing descendant of GatewayForm
	 * @param array $initial_vars Array that will be loaded straight into a
	 * test version of $wgRequest.
	 * @param array $perform_these_checks Array of checks to perform in the
	 * following format:
	 * $perform_these_checks[$element_id][$check_to_perform][$expected_result]
	 * So far, $check_to_perform can be either 'nodename' or 'innerhtml'
	 * @param boolean $fail_on_log_errors When true, this will fail the
	 * current test if there are entries in the gateway's error log.
	 */
	function verifyFormOutput( $special_page_class, $initial_vars, $perform_these_checks, $fail_on_log_errors = false ) {
		global $wgOut;

		$globals = array (
			'wgRequest' => new TestingRequest( $initial_vars, false ),
			'wgTitle' => Title::newFromText( 'nonsense is apparently fine' ),
		);
//		$this->setMwGlobals( 'wgRequest', new TestingRequest( $initial_vars, false ) );
		$this->setMwGlobals( $globals );
//		$wgRequest = new TestingRequest( $initial_vars, false );
//		$wgTitle = Title::newFromText( 'nonsense is apparently fine' );

		ob_start();
		$formpage = new $special_page_class();
		$formpage->execute( NULL );
		$formpage->getOutput()->output();
		$form_html = ob_get_contents();
		ob_end_clean();

		// In the event that something goes crazy, uncomment the next line for much easier local debugging
		// file_put_contents( '/tmp/xmlout.txt', $form_html );
		$log = $formpage->adapter->testlog;

		$this->assertTrue( is_array( $log ), "Missing the adapter testlog" );
		if ( $fail_on_log_errors ) {
			//for our purposes, an "error" is LOG_ERR or less.
			$checklogs = array (
				LOG_ERR => "Oops: We've got LOG_ERRors.",
				LOG_CRIT => "Critical errors!",
				LOG_ALERT => "Log Alerts!",
				LOG_EMERG => "Logs says the servers are actually on fire.",
			);

			$message = false;
			foreach ( $checklogs as $level => $levelmessage ) {
				if ( array_key_exists( $level, $log ) ) {
					$message = $levelmessage . ' ' . print_r( $log[$level], true ) . "\n";
				}
			}

			$this->assertFalse( $message, $message ); //ha
		}

		$dom_thingy = new DomDocument();
		if ( $form_html ) {
			$dom_thingy->loadHTML( $form_html );
		}

		foreach ( $perform_these_checks as $id => $checks ) {
			if ( $id == 'headers' ) {
				foreach ( $checks as $name => $expected ) {
					switch ( $name ) {
						case 'redirect':
							$actual = $formpage->getRequest()->response()->getheader( $name );
							$this->assertEquals( $expected, $actual, "Expected header '$name' to be '$expected', found '$redirect' instead." );
							break;
					}
				}
				continue;
			}

			$input_node = $dom_thingy->getElementById( $id );
			$this->assertNotNull( $input_node, "Couldn't find the '$id' element" );
			foreach ( $checks as $name => $expected ) {
				switch ( $name ) {
					case 'nodename':
						$this->assertEquals( $expected, $input_node->nodeName, "The node with id '$id' is not an '$expected'. It is a " . $input_node->nodeName );
						break;
					case 'innerhtml':
						$this->assertEquals( $expected, $input_node->nodeValue, "The node with id '$id' does not have value '$expected'. It has value " . $input_node->nodeValue );
						break;
				}
			}
		}

		//because do_transaction is totally expected to leave session artifacts...
//		$wgRequest = new FauxRequest();
	}

	/**
	 * Finds a relevant line/lines in a gateway's log array
	 * @param test adapter $gateway The gateway that should have the log line you're looking for.
	 * @param integer $log_level A standard level that the line should... get logged at.
	 * @param string $match A regex to match against the log lines.
	 * @return mixed The full log line that matches the $match, an array if there were multiples, or false if none were found.
	 */
	public function getGatewayLogMatches( $gateway, $log_level, $match ) {
		$log = $gateway->testlog;
		if ( !array_key_exists( $log_level, $log ) ) {
			return false;
		}

		$return = array ( );
		foreach ( $log[$log_level] as $line ) {
			if ( preg_match( $match, $line ) ) {
				$return[] = $line;
			}
		}

		if ( empty( $return ) ) {
			return false;
		}
		if ( sizeof( $return ) === 1 ) {
			return $return[0];
		}
		return $return;
	}

}