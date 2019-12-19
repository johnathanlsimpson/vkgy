/*!
 * Serialize all form data into a query string
 * (c) 2018 Chris Ferdinandi, MIT License, https://gomakethings.com
 * @param  {Node}   form The form to serialize
 * @return {String}      The serialized form data
 */
var serialize = function (form) {

	// Setup our serialized data
	var serialized = [];

	// Loop through each field in the form
	for (var i = 0; i < form.elements.length; i++) {

		var field = form.elements[i];

		// Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
		if (!field.name || field.disabled || field.type === 'file' || field.type === 'reset' || field.type === 'submit' || field.type === 'button') continue;

		// If a multi-select, get all selections
		if (field.type === 'select-multiple') {
			for (var n = 0; n < field.options.length; n++) {
				if (!field.options[n].selected) continue;
				serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[n].value));
			}
		}

		// Convert field data to a query string
		else if ((field.type !== 'checkbox' && field.type !== 'radio') || field.checked) {
			serialized.push(encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value));
		}
	}

	return serialized.join('&');

};

// At page load, serialize current data and insert it into an element used to track changes
var origForm = document.querySelector('#form__edit');
var origFormData = serialize(origForm);
var origElem = document.querySelector('#form__original');
origElem.value = origFormData;

// Auto-size textareas
autosize($(".autoresize"));


// Show hidden elements on click
$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();
	showElem($(this), "edit__hidden any--hidden");
});

$(document).on("click", ".edit__hidden", function() {
	var classList = $(this).attr("class").split(" ");
	
	for(var i = 0; i < classList.length; i++) {
		$("[data-show=" + classList[i] + "]").trigger("click");
	}
});


// Log changes made
var formElement = document.getElementById('form__edit');
var changesElement = document.getElementById('form__changes');
formElement.addEventListener('change', function(event) {
	changesElement.value = changesElement.value + (changesElement.value ? ',' : '') + event.target.name;
});


// Handle submit
initializeInlineSubmit($("[name=form__edit]"), "/artists/function-edit.php", {
	"submitOnEvent" : "submit",
	"callbackOnSuccess" : function() {
		document.getElementById('form__changes').value = '';
	}
});


// Handle artist deletion
initDelete($("[name=delete]"), "/artists/function-delete.php", { id : $("[name=id]").val() }, function() {
	$("body").removeClass("any--pulse").addClass("any--pulse");
});


// Handle musician deletion
$(".edit__delete-musician").each(function() {
	var elem = $(this);
	initDelete(elem, "/musicians/function-delete.php", { id : elem.data("id") }, function(elem) {
		elem.parents(".edit__musician").addClass("any--fade-out");
		setTimeout(function() {
			elem.parents(".edit__musician").addClass("any--hidden");
		}, 300);
	});
});


// Preview bio
previewBio($(".edit__history"), $(".edit__history-preview"), $("[name=id]").val());
$(".edit__history").on("change", function(event) {
	previewBio($(this), $(".edit__history-preview"), $("[name=id]").val());
});


// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();