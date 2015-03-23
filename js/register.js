$(document).ready(function()
{
	// validattion on fields those are NOT handled by formValidation:
	$( "#regist" ).submit(function( event ) {
		var msg = eleid = '';
		if($("#email").val() !== $("#email-clone").val()) {
			msg = 'Email and Email-retype does not matched';
			eleid = "#email";
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
