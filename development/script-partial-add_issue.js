let addIssueForm = document.querySelector('[name="form__add-issue"]');
let titleElem = addIssueForm.querySelector('[name="title"]');
let statusElem = addIssueForm.querySelector('[data-role="status"]');

initializeInlineSubmit($(addIssueForm), '/about/function-update.php', {
	submitOnEvent: 'submit',
	
	callbackOnSuccess: function(formElem, returnedData) {
		
		console.log(formElem);
		
		// Clear inputs.value = '';
		titleElem.value = '';
		
		// Remove status
		setTimeout(function() {
			statusElem.classList.remove(...statusElem.classList);
		}, 3000);
		
		console.log('success');
		console.log(returnedData);
	},
	
	callbackOnError: function(formElem, returnedData) {
		console.log('error');
		console.log(returnedData);
	}
});

lookForSelectize();