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
			
			// Clear checkbox
			else if( elemToClear.nodeName == 'TEXTAREA' ) {
				elemToClear.value = '';
			}
			
			// Clear checkbox
			else if( elemToClear.nodeName == 'INPUT' && elemToClear.type == 'checkbox' ) {
				elemToClear.checked = false;
			}
			
			// Clear checkbox
			else if( elemToClear.nodeName == 'INPUT' && elemToClear.type == 'text' ) {
				
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

// ========================================================
// Shared
// ========================================================

// Get shared elements
let magazineForm = document.querySelector('[name="update-magazine"]');

// ========================================================
// Submit
// ========================================================

// Submit additions
initializeInlineSubmit($(magazineForm), '/magazines/function-update.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElem, returnedData) {
	}
});

// Clear inputs unless marked otherwise
//clearInputs( magazineForm.querySelectorAll('[name]:not([data-persist-on-dupe])') );

// Look for dropdowns
lookForSelectize();