#!/usr/bin/env php
<?php
$base = dirname(__FILE__);

class OC {
	public static $SERVERROOT = null;
};

include_once("$base/../../../config/config.php");

$db = new mysqli($CONFIG['dbhost'], $CONFIG['dbuser'], $CONFIG['dbpassword'],
		 $CONFIG['dbname']);
$db_pre = $CONFIG['dbtableprefix'];

if ($db->connect_error) {
	die('Connect Error (' . $db->connect_errno . ') ' .
	    $db->connect_error);
}

function setPref($uid, $key, $val)
{
	global $db, $db_pre, $CONFIG;

	$uid = $db->real_escape_string($uid);
	$key = $db->real_escape_string($key);
	$val = $db->real_escape_string($val);

	$db->query("DELETE FROM ${db_pre}preferences WHERE " .
		"appid='registration' AND userid='$uid' AND configkey='$key'");

	$res = $db->query("INSERT INTO ${db_pre}preferences VALUES(" .
		"'$uid', 'registration', '$key', '$val')");
}

$fields = array(
	'firstname' => 'firstname',
	'lastname' => 'lastname',
	'zip' => 'zip',
	'cardnum' => 'cc_cardnum',
	'tierid' => 'tierid',
	'rate' => 'rate',
);
		
function convertToPref($row)
{
	global $fields;

	$uid = $row['uid'];

	foreach ($fields as $key => $val)
		setPref($uid, $key, $row[$val]);

	$expire = sprintf("%02d%02d", $row['cc_expmonth'], substr($row['cc_expyear'], -2));
	setPref($uid, 'expire', $expire);
}

$res = $db->query("SELECT * FROM ${db_pre}users_billing");

if ($res === false)
	exit();

while ($row = $res->fetch_array())
	convertToPref($row);
