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
let resultElem = magazineForm.querySelector('[data-role="result"]');
let inputsToClear = magazineForm.querySelectorAll('[name]:not([data-persist-on-dupe])');

// Get ID (element may appear multiple times in page, but only first one matters if editing a magazine)
let idElem = magazineForm.querySelector('[name="id[]"]');
let isEdit = idElem.value && idElem.value.length > 0 ? 1 : 0;

// ========================================================
// Submit
// ========================================================

// Submit additions
initializeInlineSubmit($(magazineForm), '/magazines/function-update.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElem, returnedData) {
	}
});

// ========================================================
// Delete
// ========================================================
initDelete( $('[name="delete"]'), '/magazines/function-delete.php', { 'magazine_id': idElem.value }, function(formElem, returnedData) {
	
	document.body.classList.add('any--pulse');
	
	resultElem.innerHTML = returnedData.result;
	resultElem.classList.remove('any--hidden');
	
	setTimeout(function() {
		window.location = '/magazines/';
	}, 1500);
	
});

// ========================================================
// Delete attribute
// ========================================================
let deleteAttributeElems = document.querySelectorAll('[name="delete_attribute[]"]');

if( deleteAttributeElems && deleteAttributeElems.length > 0 ) {
	deleteAttributeElems.forEach(function(deleteAttributeElem) {
		
		initDelete( $(deleteAttributeElem), '/magazines/function-delete_attribute.php', { 'attribute_id': deleteAttributeElem.value }, function(formElem, returnedData) {
			
			deleteAttributeElem.closest('li').classList.add('any--fade-out');
			
			setTimeout(function() {
				deleteAttributeElem.closest('li').remove();
			}, 300);
			
		});
		
	});
}

// ========================================================
// On page load
// ========================================================

// Look for dropdowns
lookForSelectize();