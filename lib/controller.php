<?php
namespace OCA\Registration;

class Controller {
	protected static function displayRegisterPage($errormsg, $entered) {
		\OCP\Template::printGuestPage('registration', 'register',
			array('errormsg' => $errormsg,
				'entered' => $entered));
	}

	/**
	 * @brief Renders the registration form
	 * @param $errormsgs numeric array containing error messages to displey
	 * @param $entered_data associative array containing previously entered data by user
	 * @param $email User email
	 */
	protected static function displayRegisterForm($errormsgs, $entered_data, $email) {
		\OCP\Template::printGuestPage('registration', 'form',
			array('errormsgs' => $errormsgs,
			'entered_data' => $entered_data,
			'email' => $email ));
	}

	public static function index($args) {
		self::displayRegisterPage(false, false);
	}

	

	/**
	 * @brief Send registration email to given address (check if regrequest or user with this email exists )
	 */
	public static function sendEmail($args) {
		$l = \OC_L10N::get('core');

		if ( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
			self::displayRegisterPage($l->t('Email address you entered is not valid'), true);
			return;
		}
		$email = $_POST['email'];
		
		// Check if user with this email already exists
		$query = \OC_DB::prepare('SELECT * FROM `*PREFIX*preferences` WHERE `configvalue` = ? ');
		$values=$query->execute(array($email))->fetchAll();
		$existing_email=(count($values)>0);

		if ( $existing_email ) {
			self::displayRegisterPage($l->t('A user with this email address already exists!'), true);
			return;
		}
	
		$token = self::savePendingRegistration($_POST['email']);
			
		if ( $token === false ) {
			//pending registration already exists in databasetable pending_regist
			self::displayRegisterPage($l->t('There is already a pending registration with this email'), true);
			return;
		} 
		elseif ( strlen($token) === 64 ) {
			$link = \OC_Helper::linkToRoute('registration.register.form',
				array('token' => $token));
			$link = \OC_Helper::makeURLAbsolute($link);
			$from = \OCP\Util::getDefaultEmailAddress('register');
			$tmpl = new \OCP\Template('registration', 'email');
			$tmpl->assign('link', $link, false);
			$msg = $tmpl->fetchPage();
			try {
			    \OC_Mail::send($_POST['email'], 'Cyphre User', $l->t('Cyphre Registration.'), $msg, $from, 'cyphre', 1);
			} catch (Exception $e) {
				\OC_Template::printErrorPage( 'A problem occurs during sending the e-mail please contact your administrator.');
			}
			self::displayRegisterPage('', true);
		}
	}

	public static function myval($key, $vars)
	{
		if (empty($vars[$key]))
			return '';
		return $vars[$key];
	}

	public static function postDelete($uid)
	{
		$config = \OC::$server->getConfig();
		$rebill_id = $config->setUserValue($uid, 'registration', 'rebill_id', null);
		if (!empty($rebill_id)) {
			$bp = new \OCA\Registration\BluePay();
			$bp->cancelRebill($rebill_id);
			$bp->process();
		}
	}

	public static function postCreateUser($uid, $pass)
	{
		$config = \OC::$server->getConfig();

		$tierid = $config->getUserValue($uid, 'registration', 'tierid', null);
		if (!empty($tierid))
			return true;

		$stmt = \OC_DB::prepare('SELECT * FROM `*PREFIX*tier_table` WHERE tierid= ?');
		$result = $stmt->execute(array($tierid));
		if (!$result) {
			throw new \Exception('Invalid service level');
			return false;
		}

		$tier = $result->fetchRow();

		$config->setUserValue($uid, 'registration', 'tierid', $tier['tierid']);

		/* Set Quota. A NULL value for quota means unlimited */
		if (!empty($tier['size'])) {
			$size = $tier['size'] . ' GB';
			$config->setUserValue($uid, 'files', 'quota', $size);
		}

		return true;
	}

