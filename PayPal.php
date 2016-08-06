<?php

class PayPal
{

	const IS_SANDBOX = true;
	const USER = 'YOUR_USER_ID';
	const PWD = 'YOUR_PWD';
	const SIGNATURE = 'YOUR_SIGNATURE';
	const VERSION = 86;
	const RECURRING_AMOUNT = 20;

	// const 

	public static function getPayPalNVP()
	{
		if (self::IS_SANDBOX)
			return 'https://api-3t.sandbox.paypal.com/nvp';

		return 'https://api-3t.paypal.com/nvp';
	}

	public static function getAuthURL($token)
	{
		if (self::IS_SANDBOX)
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='. $token;
		
		return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='. $token;

	}


	public static function getSubscriptionStatus($profileID)
	{
		$data = array(
		'USER' => self::USER,
		'PWD' => self::PWD,
		'SIGNATURE' => self::SIGNATURE,
		'VERSION' => self::VERSION,
		'METHOD' => 'GetRecurringPaymentsProfileDetails',
		'PROFILEID' => $profileID
		);
		// $data['PROFILEID'] = $profileID;

		$fieldString = http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::getPayPalNVP());
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		curl_close($ch);

		$rData = array();
		parse_str($response, $rData);
		return $rData;
	}

	public static function createPaymentURL()
	{
		$data = array(
		'USER' => self::USER,
		'PWD' => self::PWD,
		'SIGNATURE' => self::SIGNATURE,
		'VERSION' => self::VERSION,
		'L_BILLINGTYPE0' => 'RecurringPayments',
		'L_BILLINGAGREEMENTDESCRIPTION0' => 'YOUR_PRODUCT_NAME',
		'returnUrl' => self::SUCCESS_URL,
		'cancelUrl' => self::CANCEL_URL,
		'METHOD' => 'SetExpressCheckout',
		'PAYMENTREQUEST_0_AMT' => 0,
		'NOSHIPPING' => 1,
		'BRANDNAME' => 'AE SOFTWORKS',
		);

		$fieldString = http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::getPayPalNVP());
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		curl_close($ch);
		
		//parse data to get the token string, then return the json string 
		$rData = array();

		parse_str($response, $rData);

		if ($rData['ACK'] == "Success")
		{
			$toClient = array(
				'error_code' => '00',
				'message' => 'success',
				'authURL' => self::getAuthURL($rData['TOKEN'])
			);
		} else
		{
			$toClient = array(
				'error_code' => '01',
				'message' => $rData
			);
		}

		echo json_encode($toClient);
		return;
	}

	public static function getPayerID($token)
	{
		$data = array(
			'USER' => self::USER,
			'PWD' => self::PWD,
			'SIGNATURE' => self::SIGNATURE,
			'VERSION' => self::VERSION,
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN' => $token
		);


		$fieldString = http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::getPayPalNVP());
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fieldString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		curl_close($ch);

		$rData = array();

		parse_str($response, $rData);
		
		if ($rData['ACK'] == 'Success')
		{
			//also store the payerID in DB d
			return $rData;
		} else
		{
			return false;
		}

	}

	//get data from getPayerID (payerID and token)
	public static function createPaymentProfile($payerData)
	{
		$data = array(
			'USER' => self::USER,
			'PWD' => self::PWD,
			'SIGNATURE' => self::SIGNATURE,
			'VERSION' => self::VERSION,
			'METHOD' => 'CreateRecurringPaymentsProfile',
			'TOKEN' => $payerData['TOKEN'],
			'PAYERID' => $payerData['PAYERID'],
			'PROFILESTARTDATE' => $payerData['TIMESTAMP'],
			'DESC' => 'YOUR_PRODUCT_NAME',
			'BILLINGPERIOD' => 'Month',
			'BILLINGFREQUENCY' => 1,
			'AMT' => self::RECURRING_AMOUNT,
			'MAXFAILEDPAYMENTS' => 3,
			'TRIALBILLINGPERIOD' => 'Day',
			'TRIALBILLINGFREQUENCY' => 7,
			'TRIALTOTALBILLINGCYCLES' => 1,
			'TRIALAMT' => 0

		);

		$fieldString = http_build_query($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::getPayPalNVP());
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);

		curl_close($ch);

		$rData = array();

		parse_str($response, $rData);

		if ($rData['ACK'] == 'Success')
		{
			return $rData;
		}

		return false;



	}

}
