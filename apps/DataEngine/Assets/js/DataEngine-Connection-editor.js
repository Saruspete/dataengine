$(document).ready(function($) {

	$("#connectionid").select2({
		placeholder: "Select / Create new connection",
		theme:		"classic",
	});

	$("#connectionid").on("change", function(e) {
		connId = $(this).val();
		
		// Already registered entry.
		if (connId != undefined && connId != 0) {

			$.getJSON("/DataEngine/Connection/editorAjaxGetConnectionInfo/"+connId, function(data) {
				var res = data[0];
				var hst = res.hostname;
				if (res.hostport != '0')
					hst += ":"+res.hostport;
				$('#connectionname').val(res.name).change();
				$('#connectiontype').val(res.type).change();
				$('#connectionhost').val(hst).change();
				$('#connectionusername').val(res.username).change();
				$('#connectionpassword').val("(unchanged)").change();
				$('#connectionschema').val(res.resource).change();
				$('#connectionextra').val(res.extra).change();
			});
		}
		// New entry
		else {

			var flds = new Array('name', 'host', 'username', 'password', 'schema', 'extra');
			for (fldId in flds) {
				fld = flds[fldId];
				$('#connection'+fld).val('').change();
			}
		}

	});


	$("#connectiontype").select2({
		theme:		"classic"
	});

	// Hook the "test" button
/*
	$("#btnConnectionTest").click(function() {

		var postOpts = '';
		var flds = new Array('Type', 'User', 'Pass', 'Host', 'Schema', 'Extra');
		var vals = new Array();
		for (fldId in flds) {
			fld = flds[fldId];
			postOpts += "&"+fld+"="+ $("#connection"+fld).val();
		}

		// TODO: DO the post request
		$.post("/DataEngine/Collection/discoverAjaxGetPlaceholders", postOpts, function() {
			  
		});

		return false;
	})
*/

/*
	$('input[type="text"]').each(function(){

		// Skip undefined attributes
		if (! $(this).attr('title') ) 
			return;

		$(this).focus(function(){
			if(this.value == $(this).attr('title')) {
				this.value = '';
				$(this).removeClass('text-label');
			}
		});

		$(this).blur(function(){
			if(this.value == '') {
				this.value = $(this).attr('title');
				$(this).addClass('text-label');
			}
		});

		$(this).on('change', function(){
			if(this.value == '') {
				this.value = $(this).attr('title');
				$(this).addClass('text-label');
			}
			else {
				$(this).removeClass('text-label');
			}
		});


		// Skip already filled values
		if (this.value != '' && this.value != $(this).attr('title'))
			return;

		this.value = $(this).attr('title');
		$(this).addClass('text-label');
	});
*/

});