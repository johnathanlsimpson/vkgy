//
// Click on button to add/remove from report
//
document.addEventListener('change', function(event) {
	
	// Handle change to report checkbox (add/remove from report)
	if(event.target.classList.contains('report__choice')) {
		
		// Get checkbox input and the button that triggered it
		let reportChoiceElem = event.target;
		let reportButtonElem = reportChoiceElem.closest('.report__button');
		
		// Handle the actual change
		changeReportStatus(reportButtonElem, reportChoiceElem);
		
	}
	
});

//
// Moderation buttons
//
document.addEventListener('click', function(event) {
	
	// Approve video
	if(event.target.classList.contains('moderation__button')) {
		
		let buttonElem = event.target;
		let action = buttonElem.value;
		let id = buttonElem.dataset.id;
		
		// For delete button, add question mark first, and only go through if clicked again
		if( action === 'delete' && !buttonElem.classList.contains('moderation__button--clicked') ) {
			
			buttonElem.innerHTML += '?';
			buttonElem.classList.add('moderation__button--clicked');
			buttonElem.blur();
			
		}
		
		// Otherwise go through
		else {
			
			// Add loading symbol
			buttonElem.classList.add('loading');
			
			initializeInlineSubmit($(buttonElem), '/videos/function-moderate.php', {
				preparedFormData: {
					'action': action,
					'id': id
				},
				
				callbackOnSuccess: function(formElem, returnedData) {
					
					// Get container so we can do stuff
					let containerElem = buttonElem.closest('.moderation__container');
					
					// Reset status classes
					buttonElem.classList.remove('loading');
					buttonElem.classList.add('symbol__success');
					buttonElem.blur();
					
					// Show change after a few secs
					setTimeout(function() {
						
						// If video approved, just fade out and then hide moderation box
						if( action === 'approve' || action === 'approve_all' ) {
							
							containerElem.classList.add('any--fade-out');
							setTimeout(function() {
								containerElem.classList.add('any--hidden');
							}, 300);
							
						}
						
						// If video deleted, fade out video then refresh page
						else if( action === 'delete' ) {
							
							buttonElem.closest('.col').classList.add('any--fade-out');
							setTimeout(function() {
								location.reload();
							}, 300);
							
						}
						
					}, 1000);
					
				},
				
				callbackOnError: function(formElem, returnedData) {
					
					// Don't want loading symbol to override error symbol
					buttonElem.classList.remove('loading');
					buttonElem.classList.add('symbol__error');
					
				},
				
			});
			
		}
		
	}
	
});


//
// Logic for actually adding/removing from report
//
function changeReportStatus(reportElem, reportChoiceElem, event) {
	
	let reportType = reportChoiceElem.value;
	let itemId     = reportElem.dataset.itemId;
	let statusElem = reportElem.querySelector('[data-role="status"]');
	
	// Set status classes
	if(statusElem) {
		statusElem.classList.remove('success');
		statusElem.classList.add('loading');
	}

	initializeInlineSubmit($(reportElem), '/videos/function-report_item.php', {
		preparedFormData: {
			'report_type': reportType,
			'item_id': itemId,
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading');
			}
			
			// Remove success symbol after a few secs
			setTimeout(function() {
				statusElem.classList.remove('symbol__success');
			}, 3000);
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			
			// Don't want loading symbol to override error symbol
			statusElem.classList.remove('loading');
			statusElem.classList.add('symbol__error');
			
		},
		
	});

}

//
// Init the tippy-powered dropdown of available report
//
function initReportTippys() {
	
	// Get report triggers
	let reportWrapperElem = document.querySelector('.report__wrapper:not(.tippy-active)');
	let reportContainerElem = document.querySelector('#template-report-container');
	
	// Clone reportContainer so we can manipulate it for each dropdown button
	let tempElem = document.createElement('div');
	tempElem.innerHTML = reportContainerElem.innerHTML;
	let newReportsContainerElem = tempElem.querySelector('.report__container');
	
	// Get the checkbox elem for the dropdown, so that the button is appropriately styled
	let reportChoiceElem = reportWrapperElem.querySelector('.report__open .input__choice');
	
	let reportTip = tippy(reportWrapperElem, {
		arrow: false,
		delay: [0, 0],
		duration: 0,
		dynamicTitle: false,
		hideOnClick: true,
		html: newReportsContainerElem,
		interactive: true,
		interactiveBorder: 0,
		maxWidth: 300,
		placement: 'bottom-start',
		showOnCreate: true,
		trigger: 'click',
		onShow: function() {
			this.classList.add('report__tippy');
			reportChoiceElem.checked = true;
		},
		onHidden: function() {
			reportChoiceElem.checked = false;
		},
	});
	
}

initReportTippys();