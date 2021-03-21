// Get all session-band indicators and make them highlight in pairs
let sessionElems = document.querySelectorAll('session');
if(sessionElems.length) {
	sessionElems.forEach(function(sessionElem) {
		var forSession = sessionElem.getAttribute('data-for-session');
		var isSession = sessionElem.getAttribute('data-is-session');
		var siblingElem;
		
		if(forSession) {
			siblingElem = document.querySelector('session[data-is-session="' + forSession + '"]');
		}
		else {
			siblingElem = document.querySelector('session[data-for-session="' + isSession + '"]');
		}
		
		sessionElem.addEventListener('mouseover', function() {
			sessionElem.classList.add('lineup__session--hovered');
			siblingElem.classList.add('lineup__session--hovered');
		});
		sessionElem.addEventListener('mouseout', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
		});
		sessionElem.addEventListener('click', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
			sessionElem.classList.toggle('lineup__session--clicked');
			siblingElem.classList.toggle('lineup__session--clicked');
		});
	});
}


	// Set up filtering/ordering options
 let filterOptions = {
  valueNames: [
			'activity',
			'date',
			'end',
			'lineup',
			'live',
			'member',
			'note',
			'release',
			'schedule',
			'start',
			'trouble',
			{ 'data' : [ 'year', 'sort-date' ] }
		]
 };
	
	// Set up filterable list
	let historyContainer = document.querySelector('.history__container');

 let historyItems = new List(historyContainer, filterOptions);
	
	// Set up dynamic filters
	let filtersContainer = document.querySelector('.history__filters');
	
	filtersContainer.addEventListener('change', function(event) {
		
		if( event.target.classList.contains('filter') ) {
			
			let allElem = filtersContainer.querySelector('[value="all"]');
			let allIsChecked = allElem.checked;
			let otherElems = filtersContainer.querySelectorAll('[value]:not([value="all"])');
			let otherCheckedElems = filtersContainer.querySelectorAll('[value]:not([value="all"]):checked');
			
			if(
				( event.target.value === 'all' && allIsChecked ) // if person just checked 'all' (can't uncheck 'cause it's radio)
				||
				( !otherCheckedElems || !otherCheckedElems.length ) // if all other filters unchecked, default back to all being checked
			) {
				
				// Make sure 'all' is checked
				allElem.checked = true;
				
				// Make sure other filters aren't checked as the same time as 'all'
				otherElems.forEach(function(otherElem) {
					otherElem.checked = false;
				});
				
				// Reset filter
				historyItems.filter();
				
			}
			
			// If 'all' isn't checked and some other filter is checked, filter appropriately
			else {
				
				// Make sure 'all' is unchecked
				allElem.checked = false;
				
				// Loop through list elems
				historyItems.filter(function(item) {
					
					// By default, assume item will be hidden
					let itemSatisfiesFilter = false;
					
					// We'll save the active filters here and use them in a bit
					let activeFilters = [];
					
					// Loop through filter buttons and see which filters we need to push to list
					otherCheckedElems.forEach(function(checkedElem) {
						
						let checkedFilter = checkedElem.value;
						
						// Push the filter button's value as the filter
						activeFilters.push( checkedFilter );
						
						// Certain filter buttons may activate further hidden filters
						if( checkedFilter === 'activity' ) {
							activeFilters.push( 'start', 'end' );
						}
						
						else if( checkedFilter === 'other' ) {
							activeFilters.push( 'label', 'name', 'trouble', 'note', 'setlist' );
						}
						
						else if( checkedFilter === 'member' ) {
							activeFilters.push( 'lineup' );
						}
						
					});
					
					// Now for the active filters, loop through and actually filter them
					activeFilters.forEach(function(activeFilter) {
						
						if( item.values()[activeFilter] ) {
							
							itemSatisfiesFilter = true;
							
						}
						
					});
					
					return itemSatisfiesFilter;
					
				});
				
			}
			
		}
		
	});
	
	// When filters are changed, make sure year separators are correct
	historyItems.on('updated', function() {
		
		let currentYear, previousYear, currentDay, previousDay = '';
		
		historyItems.visibleItems.forEach(function(item) {
			
			currentYear = item.values().year;
			currentDay = item.values().date;
			
			if( currentYear != previousYear ) {
				item.elm.classList.add('new-year');
			}
			else {
				item.elm.classList.remove('new-year');
			}
			
			if( currentDay === previousDay ) {
				item.elm.classList.add('same-day');
			}
			else {
				item.elm.classList.remove('same-day');
			}
			
			previousYear = currentYear;
			previousDay = currentDay;
			
		});
		
		// Scroll back to top
		location.hash = null;
		location.hash = 'history';
		
	});
	
	// Account List.js issue where initial sort appears to do nothing since
	// it's sorted in the php; so we'll make the button initially sort the
	// opposite direction then remove that attribute so it toggles directions
	let sortElem = filtersContainer.querySelector('.sort');
	
	sortElem.addEventListener('click', function() {
		
		// Let List.js do its thing, then change the button
		setTimeout(function() {
			sortElem.removeAttribute('data-order');
		}, 300);
		
		// Also blur the button
		sortElem.blur();
		
	});