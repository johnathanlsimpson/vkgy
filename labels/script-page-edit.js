// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();

// Init inputmask() on appropriate elements
$(":input").inputmask();

// Submit
initializeInlineSubmit($("[name=form__edit]"), "/labels/function-edit.php", { submitOnEvent : "submit" });

// Handle label deletion
initDelete($("[name=delete]"), "/labels/function-delete.php", { id : $("[name=id]").val() }, function() {
	$("body").removeClass("any--pulse").addClass("any--pulse");
});