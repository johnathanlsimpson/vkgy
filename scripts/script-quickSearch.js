// Quick search result container
let searchElem = document.querySelector('.primary-nav__search');
let quickSearchElem = document.querySelector('.quick-search__container');
let quickSearchWrapper = document.querySelector('.quick-search__wrapper');


// Make the result container visible
let showQuickSearch = function() {
	
	// Remove hidden class (shows loading symbol by default)
	quickSearchWrapper.classList.remove('quick-search--hidden', 'quick-search--closed');
	
}


// Hide search results (css makes sure visible on hover)
let hideQuickSearch = debounceX(function() {
	
	quickSearchWrapper.classList.add('quick-search--hidden');
	
}, 100);


// Clear results and input
let clearQuickSearch = debounceX(function() {
	searchElem.value = '';
	quickSearchElem.innerHTML = '';
}, 0);


// Handle the actual quick search (debounced 300 ms)
let quickSearch = debounceX(function() {
	
	initializeInlineSubmit( $(quickSearchElem), '/php/get-quick_search.php', {
		
		'preparedFormData': { 'q': searchElem.value },
		
		'callbackOnSuccess': function(formElem, returnedData) {
			
			if( returnedData.result ) {
				quickSearchElem.innerHTML = returnedData.result;
			}
			else {
				clearQuickSearch();
			}
			
		},
		
		'callbackOnError': function(formElem, returnedData) {
			
			if( returnedData.result ) {
				quickSearchElem.innerHTML = returnedData.result;
			}
			else {
				clearQuickSearch();
			}
			
		}
		
	});
	
}, 200);


// Show the empty result container as soon as paste/start typing in search, then get results after debounce
['keyup', 'paste', 'focus'].forEach(function(eventType) {
	
	searchElem.addEventListener( eventType , function() {
		
		// Show and search if value not empty
		if( searchElem.value && searchElem.value.length > 0 ) {
			showQuickSearch();
			quickSearch();
		}
		
		// Otherwise hide and clear results
		else {
			hideQuickSearch();
			clearQuickSearch();
		}
		
	});
	
});


// On blur, wait a bit then add class to close results (css won't let it close if being hovered on)
searchElem.addEventListener('blur', function(event) {
	hideQuickSearch();
});


// Close button: won't actually be loaded until menu has some content, so check for bubbling click
quickSearchWrapper.addEventListener('click', function(event) {
	
	if( event.target.classList.contains('quick-search__close') ) {
		
		// Also add another class to force it closed regardless of hover
		quickSearchWrapper.classList.add('quick-search--closed');
		
		// Ignore link stuff
		event.preventDefault();
		//event.target.blur();
		
	}
	
});