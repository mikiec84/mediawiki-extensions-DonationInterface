<?php

class PayflowProGateway_Form_TwoColumnLetter extends PayflowProGateway_Form_TwoColumn {
	public $paypal = false; // true for paypal only version

	public function __construct( &$form_data, &$form_errors ) {
		global $wgOut, $wgScriptPath;
		
		// set the path to css, before the parent constructor is called, checking to make sure some child class hasn't already set this
		if ( !strlen( $this->getStylePath())) {
			$this->setStylePath( $wgScriptPath . '/extensions/DonationInterface/payflowpro_gateway/forms/css/TwoColumnLetter.css' );
		}
			
		parent::__construct( $form_data, $form_errors );
		
		// update the list of hidden fields we need to use in this form.
		$this->updateHiddenFields();
	}

	public function generateFormStart() {
		global $wgOut, $wgRequest;
		
		$this->paypal = $wgRequest->getBool( 'paypal', false );
		
		$form = parent::generateBannerHeader();
		
		$form .= Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-cc_form_container'));
		
		$form .= Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-cc_form_form', 'class' => 'payflowpro_gateway-cc_form_column'));
		
		$form .= Xml::openElement( 'div', array( 'id' => 'mw-creditcard' ) ); 
		
		// provide a place at the top of the form for displaying general messages
		if ( $this->form_errors['general'] ) {
			$form .= Xml::openElement( 'div', array( 'id' => 'mw-payflow-general-error' ));
			if ( is_array( $this->form_errors['general'] )) {
				foreach ( $this->form_errors['general'] as $this->form_errors_msg ) {
					$form .= Xml::tags( 'p', array( 'class' => 'creditcard-error-msg' ), $this->form_errors_msg );
				}
			} else {
				$form .= Xml::tags( 'p', array( 'class' => 'creditcard-error-msg' ), $this->form_errors_msg );
			}
			$form .= Xml::closeElement( 'div' );  // close div#mw-payflow-general-error
		}

		// open form
		$form .= Xml::openElement( 'div', array( 'id' => 'mw-creditcard-form' ) );
		
		// Xml::element seems to convert html to htmlentities
		$form .= "<p class='creditcard-error-msg'>" . $this->form_errors['retryMsg'] . "</p>";
		$form .= Xml::openElement( 'form', array( 'name' => 'payment', 'method' => 'post', 'action' => '', 'onsubmit' => 'return validate_form(this)', 'autocomplete' => 'off' ) );
		
		$form .= $this->generateBillingContainer();
		return $form;
	}
        
	public function generateFormEnd() {
		global $wgRequest, $wgOut;
		$form = '';
		
		$form .= $this->generateFormClose();

		$form .= Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-cc_form_letter', 'class' => 'payflowpro_gateway-cc_form_column'));
		$form .= Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-cc_form_letter_inside' ));
		
		$text_template = $wgRequest->getText( 'text_template' );
		// if the user has uselang set, honor that, otherwise default to the language set for the form defined by 'language' in the query string
		if ( $wgRequest->getText( 'language' )) $text_template .= '/' . $this->form_data[ 'language' ];
		
		$template = ( strlen( $text_template )) ? $wgOut->parse( '{{'.$text_template.'}}' ) : '';
		// if the template doesn't exist, prevent the display of the red link
		if ( preg_match( '/redlink\=1/', $template )) $template = NULL;
		$form .= $template;
		
		$form .= Xml::closeElement( 'div' ); // close div#payflowpro_gateway-cc_form_letter
		$form .= Xml::closeElement( 'div' ); // close div#payflowpro_gateway-cc_form_letter_inside
		return $form;
	}
	
	protected function generateBillingContainer() {
		$form = '';
		$form .= Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-personal-info' ));
		$form .= Xml::tags( 'h3', array( 'class' => 'payflow-cc-form-header','id' => 'payflow-cc-form-header-personal' ), wfMsg( 'payflowpro_gateway-make-your-donation' ));
		$form .= Xml::openElement( 'table', array( 'id' => 'payflow-table-donor' ) );
		$form .= $this->generateBillingFields();
		$form .= Xml::closeElement( 'table' ); // close table#payflow-table-donor
		$form .= Xml::closeElement( 'div' ); // close div#payflowpro_gateway-personal-info

		return $form;
	}

