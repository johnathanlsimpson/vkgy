// Initiate pagination
initPagination();

// Handle filtering
let filterForm = document.querySelector('[name="filter_videos"]');
filterForm.addEventListener('submit', function(event) {
	
	// Cancel normal submit
	event.preventDefault();
	
	// Get filter values
	let filters = Object.fromEntries(new FormData(event.target));
	
	/*
	// Don't actually want to save pagination if changing filter... but let's save it just for fun
	
	// Since we're fucky with URLs, split at first & to check for pagination
	[currentUrl, currentQuery] = window.location.href.split('&');
	let urlParams = new URLSearchParams(currentQuery);
	
	// Save current pagination value
	if( currentQuery && urlParams.get('page') ) {
		filters.page = urlParams.get('page');
	}*/
	
	// Push filters to url
	let newUrl = window.location.href.split('&')[0];
	if( filters ) {
		for( const [key, value] of Object.entries(filters) ) {
			if( value.length ) {
				newUrl += '&' + key + '=' + value;
			}
		}
	}
	
	// Make it headless
	let headlessUrl = newUrl + '&headless=1';
	
	// Get wrapper into which to load
	let paginationWrapper = document.querySelector('.pagination__wrapper');
	
	// Load the headless page in the wrapper
	fetch(headlessUrl)
	
	// Get raw data
	.then((response) => {
		return response.text();
	})
	
	// Once we have raw data, put back into page and re-init links and such
	.then((html) => {
		
		// Inject data
		paginationWrapper.innerHTML = html;
		
		// Re-init new pagination links
		initPagination();
		
		// Update history (unless we're manually surpressing this, i.e. forward/back in browser)
		window.history.pushState({ 'newUrl': newUrl }, '', newUrl);
		
	})
	
	.catch((error) => {
	});
	
});

// Init selectize
lookForSelectize();



// If choosing 'change_type' as bulk edit action, show dropdown of available types
document.addEventListener('change', function(event) {
	
	if(event.target && event.target.name == 'action') {
		
		let typeElem = event.target.closest('form').querySelector('.moderation__type');
		
		if(event.target.value == 'change_type') {
			typeElem.classList.remove('any--hidden');
		}
		else {
			typeElem.classList.add('any--hidden');
		}
		
	}
	
});



// Do your best to handle moderation form
document.addEventListener('submit', function(event) {
	if(event.target && event.target.classList.contains('videos__container')) {
		
		event.preventDefault();
		
		initializeInlineSubmit($(event.target), '/videos/function-bulk_edit.php', {
			
			callbackOnSuccess: function(formElem, returnedData) {
				
				// Uncheck the checkboxes
				let checkboxElems = formElem[0].querySelectorAll('[name="ids[]"]:checked');
				checkboxElems.forEach(function(checkboxElem) {
					checkboxElem.checked = false;
				});
				
				// Get action
				let actionElem = formElem[0].querySelector('[name="action"]:checked');
				let action = actionElem.value;
				let typeElem = formElem[0].querySelector('[name="type"]');
				let type = typeElem.options[typeElem.selectedIndex].text;
				
				// Loop through checkboxes and make changes to parent video elems
				checkboxElems.forEach(function(checkboxElem) {
					let videoElem = checkboxElem.closest('.videos__video');
					let typeTextElem = videoElem.querySelector('.videos__type');
					
					// If videos were approved, remove is_flagged attribute
					if(action == 'approve') {
						videoElem.classList.remove('video--flagged');
					}
					
					else if(action == 'change_type') {
						typeTextElem.innerHTML = type;
					}
					
				});
				
			},
			
			callbackOnError: function(formElem, returnedData) {
			}
			
		});
		
	}
});