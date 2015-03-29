<?php
	OCP\Util::addStyle('core', 'guest');
	$_['email'] = array_key_exists('email',$_) ? $_['email'] : '';

    if (!array_key_exists('entered_data',$_) || !is_array($_['entered_data'])){
		$_['entered_data'] = array();
	}
	$_['entered_data']['email'] = array_key_exists('email',$_['entered_data']) ? $_['entered_data']['email'] : '';
	$_['entered_data']['user'] = array_key_exists('user',$_['entered_data']) ? $_['entered_data']['user'] : '';
	$_['entered_data']['tierid'] = array_key_exists('tierid',$_['entered_data']) ? $_['entered_data']['tierid'] : 1;
	$_['entered_data']['firstname'] = array_key_exists('firstname',$_['entered_data']) ? $_['entered_data']['firstname'] : '';
	$_['entered_data']['lastname'] = array_key_exists('lastname',$_['entered_data']) ? $_['entered_data']['lastname'] : '';
	$_['entered_data']['country'] = array_key_exists('country',$_['entered_data']) ? $_['entered_data']['country'] : 'US';
	$_['entered_data']['zip'] = array_key_exists('zip',$_['entered_data']) ? $_['entered_data']['zip'] : '';
	$_['entered_data']['address'] = array_key_exists('address',$_['entered_data']) ? $_['entered_data']['address'] : '';
	$_['entered_data']['address1'] = array_key_exists('address1',$_['entered_data']) ? $_['entered_data']['address1'] : '';
	$_['entered_data']['city'] = array_key_exists('city',$_['entered_data']) ? $_['entered_data']['city'] : '';
	$_['entered_data']['state'] = array_key_exists('state',$_['entered_data']) ? $_['entered_data']['state'] : '';

?>

<form id="regist" action="" method="post">
	<fieldset>
		<?php if ( $_['errormsgs'] ) {?>
		<div class="errors">
<?php foreach ( $_['errormsgs'] as $errormsg ) {
	echo "<p>$errormsg</p>";
} ?>
		</div>
		<?php } ?>
		
		<p class='info'><?php print_unescaped($l->t('Choose a unique username and password')); ?></p>
		<p class="infield grouptop">
		<input style="width: 223px; padding-left: 1.8em; color:#888 !important;" type="email" name="email" id="email" value="<?php echo $_['email']; ?>" disabled />
		<label for="email" class="infield"><?php echo $_['email']; ?></label>
		<img style="top:1.5em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>

		<p class="infield groupmiddle">
		<label for="user"><?php print_unescaped($l->t( 'Username' )); ?></label>
		<input type="text" name="user" id="user" required="" value="<?php echo $_['entered_data']['user']; ?>" placeholder="<?php print_unescaped($l->t( 'Username' )); ?>" />
		<img style="top:2.7em;" class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>
		
		<p class="infield groupmiddle">
			<label for="password"><?php print_unescaped($l->t( 'Password' )); ?></label>
			<input type="password" name="password" id="password" value="" data-typetoggle="#show" placeholder="<?php print_unescaped($l->t( 'Password' )); ?>" required="" original-title="" style="display: inline-block;" />
			<img style="top:2.7em;" id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
			<input type="hidden" id="groups" name="groups" value="users" />
		</p>

		<p class="infield groupbottom">
			<input type="password" name="password-clone" id="password-clone" autocomplete="off" original-title="" placeholder="<?php print_unescaped($l->t( 'Re-type Password' )); ?>" />
			<img style="top:2.7em;" id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
		</p>

		<hr/>

		<p class='infield'>
			<label for="tierid"><?php print_unescaped($l->t( 'Select your service level' )); ?></label><br>
			<div>
			<select name="tierid" id="tierid">
<?php
		$display_billing = false;
		$stmt = OC_DB::prepare('SELECT * FROM `*PREFIX*tier_table`');
                $result = $stmt->execute(array());
                while($row = $result->fetchRow()) {
			print "\t\t<option value=\"". $row['tierid'] ."\"";

			if ($row['tierid'] == $_['entered_data']['tierid']) {
				if ($row['amount'] > 0)
					$display_billing = true;
				print " selected";
			}

			if ($row['amount'] > 0)
				print " state='1'";
			else
				print " state='0'";

			print ">". $row['description'] ."</option>\n";
		}
?>
			</select>
			</div>
		</p>

<?php
	if ($display_billing) {
?>
	<div style="display: block" id="billing_display">
<?php
	} else {
?>
	<div style="display: none" id="billing_display">
<?php
	}
?>

<?php
	$tmpl = new OCP\Template('registration', 'ccform');
	$tmpl->assign('entered_data', $_['entered_data']);
	$tmpl->printPage();
?>

	</div>

		<hr/>

		<p class="infield groupbottom">
			<p class="info">
			<input id="tosagree" type="checkbox" name="tosagree" required="" value="yes"></input>
			I agree to <a style="text-decoration: none" target="_blank" href="/themes/svy/terms/cyphre_termsV2.htm" target="_blank">Terms and Conditions</a>
			</p>
		</p>
		<br/>

		<div id="formMsgContainer" class="errors" style="display:none;">
			<p id="formMsg"></p>
		</div>

		<input type="submit" id="submit"  class="login primary" value="<?php print_unescaped($l->t('Create account')); ?>" />
	</fieldset>
</form>

<?php OC_Util::addScript("registration", "billing");?>
