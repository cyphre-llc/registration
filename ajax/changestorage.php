<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('registration');
OCP\JSON::callCheck();

$l = new OC_L10n('settings');
$uid = OC_User::getUser();

$config = OC::$server->getConfig();
$email = $config->getUserValue($uid, 'settings', 'email', '');

$post = array_merge($_POST, array('email' => $email, 'tierid' => '2'));

try {
	OCA\Registration\Controller::processTier($uid, $post);
} catch (\Exception $e) {
	OC_JSON::error(array("data" => array("message" => $e->getMessage())));
	exit();
}

OC_JSON::success();
