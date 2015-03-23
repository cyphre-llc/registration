<?php OCP\Util::addStyle('core', 'guest'); ?>

<div id="quota" class="section">
        <div style="width:<?php p($_['usage_relative']);?>%;">
                <p id="quotatext">
<?php
if (!empty($_['quota'])) {
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
if ($_['tierid'] < 2) {
?>

<div class="section">
	<h2><?php p($l->t('Upgrade to ').$_['tier']['description']); ?></h2><br/>
	<div id="storagechanged"><?php echo $l->t('Your storage was upgraded');?></div>
	<div id="storageerror"><?php echo $l->t('Unable to upgrade your storage');?></div>
	<form id="storageform">
	<fieldset>

		<?php print_unescaped($_['ccform']); ?>

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
