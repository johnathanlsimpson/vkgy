// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();

// Init inputmask() on appropriate elements
$(":input").inputmask();

// Submit
initializeInlineSubmit($("[name=form__add]"), "/labels/function-add.php", { submitOnEvent : "submit" });