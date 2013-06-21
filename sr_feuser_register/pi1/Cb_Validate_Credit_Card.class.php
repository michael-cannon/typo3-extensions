<?php
/**
* class Cb_Validate_Credit_Card
*
* Validates Credit Card Numbers and Expiration Dates
* using the MOD10 Algorithm
*
* @author Micahel Cannon, michael@peimic.com
* @version $Id: Cb_Validate_Credit_Card.class.php,v 1.1.1.1 2010/04/15 10:04:04 peimic.comprock Exp $
*
* @access public
*/

require_once( dirname( __FILE__ ) . '/Validate_Credit_Card.class.php');

//Error Codes
define('ERR_INVALID_TYPE',		-8);

class Cb_Validate_Credit_Card extends Validate_Credit_Card
{

	/**
	* Cb_Validate_Credit_Card::Cb_Validate_Credit_Card()
	*
	* Constructor
	*
	*/
	function Cb_Validate_Credit_Card ()
	{
		parent::Validate_Credit_Card();

		$this->error_text[ERR_INVALID_TYPE]	= 
								'Card type not allowed.';
	}

	/**
	* Cb_Validate_Credit_Card::is_valid_card()
	*
	* Validates credit card number and expiration date
	*
	* @param string $card_number
	* @param string $exp_month
	* @param string $exp_year
	* @param string $cc_given_type
	* @param array $allowed_types
	* @return integer CC_SUCCESS or error code
	*
	* @see is_valid_number()
	* @see is_valid_expiration()
	*
	* @access public
	*/
	function is_valid_card ( $card_number, $exp_month, $exp_year, 
		$cc_type, $allowed_types )
	{
		$card_type				= $this->get_card_type($card_number);

		// if given doesn't match determined
		if ( $card_type != $this->cc_text_type[ $cc_type ] )
		{
			return ERR_BAD_TYPE_MATCH;
		}

		if ( in_array($card_type, $allowed_types) )
		{
			$ret = $this->is_valid_number ( $card_number, $card_type );

			if ( $ret != CC_SUCCESS )
			{
				return $ret;
			}

			$ret = $this->is_valid_expiration ( $exp_month, $exp_year );

			if ( $ret != CC_SUCCESS )
			{
				return $ret;
			}

			return CC_SUCCESS;
		}

		else
		{
			return ERR_INVALID_TYPE;
		}
	}
}  //END CLASS Cb_Validate_Credit_Card
?>
