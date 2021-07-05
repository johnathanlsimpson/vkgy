// ========================================================
// Helpers
// ========================================================

// Clear specified inputs
function clearInputs(elemsToClear) {
	
	if( elemsToClear && elemsToClear.length > 0 ) {
		
		elemsToClear.forEach(function(elemToClear) {
			
			// Clear select
			if( elemToClear.nodeName == 'SELECT' ) {
				elemToClear.selectedIndex = -1;
			}
			
			// Clear textarea
			else if( elemToClear.nodeName == 'TEXTAREA' ) {
				
				elemToClear.value = '';
				
				// Trigger change if textarea is connected to rich editor
				if( elemToClear.classList.contains('any--tributable') ) {
					triggerChange(elemToClear);
				}
				
			}
			
			// Clear checkbox
			else if( elemToClear.nodeName == 'INPUT' && elemToClear.type == 'checkbox' ) {
				elemToClear.checked = false;
			}
			
			// Clear text inputs
			// Making this more generic to also clear type="hidden"--will have to see if this causes problems
			else if( elemToClear.nodeName == 'INPUT' && ( elemToClear.type == 'text' || elemToClear.type == 'hidden' ) ) {
				
				// Idk something weird is going on with Alpine if I don't clear actual value
				if( elemToClear.getAttribute('x-model') ) {
					elemToClear.value = '';
				}
				
				// And something weird going on with normal elems if don't clear attribute
				else {
					elemToClear.setAttribute('value', '');
				}
				
			}
			
		});
		
	}
	
}

// Decode html entities (stored in data attribute for elements which toggle text depending on state)
function entityDecode(input) {
	
	let tempElem = document.createElement('textarea');
	tempElem.innerHTML = input;
	return tempElem.value;
	
}

// ========================================================
// Shared
// ========================================================

// Get shared elements
let issueForm = document.querySelector('[name="update-issue"]');
let resultElem = issueForm.querySelector('[data-role="result"]');

// Get ID
let idElem = issueForm.querySelector('[name="id"]');
let isEdit = idElem.value && idElem.value.length > 0 ? 1 : 0;

// ========================================================
// Submit
// ========================================================

// Submit additions
initializeInlineSubmit($(issueForm), '/magazines/function-update_issue.php', {
	submitOnEvent: 'submit',
	showEditLink: true,
	callbackOnSuccess: function(formElem, returnedData) {
		
		if(returnedData.is_new) {
			let newEvent = new Event('item-id-updated');
			newEvent.details = { 'id':returnedData.id };
			document.dispatchEvent(newEvent);
		}
		
	}
});

// ========================================================
// Change state
// ========================================================

// Get containers for toggling
let submitContainer = issueForm.querySelector('[data-role="submit-container"]');
let editContainer = issueForm.querySelector('[data-role="edit-container"]');

// Get elems
let statusElem = issueForm.querySelector('[data-role="status"]');
let editElem = document.querySelector('[data-role="edit"]');
let duplicateElem = document.querySelector('[data-role="duplicate"]');

// Get elems with text that toggles
let toggleElems = document.querySelectorAll('[data-add-text]');
let navElem = document.querySelector('.tertiary-nav--active');
let titleEnd = ' | ' + document.title.split(/\ \| /).pop();

// Change page state after saving
function changePageState(state) {
	
	// Default URL to update address bar
	let newURL = '/magazines/add/';
	let newTitle = 'Add issue (&#38609;&#35468;&#12434;&#36861;&#21152;)';
	
	// Duplicate or add new issue
	if( state == 'add' ) {
		
		// Remove images if necessary
		let images = document.querySelectorAll('.image__results .image__template');
		if(images && images.length) {
			images.forEach(function(image) {
				image.remove();
			});
		}
		
		// Clear inputs unless marked otherwise
		clearInputs( issueForm.querySelectorAll('[name]:not([data-persist-on-dupe]):not([name^="image_"])') );
		
		// Leave all but year for date
		let dateElem = document.querySelector('[name="date_represented"]');
		dateElem.value = dateElem.value && dateElem.value.length > 0 ? dateElem.value.substring(0, 4) : '';
		
		// Clear status and results (this is handled by inlineSubmit for edits but not dupes)
		statusElem.classList.remove('success', 'loading', 'error', 'symbol__success', 'symbol__loading', 'symbol__error');
		resultElem.innerHTML = '';
		
		// Toggle view of submit controls (this is handled by inlineSubmit for edits but not dupes)
		submitContainer.classList.remove('any--hidden');
		editContainer.classList.add('any--hidden');
		
		// Come back to this after we add images
		// Reset defaults for image uploading
		document.querySelector('[name=image_item_id]').value = null;
		document.querySelector('[name=image_item_name]').value = null;
		
	}
	
	// Re-edit issue
	else if( state == 'edit' ) {
		
		// Change new URL to point to edit URL
		newURL = issueForm.querySelector('[data-get="edit_url"]').href;
		newTitle = 'Edit issue (&#38609;&#35468;&#12434;&#32232;&#38598;&#12377;&#12427;)';
		
	}
	
	// Toggle elements with variable text
	toggleElems.forEach(function(toggleElem) {
		let newHTML = entityDecode( toggleElem.getAttribute('data-' + state + '-text') );
		toggleElem.innerHTML = newHTML;
	});
	
	// Change url of nav bar
	navElem.href = newURL;
	
	// Change title and url, add to history
	newTitle += titleEnd;
	document.title = entityDecode(newTitle);
	history.pushState( null, newTitle, newURL );
	
	// Flash page to indicate change
	document.body.classList.remove('any--pulse');
	setTimeout(function() {
		document.body.classList.add('any--pulse');
	}, 1);
	
}

// Re-edit issue
editElem.addEventListener('click', function(event) {
	event.preventDefault();
	changePageState('edit');
});

// Duplicate issue
duplicateElem.addEventListener('click', function(event) {
	event.preventDefault();
	changePageState('add');
});

// ========================================================
// Delete
// ========================================================
initDelete( $('[name="delete"]'), '/magazines/function-delete_issue.php', { 'issue_id': idElem.value }, function(formElem, returnedData) {
	
	document.body.classList.add('any--pulse');
	
	resultElem.innerHTML = returnedData.result;
	resultElem.classList.remove('any--hidden');
	
	setTimeout(function() {
		window.location = '/magazines/';
	}, 1500);
	
});

// ========================================================
// Inits
// ========================================================

// Init inputmask
$(':input').inputmask();

// Autosize
autosize($(".autosize"));

// Look for dropdowns
lookForSelectize();