	public static function processTier($uid, $post, $checkuser = false)
	{
		/* This is also checked in createUser, but check here before
		 * go charging credit cards. */
		if ($checkuser and \OC_User::userExists($uid)) {
			throw new \Exception('User exists');
			return false;
		}

		/* XXX Verify information is valid format */
		/* XXX TODO Need to cleanly recover from errors here */

		$tierid = '1';
		if (!empty($post['tierid']))
			$tierid = $post['tierid'];

		$stmt = \OC_DB::prepare('SELECT * FROM `*PREFIX*tier_table` WHERE tierid= ?');
		$result = $stmt->execute(array($tierid));
		if (!$result) {
			throw new \Exception('Invalid service level');
			return false;
		}

		$tier = $result->fetchRow();

		\OCP\Util::writeLog('registration', serialize($tier), \OCP\Util::ERROR);

		$rebill_id = null;

		/* Only do this for tiers that require payment */
		if ($tier['amount'] > 0) {
			// AvaTax:
			$avatax = self::calSalesTax($post, false);

			if (!$avatax || array_key_exists('errorMsg', $avatax)) {
				$avatax['errorMsg'] = $avatax['errorMsg'] ? $avatax['errorMsg'] : 'Unable to determine sales tax amount';
				throw new \Exception($avatax['errorMsg']);
				return false;
			} else if (floatval($post['sales_tax_amount']) != floatval($avatax['taxamount'])) {
				$avatax['errorMsg'] = "Previewed sales_tax_amount and charged sales_tax_amount do not match.";
				throw new \Exception($avatax['errorMsg']);
				return false;
			}

			$expire = sprintf("%02d%02d", self::myval('cc_expmonth', $post),
				substr(self::myval('cc_expyear', $post), -2));

			$bp = new \OCA\Registration\BluePay();

			// TODO Tax will be the second arg here -- BenC
			$totalamount = $tier['amount'] + $avatax['taxamount'];
			$bp->setSale($totalamount, $avatax['taxamount']);
			$bp->setRebill($totalamount, "1 Month", "1 Month");

			$bp->setCustInfo(array(
				'account'	=> $uid,
				'email'		=> self::myval('email', $post),
				'memo'		=> $tier['description'],
				'name1'		=> self::myval('firstname', $post),
				'name2'		=> self::myval('lastname', $post),
				'zip'		=> self::myval('zip', $post),
				'account'	=> self::myval('cc_cardnum', $post),
				'cvv2'		=> self::myval('cc_ccv', $post),
				'expire'	=> $expire,
				'country'	=> self::myval('country', $post),
				'addr1' => self::myval('address', $post),
				'addr2' => self::myval('address1', $post),
				'city'  => self::myval('city', $post),
				'state' => self::myval('state', $post),

			));

			$bp->process();

			if ($bp->status() != $bp::STATUS_APPROVED) {
				throw new \Exception('Payment failed: ' . $bp->message());
				return false;
			}

			$rebill_id = $bp->rebillId();

			// Record AvaTax:
			$avatax = self::calSalesTax($post, true);

		}

		$config = \OC::$server->getConfig();

		$config->setUserValue($uid, 'registration', 'tierid', $tier['tierid']);

		if ($tier['amount'] > 0) {
			$config->setUserValue($uid, 'registration', 'cardnum', substr($post['cc_cardnum'], -4));
			$config->setUserValue($uid, 'registration', 'expire', $expire);
			$config->setUserValue($uid, 'registration', 'firstname', $post['firstname']);
			$config->setUserValue($uid, 'registration', 'lastname', $post['lastname']);
			$config->setUserValue($uid, 'registration', 'zip', $post['zip']);
			$config->setUserValue($uid, 'registration', 'country', $post['country']);
			$config->setUserValue($uid, 'registration', 'rate', $tier['amount']);
			$config->setUserValue($uid, 'registration', 'sales_tax_amount', $avatax['taxamount']);
			$config->setUserValue($uid, 'registration', 'rebill_id', $rebill_id);
			$config->setUserValue($uid, 'registration', 'address', $post['address']);
			$config->setUserValue($uid, 'registration', 'address1', $post['address1']);
			$config->setUserValue($uid, 'registration', 'city', $post['city']);
			$config->setUserValue($uid, 'registration', 'state', $post['state']);

		}

		/* Set Quota. A NULL value for quota means unlimited */
		$size = null;
		if (!empty($tier['size']))
			$size = $tier['size'] . ' GB';
		$config->setUserValue($uid, 'files', 'quota', $size);

		return true;
	}

	public static function registerForm($args) {
		$l = \OC_L10N::get('core');
		$email = self::verifyToken($args['token']);

		if ( $email !== false ) {
			self::displayRegisterForm(array(), array('token' => $args['token']), $email);
		} else {
			self::displayRegisterPage($l->t('Your registration request has expired or already been used, please make a new request below.'), false);
		}
	}

	/**
	 * @brief Create Useraccaunt (set email address in preferences/settings, delete registration request)
	 */

