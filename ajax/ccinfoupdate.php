<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('registration');
OCP\JSON::callCheck();

$l = new OC_L10n('settings');
$uid = OC_User::getUser();

$config = OC::$server->getConfig();

$rebill_id = $config->getUserValue($uid, 'registration', 'rebill_id', '');
if ($rebill_id) {
	$email = $config->getUserValue($uid, 'settings', 'email', '');
	$expire = sprintf("%02d%02d", $_POST['cc_expmonth'], substr($_POST['cc_expyear'], -2));

	$newccauth = new \OCA\Registration\BluePay();
	$newccauth->setCustInfo(array(
				'account'   => $uid,
				'email'     => $email,
				'memo'      => 'NEW CC Authorization',
				'name1'     => $_POST['firstname'],
				'name2'     => $_POST['lastname'],
				'zip'       => $_POST['zip'],
				'account'   => $_POST['cc_cardnum'],
				'cvv2'      => $_POST['cc_ccv'],
				'expire'    => $expire,
				'country'   => $_POST['country'],
				'addr1' => $_POST['address'],
				'addr2' => $_POST['address1'],
				'city'  => $_POST['city'],
				'state' => $_POST['state'],
				));
	$newccauth->setAuth(0, 0);	// $0 transaction to get AUTH on NEW cc
	$newccauth->process();

	// If transaction was approved..
	if ($newccauth->status() == $newccauth::STATUS_APPROVED) {
		// Bind $newccauth to existing rebill with bluepay:
		$bprebill = new \OCA\Registration\BluePay();
		$bprebill->updateRebillCCard($rebill_id, $newccauth->transId());
		$bprebill->process();

		if ($bprebill->rebillStatus() 
			&& $bprebill->getTemplateID() === $newccauth->transId()
			&& $bprebill->getRebStatus() === 'active') {
			OC_JSON::success();
			// Update user's NEW last 4 cardnum:
			$config->setUserValue($uid, 'registration', 'cardnum', substr($_POST['cc_cardnum'], -4));
		} else {
			OC_JSON::error(array("data" => array("message" => $l->t('Please contact support for this request.'))));
			\OCP\Util::writeLog('setting', "UserId '$uid': Rebill_Credit_Card_Update failed", \OCP\Util::ERROR);
		}
	} else {
		OC_JSON::error(array("data" => array("message" => 'Update failed: '.$l->t($newccauth->message()))));
	}
} else {
	OC_JSON::error(array("data" => array("message" => $l->t('Please contact support for this request.'))));
	\OCP\Util::writeLog('setting', "UserId '$uid': Missing rebill_id record in oc_preferences table", \OCP\Util::ERROR);
}

exit();


