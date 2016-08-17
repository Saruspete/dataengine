jQuery(document).ready(function($) {

	// Load the content
	$.getJSON("/DataEngine/Collection/editorAjaxGetPlaceholders", function(data) {

		$("#fields").empty();

		$.each(data, function(phid, placeholder) {
			// create new group
			var ph = $("<optgroup>");
			ph.attr('label', placeholder.name);

			// Add fields to group
			$.each(placeholder.fields, function(fid, field) {
				var opt = $("<option></option>")
					.text(field.name+" ("+field.path+")")
					.val(field.id);
				ph.append(opt);
			});
			// Add the group
			$("#fields").append(ph);
		});
	//	$("#fields").multiselect('refresh');
	});


	$.getJSON("/DataEngine/Collection/editorAjaxGetCollections", function(data) {

		$("#collection").empty();

		$.each(data, function(phid, data) {
			var opt = $("<option></option>")
				.text(data.name)
				.val(data.id);
			$("#collection").append(opt);
		});

		// Required due to https://github.com/select2/select2/issues/4104
		if ( ! $("#collection").length ) {
			$("#collection").append($("<option></option>"));
		}
	//	$("#fields").multiselect('refresh');
	});


	$("#fields").multiselect({
		search: {
			left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
        },
        sort: false
	});

	$("#collection").select2({
		placeholder: "Select / Create new collection",
		allowClear:	true,
		theme:		"classic",
		tags:		true,
		createTag: function (params) {
			return {
				id: params.term,
				text: params.term,
				newOption: true
			}
		}
	});
});
