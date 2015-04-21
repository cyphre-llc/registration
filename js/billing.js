$(document).ready(function()
{
	var msg = eleid = '';

	$("#tierid").change(function()
	{

		$( '#sales-tax-div' ).hide();
		$('#tier_amount').val("");
		$('#sales_tax_amount').val("");
		$('#monthly_total_amount').val("");

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
			$("input[name = zip]").attribute("placeholder", "Zip Code");
		else if (country == "CA")
			$("input[name = zip]").attr("placeholder", "Postal Code");
		else if (country == "IT")
			$("input[name = zip]").attr("placeholder", "CAP");
		else if (country == "BR")
			$("input[name = zip]").attr("placeholder", "CEP");
		else if (country == "IN")
			$("input[name = zip]").attr("placeholder", "PIN Code");
		else if (country == "DE" || country == "AT" || country == "CH")
			$("input[name = zip]").attr("placeholder", "PLZ");
		else
			$("input[name = zip]").attr("placeholder", "Postcode");
	});

	$("#storagebutton").click( function(){
		ccValidate();
		if (!msg) {
			if (!$('#sales_tax_amount').val()) {
				getSalesTax();
				$("#storagebutton").blur();
				return false;
			}

			// Serialize the data
			var post = $( "#storageform" ).serialize();
			$('#storagechanged').hide();
			$('#storageerror').hide();
			// Ajax foo
			$.post(OC.filePath( 'registration', 'ajax', 'changestorage.php' ), post, function(data){
				$("#storagebutton").blur();
				if( data.status === "success" ){
					$('#storagechanged').show();
					$('#storageform').hide();
				} else{
					if (typeof(data.data) !== "undefined") {
						msg = data.data.message;
					} else {
						msg = t('Unable to change storage');
					}
					$('#storageerror').html(msg);
					$('#storageerror').show();
					$( '#formMsg' ).text(msg);
					$( '#formMsgContainer' ).show().fadeOut(4000, function() {
						$( "div#content-wrapper" ).scrollTop(0);
					});
				}
			});

		} else {
			$( '#formMsg' ).text(msg);
			$( '#formMsgContainer' ).show().fadeOut(4000, function() {
				$(eleid).focus();
			});
		}
		return false;
	});

	$("#ccupdatebutton").click( function(){

		ccValidate();
		if (!msg) {
			// Serialize the data
			var post = $( "#storageform" ).serialize();
			$('#storagechanged').hide();
			$('#storageerror').hide();
			$.post(OC.filePath( 'registration', 'ajax', 'ccinfoupdate.php' ), post, function(data){
				$("#ccupdatebutton").blur();
				if( data.status === "success" ){
					$('#storageform').hide();
					$('#storagechanged').slideDown('slow');
				} else{
					if (typeof(data.data) !== "undefined") {
						msg = data.data.message;
					} else {
						msg = t('Unable to change storage');
					}
					$('#storageerror').html(msg);
					$('#storageerror').show();
					$( '#formMsg' ).text(msg);
					$( '#formMsgContainer' ).show().fadeOut(4000, function() {
						$( "div#content-wrapper" ).scrollTop(0);
					});
					$('html, body').animate({ scrollTop: 0 }, 0);
				}
			});
		} else {
			$( '#formMsg' ).text(msg);
			$( '#formMsgContainer' ).show().fadeOut(4000, function() {
				$(eleid).focus();
			});
			$('html, body').animate({ scrollTop: 0 }, 0);
		}

		return false;
	});

	// validattion on fields those are NOT handled by formValidation:
	$( "#regist" ).submit(function( event ) {
		if($("#password").val() !== $("#password-clone").val()) {
			msg = 'Passwords do not match';
			eleid = "#password";
		} else if ($("#tierid option:selected").index()) {
			ccValidate();
			if (!msg && !$('#sales_tax_amount').val()) {
				getSalesTax();
				event.preventDefault();
				return false;
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

	function ccValidate() {
		msg = eleid = '';
		if (!$("#firstname").val()) {
			msg = 'Firstname is required';
			eleid = "#firstname";
		} else if(!$("#lastname").val()) {
			msg = 'Lastname is required';
			eleid = "#lastname";
		} else if(!$("#address").val()) {
			msg = 'Address is required';
			eleid = "#address";
		} else if(!$("#city").val()) {
			msg = 'City is required';
			eleid = "#city";
		} else if(!$("#state").val()) {
			msg = 'State is required';
			eleid = "#state";
		} else if(!$("#country option:selected").index()) {
			msg = 'Country is required';
			eleid = "#country";
		} else if(!$("#zip").val()) {
			msg = 'Postal code is required';
			eleid = "#zip";
		} else if(!$("#cc_cardnum").val()||!/^\d+$/.test($("#cc_cardnum").val())) {
			msg = 'Credit Card Number is required (Digits ONLY)';
			eleid = "#cc_cardnum";
		} else if(!$("#cc_ccv").val()||!/^\d+$/.test($("#cc_ccv").val())) {
			msg = 'Security Card Code is required (Digits ONLY)';
			eleid = "#cc_ccv";
		}
	}

	function getSalesTax() {
		msg = eleid = '';

		$.ajax({url: OC.filePath('registration', 'ajax', 'salestax.php'),
		    data: {
					tierid: $( "#tierid" ).val(),
					token: $( "#token" ).val(),
					firstname: $( "#firstname" ).val(),
					lastname: $( "#lastname" ).val(),
					address: $( "#address" ).val(),
					city: $( "#city" ).val(),
					state: $( "#state" ).val(),
					zip: $( "#zip" ).val(),
					country: $( "#country" ).val()
				},
			type: 'get',
			async: false,
			success: function(result) {
				if (result !== 'null') {
					var trans = jQuery.parseJSON(result);
					if (trans.amount && trans.taxamount) {
						var amount, taxamount;
						amount = parseFloat(trans.amount);
						taxamount = parseFloat(trans.taxamount);
						$('#tier_amount').val(amount.toFixed(2));
						$('#sales_tax_amount').val(taxamount.toFixed(2));
						$('#monthly_total_amount').val((amount + taxamount).toFixed(2));
						$( "#sales-tax-div" ).slideDown('slow');
					} else {
						msg = trans.errorMsg ? trans.errorMsg : 'Server is temprorarily busy, please try again later';
						eleid = "#monthly_total_amount";
						$( '#formMsg' ).text(msg);
						$( '#formMsgContainer' ).show().fadeOut(4000, function() {
							$(eleid).focus();
						});
					}
				} else {
					msg = 'Server is temprorarily busy, please try again later';
					eleid = "#monthly_total_amount";
					$( '#formMsg' ).text(msg);
					$( '#formMsgContainer' ).show().fadeOut(4000, function() {
						$(eleid).focus();
					});
				}
			},

			error: function(XMLHttpRequest, textStatus, errorThrown) { 
				msg = 'Server is temprorarily busy, please try again later';
				eleid = "#monthly_total_amount";
				$( '#formMsg' ).text(msg);
				$( '#formMsgContainer' ).show().fadeOut(4000, function() {
					$(eleid).focus();
				});
			}  

		});
	}

});