	protected function generateBillingFields() {
		global $wgScriptPath, $wgPayflowGatewayPaypalURL;
		$scriptPath = "$wgScriptPath/extensions/DonationInterface/payflowpro_gateway/includes";
		
		$form = '';
		
		// name	
		$form .= $this->getNameField();
		
		// email
		$form .= $this->getEmailField();
		
		//comment message
		$form .= '<tr>';
		$form .= '<td colspan="2">';
		$form .= Xml::tags( 'p', array(), wfMsg( 'donate_interface-comment-message' ));
		$form .= '</td>';
		$form .= '</tr>';
		
		//comment
		$form .= '<tr>';
		$form .= '<td class="label">' . Xml::label( wfMsg('payflowpro_gateway-comment'), 'comment' ) . '</td>';
		$form .= '<td>' . Xml::input( 'comment', '30', $this->form_data[ 'comment' ], array( 'type' => 'text', 'maxlength' => '200', 'class' => 'fullwidth' )) . '</td>';
		$form .= '</tr>';
		
		// anonymous
		$form .= $this->getCommentOptionField();

		// email agreement
		$form .= $this->getEmailOptField();
		
		// amount
		$form .= $this->getAmountField();
		
		if ( !$this->paypal ) {
			// PayPal button
			// make sure we have a paypal url set to redirect the user to before displaying the button
			if ( strlen( $wgPayflowGatewayPaypalURL )) {
				$form .= '<tr>';
				$form .= '<td class="label"></td>';
				$form .= '<td class="paypal-button">';
				$form .= Xml::hidden( 'PaypalRedirect', false );
				$form .= Xml::tags( 'div',
						array(),
						Xml::element( 'img', array( 'src' => $wgScriptPath . "/extensions/DonationInterface/payflowpro_gateway/includes/credit_card_logos2.gif" )) . '&nbsp;&nbsp;&nbsp;<a href="#" onclick="document.payment.PaypalRedirect.value=\'true\';document.payment.submit();"><img src="'.$scriptPath.'/donate_with_paypal.gif"/></a>'
					);
				$form .= '</td>';
				$form .= '</tr>';
			}
			
			// card number
			$form .= $this->getCardNumberField();
			
			// cvv
			$form .= $this->getCvvField();
			
			// expiry
			$form .= '<tr>';
			$form .= '<td class="label">' . Xml::label( wfMsg( 'payflowpro_gateway-donor-expiration' ), 'expiration' ) . '</td>';
			$form .= '<td>' . $this->generateExpiryMonthDropdown() . $this->generateExpiryYearDropdown() . '</td>';
			$form .= '</tr>';
	
			// street
			$form .= $this->getStreetField();
	
			// city
			$form .= $this->getCityField();
	
			// state
			$form .= '<tr>';
			$form .= '<td class="label">' . Xml::label( wfMsg( 'payflowpro_gateway-donor-state' ), 'state' ) . '</td>';
			$form .= '<td>' . $this->generateStateDropdown() . ' ' . wfMsg( 'payflowpro_gateway-state-in-us' ) . '<span class="creditcard-error-msg">' . '  ' . $this->form_errors['state'] . '</span></td>';
			$form .= '</tr>';
				
			// zip
			$form .= $this->getZipField();
			
			// country
			$form .= '<tr>';
			$form .= '<td class="label">' . Xml::label( wfMsg( 'payflowpro_gateway-donor-country' ), 'country' ) . '</td>';
			$form .= '<td>' . $this->generateCountryDropdown() . '<span class="creditcard-error-msg">' . '  ' . $this->form_errors['country'] . '</span></td>';
			$form .= '</tr>';
		}

		return $form;
	}
	
	public function generateFormSubmit() {
		// submit button
		$form = Xml::openElement( 'div', array( 'id' => 'payflowpro_gateway-form-submit'));
		$form .= Xml::openElement( 'div', array( 'id' => 'mw-donate-submit-button' )); 	
		if ( $this->paypal ) {
			$form .= Xml::hidden( 'PaypalRedirect', false );
			$form .= Xml::element( 'input', array( 'class' => 'input-button button-navyblue', 'value' => wfMsg( 'payflowpro_gateway-submit-button'), 'onclick' => 'document.payment.PaypalRedirect.value=\'true\';document.payment.submit();', 'type' => 'submit'));
		} else {
			$form .= Xml::element( 'input', array( 'class' => 'input-button button-navyblue', 'value' => wfMsg( 'payflowpro_gateway-submit-button'), 'onclick' => 'submit_form( this )', 'type' => 'submit'));
			$form .= Xml::closeElement( 'div' ); // close div#mw-donate-submit-button
			$form .= Xml::openElement( 'div', array( 'class' => 'mw-donate-submessage', 'id' => 'payflowpro_gateway-donate-submessage' ) ) .
				wfMsg( 'payflowpro_gateway-donate-click' ); 
		}
		$form .= Xml::closeElement( 'div' ); // close div#payflowpro_gateway-donate-submessage
		$form .= Xml::closeElement( 'div' ); // close div#payflowpro_gateway-form-submit
		
		return $form;
	}

	/**
	 * Update hidden fields to not set any comment-related fields
	 */
	public function updateHiddenFields() {
		$hidden_fields = $this->getHiddenFields();

		// make sure that the below elements are not set in the hidden fields
		$not_needed = array( 'comment-option', 'email-opt', 'comment' );

		foreach ( $not_needed as $field ) {
			unset( $hidden_fields[ $field ] );
		}
		
		$this->setHiddenFields( $hidden_fields );
	}
	
	/**
	 * Generate form closing elements
	 */
	public function generateFormClose() {
		$form = '';
		// add hidden fields			
		$hidden_fields = $this->getHiddenFields();
		foreach ( $hidden_fields as $field => $value ) {
			$form .= Xml::hidden( $field, $value );
		}
			
		$form .= Xml::closeElement( 'form' ); // close form 'payment'
		$form .= <<<EOT
<script type="text/javascript">
var fname = document.getElementById('fname');
var lname = document.getElementById('lname');
var amountOther = document.getElementById('amountOther');
if (fname.value == '') {
	fname.style.color = '#999999';
	fname.value = 'First';
}
if (lname.value == '') {
	lname.style.color = '#999999';
	lname.value = 'Last';
}
if (amountOther.value == '') {
	amountOther.style.color = '#999999';
	amountOther.value = 'Other';
}
</script>
EOT;
		$form .= $this->generateDonationFooter();
		if ( $this->paypal ) {
			$form .= Xml::tags( 'div', array( 'style' => 'margin-bottom: 8em;' ), '&nbsp;');
		}
		$form .= Xml::closeElement( 'div' ); //close div#mw-creditcard
		$form .= Xml::closeElement( 'div' ); //close div#payflowpro_gateway-cc_form_form
		$form .= Xml::closeElement( 'div' ); //close div#payflowpro_gateway-cc_form_container
		return $form;
	}
}
