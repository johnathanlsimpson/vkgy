let addIssueForm = document.querySelector('[name="form__add-issue"]');
let titleElem = addIssueForm.querySelector('[name="title"]');
let statusElem = addIssueForm.querySelector('[data-role="status"]');

initializeInlineSubmit($(addIssueForm), '/development/function-update.php', {
	submitOnEvent: 'submit',
	
	callbackOnSuccess: function(formElem, returnedData) {
		
		// Clear inputs.value = '';
		titleElem.value = '';
		
		// Remove status
		setTimeout(function() {
			statusElem.classList.remove(...statusElem.classList);
		}, 3000);
		
	},
	
	callbackOnError: function(formElem, returnedData) {
	}
});

lookForSelectize();