$(document).ready(function()
{
	// validattion on fields those are NOT handled by formValidation:
	$( "#regist" ).submit(function( event ) {
		var msg = eleid = '';
		if($("#email").val() !== $("#email-clone").val()) {
			msg = 'Email addresses do not match';
			eleid = "#email-clone";
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
