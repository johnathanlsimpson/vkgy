/**
 * Set selectize() options and initialize
 *
 * !  requires external/script-selectize.js
 */

function initSelectize(selectElement, populatedOnClick = false) {
	var dataSource  = selectElement.data("source");
	var data        = $("[data-contains=" + dataSource + "]").html();
	var isMultiple  = selectElement.data("multiple") ? true : false;
	var allowCreate = selectElement.data("create") ? true : false;
	
	if(data) {
		data = data.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
	}

	var selectizeOptions = {
		delimiter    : ",",
		persist      : false,
		create       : false,
		hideSelected : false,
		preload      : "focus"
	};
	
	if(isMultiple) {
		selectElement.attr("multiple", true);
	}
	else {
		selectizeOptions.selectOnTab = true;
	}
	
	if(allowCreate) {
		selectizeOptions.create = true;
		selectizeOptions.createOnBlur = true;
	}
	
	if(dataSource !== undefined && data !== undefined) {
		selectizeOptions.valueField  = [0];
		selectizeOptions.labelField  = [2];
		selectizeOptions.searchField = [2];
		selectizeOptions.options     = JSON.parse(data);
	}

	var selectizedElement = selectElement.selectize(selectizeOptions);
	var selectizedObject  = selectizedElement[0].selectize;
	
	if(populatedOnClick) {
		selectizedObject.open();
	}
}


function lookForSelectize() {
	$.each($("select:not(.selectized)"), function() {
		if($(this).data("populate-on-click")) {
			$(this).on("focus click", function() {
				initSelectize($(this), true);
			});
		}
		else {
			initSelectize($(this), false);
		}
	});
}