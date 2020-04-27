// Listen for clicks on list buttons
let listElems = document.querySelectorAll('[data-list-id]');
if(listElems && listElems.length) {
	
	// Watch checkbox inside each list button, trigger handleList when it changes
	listElems.forEach(function(listElem, i) {
		
		// List choice input is probably inside the list button, but could also be outside
		let listChoiceElem = listElem.querySelector('.input__choice');
		if(!listChoiceElem) {
			listChoiceElem = document.querySelector( '#' + listElem.getAttribute('for') );
		}
		
		// If list choice input was found, watch it for changes (a.k.a. clicks to parent)
		if(listChoiceElem) {
			listChoiceElem.addEventListener('change', handleList.bind(null, listElem, listChoiceElem));
		}
		
	});
	
}

// Handle clicks on list buttons
function handleList(listElem, listChoiceElem, event) {
	
	let listId       = listElem.dataset.listId;
	let itemId       = listElem.dataset.itemId;
	let itemType     = listElem.dataset.itemType;
	let itemIsListed = listChoiceElem.checked ? 1 : 0;
	let statusElem   = listElem.querySelector('[data-role="status"]');
	
	// Set status classes
	if(statusElem) {
		statusElem.classList.remove('success');
		statusElem.classList.add('loading');
	}

	initializeInlineSubmit($(listElem), '/releases/function-list.php', {
		preparedFormData: {
			'list_id': listId,
			'item_id': itemId,
			'item_type': itemType,
			'item_is_listed': itemIsListed
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading');
			}
			if(statusElem && !itemIsListed) {
				statusElem.classList.remove('symbol__success');
			}
			
			// If release was just collected, but release is also marked as wanted, unset wanted input and trigger change
			if(listId == 0 && itemIsListed) {
				let wantElem = document.querySelector('[data-list-id="1"][data-item-id="' + itemId + '"]');
				let wantChoicElem;
				
				if(wantElem) {
					wantChoicElem = wantElem.previousElementSibling;
					
					if(wantChoicElem && wantChoicElem.getAttribute('id').includes('release-wanted') && wantChoicElem.checked) {
						wantChoicElem.checked = false;
						wantChoicElem.dispatchEvent(new Event('change'));
					}
				}
				
			}
			
		},
		
		callbackOnError: function(formElem, returnedData) {
		},
	});

}