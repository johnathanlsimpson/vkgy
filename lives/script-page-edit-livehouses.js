// Activate submit button.
initializeInlineSubmit($("[name=form__update]"), "/lives/function-edit-livehouses.php", { "submitOnEvent" : "submit" });

// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();