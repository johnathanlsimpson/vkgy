let issuesContainer = document.querySelector('.issues__container');

issuesContainer.addEventListener('change', function(event) {
	if( event.target.classList.contains('issue__completed') ) {
		
		let completedElem = event.target;
		let issueElem = completedElem.closest('.issue__container');
		let issueTextElem = issueElem.querySelector('.issue__text');
		
		initializeInlineSubmit($(issueElem), '/development/function-complete_issue.php', {
			
			callbackOnSuccess: function(formElem, returnedData) {
				
				// Add/remove 'completed' class
				if(completedElem.checked) {
					issueTextElem.classList.add('issue--completed');
				}
				else {
					issueTextElem.classList.remove('issue--completed');
				}
				
			},
			
			callbackOnError: function(formElem, returnedData) {
			}
			
		});
		
		// Remove status after a few seconds
		setTimeout(function() {
			issueElem.querySelector('[data-role="status"]').classList = '';
		}, 3000);
		
	}
});