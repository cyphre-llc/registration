<?php
// If entered data:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$entered = $_POST;
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$entered = $_GET;
} else {
	$entered = array();
}

// Read user's settings from database to pass into template:
$config = \OC::$server->getConfig();
$uid = \OC_User::getUser();
$quota = $config->getUserValue($uid, 'files', 'quota');

$tierid = $config->getUserValue($uid, 'registration', 'tierid', 1);

if ($tierid < 2) {
	$stmt = \OC_DB::prepare('SELECT * FROM `*PREFIX*tier_table` WHERE tierid=2');
	$result = $stmt->execute();
	if (!$result) {
		throw new \Exception('Invalid service level');
		return false;
	}
	$tier = $result->fetchRow();
} else {
	$entered['firstname'] = $config->getUserValue($uid, 'registration', 'firstname', '');
	$entered['lastname'] = $config->getUserValue($uid, 'registration', 'lastname', '');
	$entered['cardnum'] = str_repeat('X', 12) . $config->getUserValue($uid, 'registration', 'cardnum', '');
	$entered['address'] = $config->getUserValue($uid, 'registration', 'address', '');
	$entered['address1'] = $config->getUserValue($uid, 'registration', 'address1', '');
	$entered['city'] = $config->getUserValue($uid, 'registration', 'city', '');
	$entered['state'] = $config->getUserValue($uid, 'registration', 'state', '');
	$entered['zip'] = $config->getUserValue($uid, 'registration', 'zip', '');
	$entered['country'] = $config->getUserValue($uid, 'registration', 'country', '');
	$tier = array();
}

// Initialized form's params:
$entered['firstname'] = array_key_exists('firstname',$entered) ? $entered['firstname'] : '';
$entered['lastname'] = array_key_exists('lastname',$entered) ? $entered['lastname'] : '';
$entered['cardnum'] = array_key_exists('cardnum',$entered) ? $entered['cardnum'] : '';
$entered['country'] = array_key_exists('country',$entered) ? $entered['country'] : 'US';
$entered['zip'] = array_key_exists('zip',$entered) ? $entered['zip'] : '';
$entered['address'] = array_key_exists('address',$entered) ? $entered['address'] : '';
$entered['address1'] = array_key_exists('address1',$entered) ? $entered['address1'] : '';
$entered['city'] = array_key_exists('city',$entered) ? $entered['city'] : '';
$entered['state'] = array_key_exists('state',$entered) ? $entered['state'] : '';

// Fetch ccform:
$ccform = new OCP\Template('registration', 'ccform');
$ccform->assign('entered_data', $entered);
$ccform->assign('tierid', $tierid);

// Fetch main setting form:
$tmpl = new OCP\Template('registration', 'settings');
$storageInfo=OC_Helper::getStorageInfo('/');

// Assigned form's params:
$tmpl->assign('usage', OC_Helper::humanFileSize($storageInfo['used']));
$tmpl->assign('total_space', OC_Helper::humanFileSize($storageInfo['total']));
$tmpl->assign('usage_relative', $storageInfo['relative']);

$tmpl->assign('quota', $quota);
$tmpl->assign('tierid', $tierid);
$tmpl->assign('tier', $tier);
$tmpl->assign('ccform', $ccform->fetchPage());

return $tmpl->fetchPage();
