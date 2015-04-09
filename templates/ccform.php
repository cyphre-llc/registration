<br/>
<p style="text-align:center;"><label><b><?php print_unescaped($l->t('Enter billing information')); ?></b></label></p>
<hr/>
<p class="infield">
	<label for="firstname"><?php print_unescaped($l->t( 'Name on card' )); ?></label><br>
	<input type="text" name="firstname" id="firstname" autocomplete="cc-given-name" value="<?php echo $_['entered_data']['firstname']; ?>" placeholder="<?php print_unescaped($l->t( 'First Name' )); ?>" />
	<input type="text" name="lastname" id="lastname" autocomplete="cc-family-name" value="<?php echo $_['entered_data']['lastname']; ?>" placeholder="<?php print_unescaped($l->t( 'Last Name' )); ?>" />
</p>

<hr/>

<p class="infield">
	<label for="address"><?php print_unescaped($l->t( 'Street Address' )); ?></label><br>
	<input type="text" name="address" id="address" autocomplete="street-address" maxlength="50" value="<?php echo $_['entered_data']['address']; ?>" placeholder="<?php print_unescaped($l->t( 'Address Line 1' )); ?>" />
</p>

<p class="infield">
	<input type="text" name="address1" id="address1" autocomplete="street-address1" maxlength="50" value="<?php echo $_['entered_data']['address1']; ?>" placeholder="<?php print_unescaped($l->t( 'Address Line 2' )); ?>" />
</p>

<hr/>

<p class="infield">
	<input type="text" name="city" id="city" autocomplete="city-address" maxlength="50" value="<?php echo $_['entered_data']['city']; ?>" placeholder="<?php print_unescaped($l->t( 'City or Locality' )); ?>" />
</p>

<p class="infield">
	<input type="text" name="state" id="state" autocomplete="state-address" maxlength="2" value="<?php echo $_['entered_data']['state']; ?>" placeholder="<?php print_unescaped($l->t( 'State or Province' )); ?>" />
</p>

<p class="infield">
	<div>
	<select name="country" id="country">
	<option value="">-- Select Country --</option>
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
	</div>
</p>

<p class="infield">
	<input type="text" name="zip" id="zip" autocomplete="postal-code" inputmode="numeric" value="<?php echo $_['entered_data']['zip']; ?>" placeholder="<?php print_unescaped($l->t( 'Zip Code' )); ?>" />
</p>

<hr/>

<p class="infield">
	<label for="cc_cardnum"><?php print_unescaped($l->t( 'Credit Card' )); ?></label><br>
	<input type="text" name="cc_cardnum" id="cc_cardnum" autocomplete="cc-number" inputmode="numeric" value="" placeholder="<?php print_unescaped($l->t( 'Credit Card Number' )); ?>" />
</p>
<p class="infield">
	<label for="cc_expmonth"><?php print_unescaped($l->t( 'Expiration Date' )); ?></label><br>
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
	<input type="text" name="cc_ccv" id="cc_ccv" autocomplete="cc-csc" value="" placeholder="<?php print_unescaped($l->t( 'Card Security Code' )); ?>" />
</p>

<style>
#tier_amount, #sales_tax_amount, #monthly_total_amount {background-color: rgba(230,230,230,.9)}
</style>

<div id="sales-tax-div" class="hidden">
<hr/>
<p class="infield">
	<label for="tier_amount"><?php print_unescaped($l->t( 'Monthly Amount/USD' )); ?></label><br>
	<input type="text" name="tier_amount" id="tier_amount" readonly value=""/>
</p>
<p class="infield">
	<label for="sales_tax_amount"><?php print_unescaped($l->t( 'Sales Tax/USD' )); ?></label><br>
	<input type="text" name="sales_tax_amount" id="sales_tax_amount" readonly value=""/>
</p>
<p class="infield">
	<label for="monthly_total_amount"><?php print_unescaped($l->t( 'Monthly Total/USD' )); ?></label><br>
	<input type="text" name="monthly_total_amount" id="monthly_total_amount" readonly value=""/>
</p>
</div>