	public static function createAccount($args) {
		$l = \OC_L10N::get('core');
		$email = self::verifyToken($args['token']);

		if ( $email !== false ) {
			$query = \OC_DB::prepare('SELECT `requested` FROM `*PREFIX*pending_regist` WHERE `email` = ? ');
			$requested = $query->execute(array($email))->fetchOne();
			
			if ( time() - $requested > 86400 ) { // expired - delete from database
					$query = \OC_DB::prepare('DELETE FROM `*PREFIX*pending_regist` WHERE `email` = ? ');
					$deleted = $query->execute(array($email));
					self::displayRegisterPage($l->t('Your registration request has expired, please make a new request below.'), false);
			} 
			else {
				$caught = false;
				$post = array_merge($_POST, array('email' => $email));
				try {
					self::processTier($_POST['user'], $post, true);
					\OC_User::createUser($_POST['user'], $_POST['password']);	// create user now
					\OC_Group::addToGroup($_POST['user'] , 'selfregistered' );	// create default group for new selfregistered users
				} catch (\Exception $e) {
					self::displayRegisterForm(array($e->getMessage()), $_POST, $email);
					$caught = true;
				}
					// if successfully created the user - set preferences-settings-email to the given email adress (for lostpassword and userwiththisemail already exists check )
				if (!$caught) {
					\OC_Preferences::setValue($_POST['user'], "settings", "email", "$email");
					\OC_Preferences::setValue($_POST['user'], "settings", "tosagree", $_POST['tosagree']);
					// Enable adminRecovery by default:
			        $view = new \OC\Files\View('/');
			        $util = new \OCA\Encryption\Util($view, $_POST['user']);
				    $util->addRecoveryKeys();
			        if (!$util->setRecoveryForUser(true)) {
						\OCP\Util::writeLog('files_encryption', "Enable Admin_Recovery failed for New user '".$_POST['user']."'", \OCP\Util::ERROR);
					}

					\OCP\Template::printGuestPage('registration', 'message',
						array('success' => "you did it"));
					// delete request after account created
					$query = \OC_DB::prepare('DELETE FROM `*PREFIX*pending_regist` WHERE `email` = ? ');
					$deleted = $query->execute(array($email));
				}

				
			}
		} else {
			self::displayRegisterPage($l->t('Your registration request has expired or already been used, please make a new request below.'), false);
		}
	}



	/**
	 * @brief Save a registration request to database
	 * @param string $email Request from this email
	 * @return false if a request with the email already exists, returns the generated token when success
	 */
	public static function savePendingRegistration($email) {
		// Check if the email does exist
		$query = \OC_DB::prepare('SELECT `email` FROM `*PREFIX*pending_regist` WHERE `email` = ? ');
		$values=$query->execute(array($email))->fetchAll();
		$exists=(count($values)>0);
		if ( $exists ) {
			return false;
		} else {
			$query = \OC_DB::prepare( 'INSERT INTO `*PREFIX*pending_regist`'
				.' ( `email`, `token`, `requested`) VALUES( ?, ?, ? )' );
	
			$token = hash('sha256', \OC_Util::generateRandomBytes(30).\OC_Config::getValue('passwordsalt', ''));
			$query->execute(array( $email, $token, time() ));
			return $token;
		}
	}

	public static function verifyToken($token) {
		$query = \OC_DB::prepare('SELECT `email` FROM `*PREFIX*pending_regist` WHERE `token` = ? ');
		$email = $query->execute(array($token))->fetchOne();
		return \OC_DB::isError($email) ? false : $email;
	}

	/**
	 * @brief Pre config params before making Avalar Tax Service request
	 * @param $args = form's fields, $commit is attr. for Avalar request
	 * @return success ? $avatax hash : null
	 */
	public static function calSalesTax ($args, $commit = false) {
		require_once 'lib/base.php';

			$query = \OC_DB::prepare('SELECT * FROM `*PREFIX*tier_table` WHERE tierid= ?');
			$result = $query->execute(array($args['tierid']));
			if ($result) {
				$tier = $result->fetchRow();
				
				$inv = array();

				// Billing Header info:
				$docHeader = array();
				$docHeader['customerCode'] = $args['firstname'] . "_" . $args['lastname'];
				$docHeader['docDate'] = date("Y-m-d");
				$docHeader['docCode'] = $docHeader['customerCode'] . "-" . date("Ymd") . time();


				$inv['docHeader'] = $docHeader;

				// Billing amount & description:
				$line = new \OCA\Registration\Avatax\Line();

				$line->setLineNo("01");
				$line->setItemCode("STORAGE");
				$line->setQty(1);
				$line->setAmount($tier['amount']);
				$line->setOriginCode("01");
				$line->setDestinationCode("02");
				$line->setDescription("Cyphre monthly storage");

				// TaxCode to use for this item
				$line->setTaxCode(\OC::$server->getConfig()->getAppValue('avatax', 'storage_tax_code', 'SD021100'));

				$inv['line'] = $line;

				$address = new \OCA\Registration\Avatax\Address();
				$address->setAddressCode("02");

				// Use this (Texas) address for sales tax amount TEST:
				/*
				$address->setLine1("701 Brazos St.");
				$address->setLine2('Suite 1616');
				$address->setCity("Austin");
				$address->setRegion("TX");
				$address->setPostalCode("78701");
				*/

				// User input address:
				$address->setLine1($args['address']);
				$address->setCity($args['city']);
				$address->setRegion($args['state']);
				$address->setPostalCode($args['zip']);

				$inv['address'] = $address;

				$inv['commit'] = $commit;
				$avatax = self::avataxGetSalesTax ($inv);

				if ($avatax && array_key_exists('taxamount', $avatax)) {
					$avatax['amount'] = $tier['amount'];
				}
				return $avatax;

			} else { // oc_tier_table query error -> log?
				\OCP\Util::writeLog('registration', 'Missing oc_tier_table record', \OCP\Util::ERROR);
				return null;	// just quietly fail with busy msg
			}
	}

