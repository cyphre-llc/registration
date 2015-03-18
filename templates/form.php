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
		<input style="width: 223px; padding-left: 1.8em;" type="email" name="email" id="email" value="<?php echo $_['email']; ?>" disabled />
		<label for="email" class="infield"><?php echo $_['email']; ?></label>
		<img style="position:absolute; left:1.25em; top:1.65em;-ms-filter:'progid:DXImageTransform.Microsoft.Alpha(Opacity=30)'; filter:alpha(opacity=30); opacity:.3;" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>

		<p class="infield groupmiddle">
		<input type="text" name="user" id="user" value="<?php echo $_['entered_data']['user']; ?>" />
		<label for="user" class="infield"><?php print_unescaped($l->t( 'Username' )); ?></label>
		<img class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>
		
		<p class="infield groupbottom">
			<input type="password" name="password" id="password" value="" data-typetoggle="#show" placeholder="" required="" original-title="" style="display: inline-block;"><input type="text" name="password-clone" tabindex="0" autocomplete="off" style="display: none;" original-title="">
			<label for="password" class="infield"><?php print_unescaped($l->t( 'Password' )); ?></label>
			<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
			
			
			<input type="hidden" id="groups" name="groups" value="users">
		</p>

		<p class='info'><?php print_unescaped($l->t('Select your service level')); ?></p>
                <p class="infield">
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

		<p class='info'><?php print_unescaped($l->t('Enter billing information')); ?></p>
		<hr/>
                <p class="infield">
		<label for="firstname" class="" style="color: #ccc"><?php print_unescaped($l->t( 'First Name on Card' )); ?></label>
                <input type="text" name="firstname" id="firstname" autocomplete="cc-given-name" value="<?php echo $_['entered_data']['firstname']; ?>" />
                </p>
		<p class="infield">
                <label for="lastname" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Last Name on Card' )); ?></label><br>
                <input type="text" name="lastname" id="lastname" autocomplete="cc-family-name" value="<?php echo $_['entered_data']['lastname']; ?>" />
                </p>

		<hr/>

		<p class="infield">
			<label for="address" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Street Address' )); ?></label><br>
			<input type="text" name="address" id="address" autocomplete="street-address" maxlength="50" value="<?php echo $_['entered_data']['address']; ?>" />
        </p>

		<p class="infield">
			<label for="city" class="" style="color: #ccc"><?php print_unescaped($l->t( 'City' )); ?></label><br>
			<input type="text" name="city" id="city" autocomplete="city-address" maxlength="50" value="<?php echo $_['entered_data']['city']; ?>" />
        </p>

		<p class="infield">
			<label for="state" class="" style="color: #ccc"><?php print_unescaped($l->t( 'State' )." (2 Letters Code)"); ?></label><br>
			<input type="text" name="state" id="state" autocomplete="state-address" maxlength="2" value="<?php echo $_['entered_data']['state']; ?>" />
        </p>

		<p class="infield">
		<label for="country" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Country' )); ?></label><br>
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
		</p>

		<p class="infield">
		<label for="zip" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Zip Code' )); ?></label><br>
                <input type="text" name="zip" id="zip" autocomplete="postal-code" inputmode="numeric" value="<?php echo $_['entered_data']['zip']; ?>" />
                </p>

		<hr/>

		<p class="infield">
		<label for="cc_cardnum" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Credit Card Number' )); ?></label><br>
                <input type="text" name="cc_cardnum" id="cc_cardnum" autocomplete="cc-number" inputmode="numeric" value="" />
                </p>
		<p class="infield">
		<label for="cc_expmonth" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Expiration Date' )); ?></label><br>
		<div>
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
		</div>
                </p>
		<p class="infield">
		<label for="cc_ccv" class="" style="color: #ccc"><?php print_unescaped($l->t( 'Card Security Code' )); ?></label><br>
                <input type="text" name="cc_ccv" id="cc_ccv" autocomplete="cc-csc" value="" />
                </p>
		<hr/>
	</div>


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
