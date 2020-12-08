let issuesContainer = document.querySelector('.issues__container');

issuesContainer.addEventListener('change', function(event) {
	if( event.target.classList.contains('issue__completed') ) {
		
		let completedElem = event.target;
		let issueElem = completedElem.closest('.issue__container');
		let issueTextElem = issueElem.querySelector('.issue__text');
		
		initializeInlineSubmit($(issueElem), '/about/function-complete_issue.php', {
			
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



/*

let completeIssueElems = document.querySelectorAll('.issue__complete');
completeIssueElems.forEach(function(completeIssueElem) {
	
	
	initializeInlineSubmit($(completeIssueElem), '/about/function-complete_issue.php', {
		
		submitOnEvent: 'change',
		
		callbackOnSuccess: function(formElem, returnedData) {
			console.log('success');
			console.log(returnedData);
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			console.log('error');
			console.log(returnedData);
		}
		
	});
});

function completeIssue(issueID) {
	
}*/