	/**
	 * @brief get sales tax amount from Avalar Tax Service request
	 * @param $inv = sales/invoice hash
	 * @return success ? $avatax hash : null
	 */
	public static function avataxGetSalesTax ($inv = array()) {
		if ( array_key_exists('docHeader', $inv)
				&& array_key_exists('line', $inv)
				&& array_key_exists('address', $inv) ) {

			// Avatax server config:
			$config = \OC::$server->getConfig();

			$serviceURL = $config->getAppValue('avatax', 'server_url', 'https://avatax.avalara.net/');
			$accountNumber = $config->getAppValue('avatax', 'account_number', '1100068945');
			$licenseKey = $config->getAppValue('avatax', 'license_key', 'C7849AD12D313B85');

	        // Tax service classes:
	        $taxSvc = new \OCA\Registration\Avatax\TaxServiceRest($serviceURL, $accountNumber, $licenseKey);
	        $getTaxRequest = new \OCA\Registration\Avatax\GetTaxRequest();

			// Servergy info:
			$getTaxRequest->setCompanyCode($config->getAppValue('avatax', 'company_code', 'SVY'));
			$getTaxRequest->setClient($config->getAppValue('avatax', 'client_code', 'AvaTaxSample'));

			$address = new \OCA\Registration\Avatax\Address();
			$address->setAddressCode("01");

			$address->setLine1($config->getAppValue('avatax', 'company_address1', '5900 S. Lake Forest Dr.'));
			$address->setLine2($config->getAppValue('avatax', 'company_address2', 'Suite 120'));
			$address->setCity($config->getAppValue('avatax', 'company_address_city', 'McKinney'));
			$address->setRegion($config->getAppValue('avatax', 'company_address_state', 'TX'));
			$address->setPostalCode($config->getAppValue('avatax', 'company_address_zip', '75070'));

			// Document Header:
			$getTaxRequest->setCustomerCode($inv['docHeader']['customerCode']);
			$getTaxRequest->setDocDate($inv['docHeader']['docDate']);
			$getTaxRequest->setDocCode($inv['docHeader']['docCode']);

			// Addresses:
			$getTaxRequest->setAddresses(array($address, $inv['address']));

			// Request attributes:
			$inv['commit'] = array_key_exists('commit', $inv) ? $inv['commit'] : false;
			if ($inv['commit']) {
				$getTaxRequest->setDocType(\OCA\Registration\Avatax\DocumentType::$SalesInvoice);
			}

			$getTaxRequest->setCommit($inv['commit']);
			$getTaxRequest->setCurrencyCode("USD");
			$getTaxRequest->setDetailLevel(\OCA\Registration\Avatax\DetailLevel::$Document);

			// Line Item:
			$getTaxRequest->setLines(array($inv['line']));

			// Get Tax from Avatax:
			$getTaxResult = $taxSvc->getTax($getTaxRequest);

			if($getTaxResult->getResultCode() == \OCA\Registration\Avatax\SeverityLevel::$Success) {

				return array('taxamount' => $getTaxResult->getTotalTax());

			} else { // avatax return error -> log?
				$errorMsg = "";
				foreach($getTaxResult->getMessages() as $message) {
					$errorMsg .= $message->getSeverity() . ": " . $message->getSummary() . ". ";
				}
				if (strpos($errorMsg, 'Unable to determine the taxing jurisdictions') !== false) {
					$errorMsg = 'Error: Invalid input address';
				}
				return (array('errorMsg' => $errorMsg));
			}
		} else { // Our config error -> log?
			\OCP\Util::writeLog('registration', 'avataxGetSalesTax call with invalid params', \OCP\Util::ERROR);
			return null;	// just quietly fail with busy msg
		}

	}
	//----------------------------------------------------------------------------------------------
}

?>
