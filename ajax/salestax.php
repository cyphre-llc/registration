<?php

require_once 'lib/base.php';

$avatax = \OCA\Registration\Controller::calSalesTax($_GET, false);
if ($avatax) {
	if (!array_key_exists('errorMsg', $avatax)) {
		print_r(json_encode(array('amount' => $avatax['amount'], 'taxamount' => $avatax['taxamount'])));
	} else {
		print_r(json_encode(array('errorMsg' => $avatax['errorMsg'])));	// avatax return error!
	}
} else {
	print_r(json_encode(null)); // FAILED!
}

?>
