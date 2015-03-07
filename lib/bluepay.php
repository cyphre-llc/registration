<?php
namespace OCA\Registration;

use \OCP\Config;
use \OCP\Util;

class BluePay {
	private $post = array(
		'MASTER_ID' => '',
	);
	private $secretKey;

	protected $http_response;
	protected $response;

	/* constants */
	const POST_URL = 'https://secure.bluepay.com/interfaces/bp20post'; // the url to post to

	/* STATUS response constants */
	const STATUS_DECLINE = '0'; // DECLINE
	const STATUS_APPROVED = '1'; // APPROVED
	const STATUS_ERROR = 'E'; // ERROR

	/***
	 * __construct()
	 *
	 * Constructor method, sets the account, secret key, 
	 * and the mode properties. These will default to 
	 * the constant values if not specified.
	 */
	public function __construct()
	{
		$this->post['ACCOUNT_ID'] = \OC_Config::getValue('bp_account', '');
		$this->secretKey = \OC_Config::getValue('bp_secret', '');
		$this->post['MODE'] = \OC_Config::getValue('bp_mode', 'TEST');
	}

	/***
	* sale()
	 *
	 * Will perform a SALE transaction with the amount
	 * specified.
	 */
	public function setSale($amount, $tax = "")
	{
		$this->post['TRANS_TYPE'] = "SALE";
		$this->post['AMOUNT_TAX'] = self::formatAmount($tax);
		$this->post['AMOUNT'] = self::formatAmount($amount);
	}

	/***
	 * rebCancel()
	 *
	 * Will cancel a rebilling cycle.
	 * Will only work in bp10emu.
	 */
	public function cancelRebill($rebillId)
	{
		$this->post['TRANS_TYPE'] = "REBCANCEL";
		$this->post['VERSION'] = "1";
		$this->post['MASTER_ID'] = $rebillId;
	}

	/***
	 * rebAdd()
	 *
	 * Will add a rebilling cycle.
	 */
	public function setRebill($amount, $date, $expr, $cycles = "")
	{
		$this->post['DO_REBILL']	= '1';
		$this->post['REB_AMOUNT']	= self::formatAmount($amount);
		$this->post['REB_FIRST_DATE']	= $date;
		$this->post['REB_EXPR']		= $expr;
		$this->post['REB_CYCLES']	= $cycles;
	}

	protected static function getValue($name, $cust)
	{
		return empty($cust[$name]) ? "" : $cust[$name];
	}

	/***
	 * setCustInfo()
	 *
	 * Sets the customer specified info.
	 */
	public function setCustInfo($cust)
	{
		$this->post['PAYMENT_ACCOUNT']	= self::getValue('account', $cust);
		$this->post['CARD_CVV2']	= self::getValue('cvv2', $cust);
		$this->post['CARD_EXPIRE']	= self::getValue('expire', $cust);
		$this->post['NAME1']		= self::getValue('name1', $cust);
		$this->post['NAME2']		= self::getValue('name2', $cust);
		$this->post['ADDR1']		= self::getValue('addr1', $cust);
		$this->post['ADDR2']		= self::getValue('addr2', $cust);
		$this->post['CITY']		= self::getValue('city', $cust);
		$this->post['STATE']		= self::getValue('state', $cust);
		$this->post['ZIP']		= self::getValue('zip', $cust);
		$this->post['COUNTRY']		= self::getValue('country', $cust);
		$this->post['PHONE']		= self::getValue('phone', $cust);
		$this->post['EMAIL']		= self::getValue('email', $cust);
		$this->post['CUSTOM_ID']	= self::getValue('customid1', $cust);
		$this->post['CUSTOM_ID2']	= self::getValue('customid2', $cust);
		$this->post['MEMO']		= self::getValue('memo', $cust);
	}

	/***
	 * formatAmount()
	 *
	 * Will format an amount value to be in the
	 * expected format for the POST.
	 */
	protected static function formatAmount($amount)
	{
		return sprintf("%01.2f", (float)$amount);
	}

	/***
	 * calcTPS()
	 *
	 * Calculates & returns the tamper proof seal md5.
	 */
	protected function calcTPS()
	{
		$hashstr = $this->secretKey . $this->post['ACCOUNT_ID'] .
			$this->post['TRANS_TYPE'] . $this->post['AMOUNT'] .
			$this->post['MASTER_ID'] . $this->post['NAME1'] .
			$this->post['PAYMENT_ACCOUNT'];

		return bin2hex( md5($hashstr, true) );
	}

	/***
	 * process()
	 *
	 * Will first generate the tamper proof seal, then 
	 * populate the POST query, then send it, and store 
	 * the response, and finally parse the response.
	 */
	public function process()
	{
		/* calculate the tamper proof seal */
		$this->post['TAMPER_PROOF_SEAL'] = $this->calcTPS();

		/* perform the transaction */
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, self::POST_URL); // Set the URL
		curl_setopt($ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0"); // Cosmetic
		curl_setopt($ch, CURLOPT_POST, 1); // Perform a POST
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Turns off verification of the SSL certificate.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // If not set, curl prints output to the browser
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->post));

		$this->http_response = curl_exec($ch);

		if ($this->http_response === FALSE) {
			$this->response = array(
				'STATUS' => self::STATUS_ERROR,
				'MESSAGE' => curl_error($ch),
			);
		} else {
			// TRANS_ID, STATUS, AVS, CVV2, AUTH_CODE, MESSAGE, REBID
			parse_str($this->http_response, $this->response);
		}

		curl_close($ch);
	}

	public function transId() { return $this->response['TRANS_ID']; }
	public function status() { return $this->response['STATUS']; }
	public function message() { return $this->response['MESSAGE']; }
	public function rebillId() { return $this->response['REBID']; }
}
