// ========================================================
// Helpers
// ========================================================

// Clear out dropdown that may be using selectize.js--should prob be sitewide
function clearDropdown( elemToClear ) {
	
	// Clear value
	elemToClear.value = null;
	triggerChange(elemToClear);
	
	// Destroy selectize
	if( !elemToClear.value.length && elemToClear.classList.contains('selectized') ) {
		elemToClear.selectize.destroy();
	}
	
}

// More specific function to find all dropdowns w/ data-based-on and clear them
function clearDropdownsBasedOn( basedOnElem ) {
	
	// Get the name of the element the dropdowns are connected to
	let basedOnName = basedOnElem.getAttribute('name');
	
	// Then find the elements connected to that dropdown
	let dropdownsToClear = document.querySelectorAll('[data-based-on="' + basedOnName + '"]');
	
	// Then loop through and clear them
	dropdownsToClear.forEach(function(dropdownToClear) {
		clearDropdown(dropdownToClear);
	});
	
}

// Update list of possible songs based on value of a specific artist dropdown,
// and also update any other dropdowns that are based on that new list of songs
// References updateDropdownList from getJsonLists.js
function updateDropdownsBasedOn( artistDropdownElem ) {
	
	if( artistDropdownElem ) {
		
		// Get the new artist ID
		let artistId = artistDropdownElem.value;
		
		// Get the name attribute of the dropdown
		let artistDropdownName = artistDropdownElem.getAttribute('name');
		
		// Find any other dropdowns that base their songs on that artist
		let dropdownElemsToChange = document.querySelectorAll('[data-based-on="' + artistDropdownName + '"]');
		
		// If we have dropdowns to change, change them
		if( dropdownElemsToChange.length > 0 ) {
			dropdownElemsToChange.forEach(function(dropdownElemToChange) {
				
				// Update data source for dropdown
				updateDropdownList( artistId, dropdownElemToChange );
				
			});
		}
		
	}
	
}

// ========================================================
// Shared
// ========================================================

// Get shared elements
let songForm = document.querySelector('[name="edit-song"]');
let artistIdElem = document.querySelector('[name="artist_id"]');
let songTypeElems = document.querySelectorAll('[name="type"]');

// ========================================================
// Change song choices dropdowns depending on various tings
// ========================================================

let variantElem = document.querySelector('[name="variant_of"]');
let coverElem = document.querySelector('[name="cover_of"]');
let coverArtistIdElem = document.querySelector('[name="cover_artist_id"]');

// When artist changed, update list of songs to be used in various dropdowns
if( artistIdElem ) {
	artistIdElem.addEventListener('change', function(event) {
		
		// Add new list into page
		getJsonLists( artistIdElem.value, [ 'songs' ] );
		
		// Clear any dropdowns which were selected based on previous artist's songs
		clearDropdownsBasedOn( artistIdElem );
		
		// Update any dropdowns with the new artist's songs
		updateDropdownsBasedOn( artistIdElem );
		
	});
}

// When song type is changed, make sure we clear "variant of" and "cover of" selects
if( songTypeElems ) {
	songTypeElems.forEach(function(songTypeElem) {
		songTypeElem.addEventListener('change', function(event) {
			
			clearDropdown( variantElem );
			clearDropdown( coverElem );
			
		});
	});
}

// When cover artist ID is changed, update list of possible cover songs
if( coverArtistIdElem ) {
	coverArtistIdElem.addEventListener('change', function(event) {
		
		// Add new list into page
		getJsonLists( coverArtistIdElem.value, [ 'songs' ] );
		
		// Clear the original songs dropdown
		clearDropdownsBasedOn( coverArtistIdElem );
		
		// Insert songs from covered artist and update dropdown of possible songs
		updateDropdownsBasedOn( coverArtistIdElem );
		
	});
}

// ========================================================
// Clear out flat and friendly names if necessary
// ========================================================

let nameElem = document.querySelector('[name="name"]');
let romajiElem = document.querySelector('[name="romaji"]');
let flatElem = document.querySelector('[name="flat"]');
let friendlyElem = document.querySelector('[name="friendly"]');

// If name changed, clear both friendly and flat
nameElem.addEventListener('change', function() {
	flatElem.value = '';
	friendlyElem.value = '';
});

// If romaji changed, clear friendly only
romajiElem.addEventListener('change', function() {
	friendlyElem.value = '';
});

// ========================================================
// Submit
// ========================================================

// Submit additions
initializeInlineSubmit($(songForm), '/songs/function-edit.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElem, returnedData) {
		
		console.log('success');
		console.log(formElem);
		console.log(returnedData);
		
	},
	callbackOnError: function(formElem, returnedData) {
		
		console.log('error');
		console.log(formElem);
		console.log(returnedData);
		
	}
});

// ========================================================
// Inits
// ========================================================

// Get initial list of artist's songs, clear associated dropdowns, then initialize
getJsonLists( artistIdElem.value, [ 'songs' ] );
updateDropdownsBasedOn( artistIdElem );

// Init inputmask
$(':input').inputmask();

// Look for dropdowns
lookForSelectize();

// Autosize
autosize($(".autosize"));