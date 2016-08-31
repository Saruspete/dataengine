$(document).ready(function($) {

	$("#connectionId").select2({
		placeholder: "Select / Create new connection",
		allowClear:	true,
		theme:		"classic",
	});

	$("#connectionId").on("change", function(e) {
		connId = $(this).val();
		
		// Already registered entry.
		if (connId != undefined && connId != 0) {

			$.getJSON("/DataEngine/Collection/discoverAjaxGetConnectionInfo/"+connId, function(data) {
				var res = data[0];
				var hst = res.hostname;
				if (res.hostport)
					hst += ":"+res.hostport;
				$('#connectionName').val(res.name).change();
				$('#connectionType').val(res.type).change();
				$('#connectionHost').val(hst).change();
				$('#connectionUser').val(res.username).change();
				$('#connectionPass').val("(unchanged)").change();
				$('#connectionSchema').val(res.resource).change();
				$('#connectionExtra').val(res.extra).change();
			});
		}
		// New entry
		else {

			var flds = new Array('Host', 'User', 'Pass', 'Schema', 'Extra');
			for (fldId in flds) {
				fld = flds[fldId];
				$('#connection'+fld).val('').change();
			}
		}

	});


	$("#connectionType").select2({
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

});