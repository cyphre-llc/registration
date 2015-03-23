<?php
$config = \OC::$server->getConfig();
$uid = \OC_User::getUser();
$quota = $config->getUserValue($uid, 'files', 'quota');
OCP\Util::addStyle('core', 'guest');
?>

<div id="quota" class="section">
        <div style="width:<?php p($_['usage_relative']);?>%;">
                <p id="quotatext">
<?php
if (!empty($quota)) {
        print_unescaped($l->t('You have used <strong>%s</strong> of the available <strong>%s</strong>',
                array($_['usage'], $_['total_space'])));
} else {
        print_unescaped($l->t('You have used <strong>%s</strong> of your unlimited storage',
                array($_['usage'])));
}
?>
                </p>
        </div>
</div>

<?php
$tierid = $config->getUserValue($uid, 'registration', 'tierid');
if (empty($tierid) or $tierid < 2) {
?>

<div class="section">
	<h2><?php p($l->t('Upgrade to Unlimited Storage (for $7 USD/month)')); ?></h2><br/>
	<div id="storagechanged"><?php echo $l->t('Your storage was upgraded');?></div>
	<div id="storageerror"><?php echo $l->t('Unable to upgrade your storage');?></div>
	<form id="storageform">
	<fieldset>
<?php
	if (!array_key_exists('entered_data',$_) || !is_array($_['entered_data'])){
		$_['entered_data'] = array();
	}
	$_['entered_data']['tierid'] = array_key_exists('tierid',$_['entered_data']) ? $_['entered_data']['tierid'] : $tierid;
	$_['entered_data']['firstname'] = array_key_exists('firstname',$_['entered_data']) ? $_['entered_data']['firstname'] : '';
	$_['entered_data']['lastname'] = array_key_exists('lastname',$_['entered_data']) ? $_['entered_data']['lastname'] : '';
	$_['entered_data']['country'] = array_key_exists('country',$_['entered_data']) ? $_['entered_data']['country'] : 'US';
	$_['entered_data']['zip'] = array_key_exists('zip',$_['entered_data']) ? $_['entered_data']['zip'] : '';
	$_['entered_data']['address'] = array_key_exists('address',$_['entered_data']) ? $_['entered_data']['address'] : '';
	$_['entered_data']['address1'] = array_key_exists('address1',$_['entered_data']) ? $_['entered_data']['address1'] : '';
	$_['entered_data']['city'] = array_key_exists('city',$_['entered_data']) ? $_['entered_data']['city'] : '';
	$_['entered_data']['state'] = array_key_exists('state',$_['entered_data']) ? $_['entered_data']['state'] : '';

	$tmpl = new OCP\Template('registration', 'ccform');
	$tmpl->assign('entered_data', $_['entered_data']);
	$tmpl->printPage();
?>
		<hr/>
		<div id="formMsgContainer" class="errors" style="display:none;">
			<p id="formMsg"></p>
		</div>

		<input id="storagebutton" type="submit" value="<?php echo $l->t('Upgrade Storage');?>" />
	</fieldset>
</form>
</div>

<?php
} else {
?>
<div class="section">
       <h2><?php print_unescaped($l->t('You are enjoying unlimited <strong>Cyphre</strong> Storage')); ?></h2>
</div>
<?php
}
?>

<?php OC_Util::addScript("registration", "billing");?>

<div class="section">
        <h2><?php p($l->t('Version'));?></h2>
        <strong><?php p($theme->getName()); ?></strong> <?php p(OC_Util::getHumanVersion()) ?><br />
<?php if (OC_Util::getEditionString() === ''): ?>
	<?php print_unescaped($l->t('Developed by the <a href="http://ownCloud.org/contact" target="_blank">ownCloud community</a>, the <a href="https://github.com/owncloud" target="_blank">source code</a> is licensed under the <a href="http://www.gnu.org/licenses/agpl-3.0.html" target="_blank"><abbr title="Affero General Public License">AGPL</abbr></a>.')); ?>
<?php endif; ?>
</div>

<div class="section credits-footer">
        <p><?php print_unescaped($theme->getShortFooter()); ?></p>
</div>
