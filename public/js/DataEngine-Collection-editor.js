jQuery(document).ready(function($) {

	// Load the content
	$.getJSON("/DataEngine/Collection/editorAjaxGetPlaceholders", function(data) {

		$("#fields").empty();

		$.each(data, function(phid, placeholder) {
			// create new group
			var ph = $("<optgroup>");
			ph.attr('label', placeholder.name + "("+placeholder.id+")");

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
		sort: false,
		afterMoveToRight: multiselectRefreshPhPrim,
		afterMoveToLeft: multiselectRefreshPhPrim
	});


	function multiselectRefreshPhPrim($left, $right, $options) {
		var $ph = $("#phPrim");
		var phSel = $ph.val()

		$ph.empty();
		$right.find('optgroup').each(function (i,e) {
			var phname = $(e).attr('label');
			var phid = phname.substring(phname.lastIndexOf("(")+1,phname.lastIndexOf(")"));
			var newOpt = $('<option></option>').val(phid).text(phname);
			$ph.append(newOpt);
		});

		if (phSel)
			$ph.val(phSel);
		if (!$ph.val())
			$ph.val($ph.find("option:first").val());
	}

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
	// On change event
	$("#collection").on("change", function(e) {
		
		var collId = $(this).val();

		if (collId != undefined && collId != 0) {

			$.getJSON("/DataEngine/Collection/editorAjaxGetCollectionDetails/"+collId, function(data) {
				// Already registered entry.
				$("#fields_to").empty();

				// Create placeholders
				for (var p=0; p<data.placeholders.length; p++) {
					var phdata = data.placeholders[p];

					var $optgrp = $("<optgroup>")
						.attr('label', phdata.name)
						.val(phdata.id);

					// Create fields
					$.each(phdata.fields, function(fid, fdata) {
						var $opt = $('<option>')
							.val(fdata.id)
							.text(fdata.name+" ("+ fdata.path +")");
						$optgrp.append($opt);
					});

					$("#fields_to").append($optgrp);
				};

				// Required due to https://github.com/select2/select2/issues/4104
				if ( ! $("#fields_to").length ) {
					$("#fields_to").append($("<option></option>"));
				}
			});
		}
	});

	$("#phPrim").select2({
		placeholder: "Primary placeholder",
		theme:		"classic"
	});
});
