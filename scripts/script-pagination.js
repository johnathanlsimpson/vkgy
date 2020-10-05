// Handle when a pagination link is clicked
function changePage(paginationLink, isSilent = false) {
	
	// Get parent wrapper into which to load
	let paginationWrapper = paginationLink.closest('.pagination__wrapper');
	let paginationWrapperIndex = paginationWrapper
	
	// Get link URL and attach headless flag so only partial is loaded
	let previousUrl = paginationWrapper.querySelector('.pagination--active').getAttribute('href');
	let paginatedUrl = paginationLink.getAttribute('href');
	let headlessUrl = paginatedUrl + '&headless=1';
	
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
		if(!isSilent) {
			window.history.pushState({ 'paginatedUrl': paginatedUrl, 'previousUrl': previousUrl }, '', paginatedUrl);
		}
		
	})
	
	.catch((error) => {
	});
	
}

// Add event listeners to pagination links
function initPagination() {
	
	// Find pagination links and attach handlers
	let paginationLinks = document.querySelectorAll('.pagination__wrapper .pagination__link');
	
	// Loop through links and attach handlers
	if( paginationLinks && paginationLinks.length ) {
		paginationLinks.forEach(function(paginationLink) {
			paginationLink.addEventListener('click', function(event) {
				
				// If JS doesn't load, will just hard load page like normal
				event.preventDefault();
				
				// Logic for getting URL and swapping into page
				changePage(paginationLink);
				
			});
		});
	}
	
}

// Watch for onpopstate (using back/forward in browser in states that we pushed)
window.onpopstate = function(event) {
	
	// If going through states that we pushed, re-fetch content
	if(event.state && event.state.paginatedUrl) {
		let paginationLink = document.querySelector('.pagination__link[href="' + event.state.paginatedUrl + '"]');
		changePage(paginationLink, true);
	}
	
	// If no state (i.e. initial load of page) we have to refresh to update content
	else {
		location.reload();
	}
	
};