<div id="quota" class="section">
        <div style="width:<?php p($_['usage_relative']);?>%;">
                <p id="quotatext">
<?php
if (empty($tierid) or $tierid < 2) {
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
$config = \OC::$server->getConfig();
$uid = \OC_User::getUser();
$tierid = $config->getUserValue($uid, 'registration', 'tierid', 0);

\OCP\Util::addScript('registration', 'billing');

if (empty($tierid) or $tierid < 2) {
?>
<div class="section">
       <h2><?php p($l->t('Upgrade to Unlimited Storage (for $7 USD/month)')); ?></h2><br/>
       <div id="storagechanged"><?php echo $l->t('Your storage was upgraded');?></div>
        <div id="storageerror"><?php echo $l->t('Unable to upgrade your storage');?></div>

<form id="storageform">
       <p>
       <label for="firstname"><?php p($l->t('First and Last Name on Card'));?></label><br>
       <input class="inlineblock" type="text" name="firstname" id="firstname" autocomplete="cc-given-name" value="<?php echo $_['entered_data']['firstname']; ?>" />
       <input class="inlineblock" type="text" name="lastname" id="lastname" autocomplete="cc-family-name" value="<?php echo $_['entered_data']['lastname']; ?>" />
       </p><br/>

       <p>
       <label for="country"><?php p($l->t('Country'));?></label><br>
       <select name="country" id="country">
       <option value="">-- Select one --</option>
<?php
       $stmt = OC_DB::prepare('SELECT * FROM `*PREFIX*countries` WHERE code!=\'\' ORDER BY seq ASC');
       $result = $stmt->execute(array());
       while($row = $result->fetchRow()) {
               print "\t\t<option value=\"". $row['code'] ."\"";

               if ($row['code'] == $_['entered_data']['country'])
                       print " selected";

               print ">". $row['name'] ."</option>\n";
       }
?>
       </select>
       </p><br/>

       <p>
       <label for="zip"><?php p($l->t('Zip Code'));?></label><br>
       <input type="text" name="zip" id="zip" autocomplete="postal-code" inputmode="numeric" value="<?php echo $_['entered_data']['zip']; ?>" />
       </p><br/>

       <p>
       <label for="cc_cardnum"><?php p($l->t('Credit Card Number'));?></label><br>
       <input type="text" name="cc_cardnum" id="cc_cardnum" autocomplete="cc-number" inputmode="numeric" value="<?php echo $_['entered_data']['cc_cardnum']; ?>" />
       </p><br/>

       <p>
       <label for="cc_expmonth"><?php p($l->t('Card Expiration'));?></label><br>
       <select name="cc_expmonth" id="cc_expmonth" autocomplete="cc-exp-month">
               <option value="1">1 - <?php print_unescaped($l->t( 'January' )); ?></option>
               <option value="2">2 - <?php print_unescaped($l->t( 'February' )); ?></option>
               <option value="3">3 - <?php print_unescaped($l->t( 'March' )); ?></option>
               <option value="4">4 - <?php print_unescaped($l->t( 'April' )); ?></option>
               <option value="5">5 - <?php print_unescaped($l->t( 'May' )); ?></option>
               <option value="6">6 - <?php print_unescaped($l->t( 'June' )); ?></option>
               <option value="7">7 - <?php print_unescaped($l->t( 'July' )); ?></option>
               <option value="8">8 - <?php print_unescaped($l->t( 'August' )); ?></option>
               <option value="9">9 - <?php print_unescaped($l->t( 'September' )); ?></option>
               <option value="10">10 - <?php print_unescaped($l->t( 'October' )); ?></option>
               <option value="11">11 - <?php print_unescaped($l->t( 'November' )); ?></option>
               <option value="12">12 - <?php print_unescaped($l->t( 'December' )); ?></option>
       </select>
       <select name="cc_expyear" id="cc_expyear" autocomplete="cc-exp-year">
<?php
       // 10 years, starting from current year
       $baseyear = date("Y");
       for ($i = 0; $i < 10; $i++) {
               $year = $baseyear + $i;
               print "\t\t<option value='$year'>$year</option>\n";
       }
?>
       </select>
       </p><br/>

       <p>
       <label for="cc_ccv"><?php p($l->t('Card Security Code (ccv)'));?></label><br>
       <input type="text" name="cc_ccv" id="cc_ccv" autocomplete="cc-csc" value="<?php echo $_['entered_data']['cc_ccv']; ?>" />
       </p><br/>

       <input id="storagebutton" type="submit" value="<?php echo $l->t('Upgrade Storage');?>" />
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

<div class="section">
        <h2><?php p($l->t('Version'));?></h2>
        <strong><?php p($theme->getName()); ?></strong> <?php p(OC_Util::getHumanVersion()) ?><br />
<?php if (OC_Util::getEditionString() === ''): ?>
        <?php print_unescaped($l->t('Developed by the <a href="http://ownCloud.org/contact" target="_blank">ownCloud community</a>, the <a href="https://github.com/owncloud" target="_blank">source code</
<?php endif; ?>
</div>

<div class="section credits-footer">
        <p><?php print_unescaped($theme->getShortFooter()); ?></p>
</div>
