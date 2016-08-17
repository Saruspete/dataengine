jQuery(document).ready(function($) {

	// Load the content
	$.getJSON("/DataEngine/Collection/editorAjaxLoad", function(data) {

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

	$("#fields").multiselect({
		search: {
			left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
        },
        sort: false
	});
	
});
