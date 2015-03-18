$(document).ready(function()
{
	$("#tierid").change(function()
	{
		var state = $('option:selected', this).attr('state');
		if (state == "0")
			$("#billing_display").hide();
		else
			$("#billing_display").show();
	});

	$("#country").change(function()
	{
		var country = $('option:selected', this).val();
		if (country == "US" || country == "PH")
			$("label[for = zip]").text("Zip Code");
		else if (country == "CA")
			$("label[for = zip]").text("Postal Code");
		else if (country == "IT")
			$("label[for = zip]").text("CAP");
		else if (country == "BR")
			$("label[for = zip]").text("CEP");
		else if (country == "IN")
			$("label[for = zip]").text("PIN Code");
		else if (country == "DE" || country == "AT" || country == "CH")
			$("label[for = zip]").text("PLZ");
		else
			$("label[for = zip]").text("Postcode");
	});

	$("#storagebutton").click( function(){
		// Serialize the data
		var post = $( "#storageform" ).serialize();
		$('#storagechanged').hide();
		$('#storageerror').hide();
		// Ajax foo
		$.post(OC.filePath( 'registration', 'ajax', 'changestorage.php' ), post, function(data){
			if( data.status === "success" ){
				$('#storagechanged').show();
				$('#storageform').hide();
			} else{
				if (typeof(data.data) !== "undefined") {
					$('#storageerror').html(data.data.message);
				} else {
					$('#storageerror').html(t('Unable to change storage'));
				}
				$('#storageerror').show();
			}
		});
		return false;
	});

	// validattion on fields those are NOT handled by formValidation:
	$( "#regist" ).submit(function( event ) {
		var msg = eleid = '';
		if ($("#tierid option:selected").index()) {
			if (!$("#firstname").val()) {
				msg = 'firstname is required!';
				eleid = "#firstname";
			} else if(!$("#lastname").val()) {
				msg = 'lastname is required!';
				eleid = "#lastname";
			} else if(!$("#address").val()) {
				msg = 'Street address is required!';
				eleid = "#address";
			} else if(!$("#city").val()) {
				msg = 'City address is required!';
				eleid = "#city";
			} else if(!$("#state").val()) {
				msg = 'State address Code is required!';
				eleid = "#state";
			} else if(!$("#country option:selected").index()) {
				msg = 'Please Select your country address!';
				eleid = "#country";
			} else if(!$("#city").val() && !$("#state").val() && !$("#zip").val()) {
				msg = 'Zip code is required unless city and state are specified!';
				eleid = "#zip";
			} else if(!$("#cc_cardnum").val()||!/^\d+$/.test($("#cc_cardnum").val())) {
				msg = 'Invalid Credit Card Number. Digits ONLY!';
				eleid = "#cc_cardnum";
			} else if(!$("#cc_ccv").val()||!/^\d{3}$/.test($("#cc_ccv").val())) {
				 msg = 'Invalid Security Card Code. 3 Digits ONLY!';
				 eleid = "#cc_ccv";
			}

		}
		if (msg) {
			$( '#formMsg' ).text(msg);
			$( '#formMsgContainer' ).show().fadeOut(4000, function() {
				$(eleid).focus();
			});
			event.preventDefault(); // Prevent form from submit ELSE return;
			return false;
		} else {

			return true;
		}

	});
});
