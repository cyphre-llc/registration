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
});
