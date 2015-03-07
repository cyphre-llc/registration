<?php

$tmpl = new OCP\Template('registration', 'settings');

$storageInfo=OC_Helper::getStorageInfo('/');

$tmpl->assign('usage', OC_Helper::humanFileSize($storageInfo['used']));
$tmpl->assign('total_space', OC_Helper::humanFileSize($storageInfo['total']));
$tmpl->assign('usage_relative', $storageInfo['relative']);

return $tmpl->fetchPage();
