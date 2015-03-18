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
			    \OC_Mail::send($_POST['email'], 'Cyphre User', $l->t('Verify your Cyphre Storage registration request'), $msg, $from, 'cyphre');
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

		$tierid;
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
			$expire = sprintf("%02d%02d", self::myval('cc_expmonth', $post),
				substr(self::myval('cc_expyear', $post), -2));

			$bp = new \OCA\Registration\BluePay();

			// TODO Tax will be the second arg here -- BenC
			$bp->setSale($tier['amount']);
			$bp->setRebill($tier['amount'], "1 Month", "1 Month");
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
				'address'	=> self::myval('address', $post),
				'address1'	=> self::myval('address1', $post),
				'city'	=> self::myval('city', $post),
				'state'	=> self::myval('state', $post),
			));

			$bp->process();

			if ($bp->status() != $bp::STATUS_APPROVED) {
				throw new \Exception('Payment failed: ' . $bp->message());
				return false;
			}

			$rebill_id = $bp->rebillId();
		}

		$config = \OC::$server->getConfig();

		$config->setUserValue($uid, 'registration', 'tierid', $tier['tierid']);

		if ($tier['amount'] > 0) {
			$config->setUserValue($uid, 'registration', 'cardnum', substr($post['cc_cardnum'], -4));
			$config->setUserValue($uid, 'registration', 'expire', $expire);
			$config->setUserValue($uid, 'registration', 'firstname', $post['firstname']);
			$config->setUserValue($uid, 'registration', 'lastname', $post['lastname']);
			$config->setUserValue($uid, 'registration', 'address', $post['address']);
			$config->setUserValue($uid, 'registration', 'address1', $post['address1']);
			$config->setUserValue($uid, 'registration', 'city', $post['city']);
			$config->setUserValue($uid, 'registration', 'state', $post['state']);
			$config->setUserValue($uid, 'registration', 'zip', $post['zip']);
			$config->setUserValue($uid, 'registration', 'country', $post['country']);
			$config->setUserValue($uid, 'registration', 'rate', $tier['amount']);
			$config->setUserValue($uid, 'registration', 'rebill_id', $rebill_id);
		}

		/* Set Quota. A NULL value for quota means unlimited */
		$size = '';
		if (!empty($tier['size']))
			$size = $tier['size'] . ' GB';
		$config->setUserValue($uid, 'files', 'quota', $size);

		return true;
	}

	public static function registerForm($args) {
		$l = \OC_L10N::get('core');
		$email = self::verifyToken($args['token']);

		if ( $email !== false ) {
			self::displayRegisterForm(array(), array(), $email);
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
}

?>
