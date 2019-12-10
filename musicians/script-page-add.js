// Call autosize() on textareas.
autosize($(".autosize"));

// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();

// Handle submit.
initializeInlineSubmit($("[name=form__add]"), "/musicians/function-add.php", {
	submitOnEvent : "submit"
});