// Debounce function for live previews, from https://davidwalsh.name/javascript-debounce-function
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}

// Set up elems
let filterContainer = document.querySelector('.history__filters form');
let filterElems = filterContainer.querySelectorAll('input');

// Set up sorting elems
let sortElems = filterContainer.querySelectorAll('[name="sort[]"]');
let orderElem = filterContainer.querySelector('[name="order"]');
let directionElem = filterContainer.querySelector('[name="direction"]');

// Handle sorting
sortElems.forEach(function(sortElem) {
	sortElem.addEventListener('click', function() {

		// Set direction: opposite dir if already active, default dir if not active and clicking for first time
		if( sortElem.dataset.active == 1 ) {
			sortElem.dataset.direction = ( sortElem.dataset.direction ? sortElem.dataset.direction : sortElem.dataset.defaultDirection ) == 'up' ? 'down' : 'up';
		}
		else {
			sortElem.dataset.direction = sortElem.dataset.defaultDirection;
		}

		// Reset other sort elements
		let otherSortElems = filterContainer.querySelectorAll('[name="sort[]"]:not([value="' + sortElem.value + '"])');
		otherSortElems.forEach(function(otherSortElem) {

			otherSortElem.setAttribute('data-active', 0);
			otherSortElem.querySelector('.filter__arrow').innerHTML = '';
			otherSortElem.dataset.direction = otherSortElem.dataset.defaultDirection;

		});

		// Set as active
		sortElem.dataset.active = 1;

		// Set arrow
		sortElem.querySelector('.filter__arrow').innerHTML = sortElem.dataset.direction == 'up' ? '↑' : '↓';

		// Set actual order/direction inputs
		orderElem.value = sortElem.value;
		directionElem.value = sortElem.dataset.direction;

		// Blur
		sortElem.blur();

		// Trigger change
		sortElem.dispatchEvent(new Event('change', {bubbles:true}));

	});
});

// Refresh list with updated params
function refreshList() {

	// Get active filters
	let activeFilters = Object.fromEntries(new FormData(filterContainer));

	// Turn filters into query string
	let queryString = Object.keys(activeFilters).map(key => key + '=' + activeFilters[key]).join('&');

	window.location = '/artists/&' + queryString;

}

// Fire refresh whenever filter elem changes
filterContainer.addEventListener('change', function(event) {

	debounce(refreshList(), 0);

});

// Clear filters
let resetElem = filterContainer.querySelector('[name="reset"]');
resetElem.addEventListener('click', function() {
	window.location = '/artists/';
});

// Open filters from link
let filterWrapper = filterContainer.closest('details');
let openLink = filterWrapper.querySelector('.filters__edit');

openLink.addEventListener('click', function(event) {
	event.preventDefault();
	filterWrapper.setAttribute('open', true);
});