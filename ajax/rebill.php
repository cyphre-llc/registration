<?php
/**
 * cyphre
 *
 * @author Ben Collins <ben.c@servergy.com>
 * @copyright 2015 Servergy, Inc.
 *
 * Based on http://www.bluepay.com/sites/default/files/documentation/BluePay_Rebill_Post/RebillingPostDoc.txt
 *
 * TODO Need to send more information to logs concerning errors (user, rebill id, etc)
 *
 */

try {
	require_once 'lib/base.php';

	// load all apps to get all api routes properly setup
	OC_App::loadApps();

	\OC::$session->close();

	// initialize a dummy memory session
	\OC::$session = new \OC\Session\Memory('');

	$logger = \OC_Log::$object;

	// Don't do anything if ownCloud has not been installed
	if (!OC_Config::getValue('installed', false)) {
		exit();
	}

	// These fields are required to continue
	if (empty($_POST['BP_STAMP']) or empty($_POST['BP_STAMP_DEF'])) {
		OC_JSON::error(array('message' => 'No BP_STAMP information'));
		exit();
	}

	// Verify the BP_STAMP, starting with our secret...
	$secret = \OCA\Registration\BluePay::SECRET_KEY;

	// Now concatenate the values of fields in BP_STAMP_DEF
	foreach (explode(" ", $_POST['BP_STAMP_DEF']) as $def) {
		if (!empty($_POST[$def]))
			$secret .= $_POST[$def];
	}

	// Check the md5...
	if (strtolower($_POST['BP_STAMP']) != strtolower(md5($secret))) {
		\OCP\Util::writeLog('rebill', 'BP_STAMP md5 does not verify', \OCP\Util::ERROR);
		OC_JSON::error();
		exit();
	}

	/*
	 * From here out, we trust the values sent to us, and that this is a
	 * valid request.
	 */

	// Find user by rebill_id
	$config = \OC::$server->getConfig();
	$user = $config->getUsersForValue('registration', 'rebill_id', $_POST['rebill_id']);

	if (empty($user)) {
		\OCP\Util::writeLog('rebill', 'No user found matching rebill_id' .
			$_POST['rebill_id'], \OCP\Util::ERROR);
		OC_JSON::error();
		exit();
        }

	$rate = $config->getValue($user[0], 'registration', 'rate', 0);
	// Check that amount matches rate
	if (floatval($rate) != floatval($_POST['rebilling_amount'])) {
		// Just a warning right now, do not fail
		\OCP\Util::writeLog('rebill', 'Mismatch between rate and rebill amount',
				    \OCP\Util::ERROR);
	}

	// TODO Check rebill status (auth or denied) and take appropriate action

	// TODO Store result in transaction table

	/*
	 * Currently the BluePay rebill does not make use of our success or
	 * failure, nor the HTTP response code (and definitely not our JSON
	 * codes).
	 */
	\OCP\Util::writeLog('rebill', 'Successfully charged account', \OCP\Util::INFO);
	OC_JSON::success();

	exit();

} catch (Exception $ex) {
	\OCP\Util::writeLog('rebill', $ex->getMessage(), \OCP\Util::FATAL);
	OC_JSON::error(array('message' => 'FATAL ERROR'));
}
