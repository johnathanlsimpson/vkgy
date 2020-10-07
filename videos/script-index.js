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