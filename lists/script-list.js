//
// Click on button to add/remove from list
//
document.addEventListener('change', function(event) {
	
	// Handle change to list checkbox (add/remove from list)
	if(event.target.classList.contains('list__choice')) {
		
		// Get checkbox input and the button that triggered it
		let listChoiceElem = event.target;
		let listButtonElem;
		
		// If checkbox is outside button, get the button via [for], otherwise it should be around choice
		if( listChoiceElem.hasAttribute('id') ) {
			listButtonElem = document.querySelector( '[for="' + listChoiceElem.getAttribute('id') + '"]' );
		}
		else {
			listButtonElem = listChoiceElem.closest('.list__button');
		}
		
		// Handle the actual change
		changeListStatus(listButtonElem, listChoiceElem);
		
	}
	
});

//
// Create new list and add item to it
//
document.addEventListener('submit', function(event) {
	
	if(event.target.classList.contains('lists__new')) {
		
		// Prevent form from submitting
		event.preventDefault();
		event.stopImmediatePropagation();
		
		// Get form elements
		let newListForm = event.target;
		let listSubmitElem = newListForm.querySelector('[name="submit"]');
		let listNameElem = newListForm.querySelector('[name="name"]');
		let listStatusElem = newListForm.querySelector('[data-role="status"]');
		
		// Get item info so we can auto add item to new list
		let itemId = newListForm.querySelector('[name="item_id"]').value;
		let itemType = newListForm.querySelector('[name="item_type"]').value;
		
		// Get list item template so we can inject it into lists
		let listButtonElem = document.querySelector('#template-list-button').content.firstElementChild;
		let listLinkElem = document.querySelector('#template-list-button').content.lastElementChild;
		let listsItemElem = document.querySelector('#template-lists-item').content.firstElementChild;
		let listsContainerElem = document.querySelector('#template-lists-container').content.firstElementChild;
		
		// Send to add_list function
		initializeInlineSubmit($(newListForm), '/lists/function-update_list.php', {
			
			callbackOnSuccess: function(formElem, returnedData) {
				
				// Update new button
				listButtonElem.dataset.itemId = itemId;
				listButtonElem.dataset.itemType = itemType;
				listButtonElem.dataset.listId = returnedData.list_id;
				listButtonElem.querySelector('.symbol__unchecked').innerHTML = returnedData.name;
				
				// Insert new button into new list item
				listsItemElem.prepend(listButtonElem);
				
				// Update link url to new list
				listLinkElem.href = returnedData.url;
				
				// Insert link after new button
				listsItemElem.append(listLinkElem);
				
				// Insert new list item into template menu
				listsContainerElem.insertBefore(listsItemElem.cloneNode(true), listsContainerElem.querySelector('.lists__item:last-of-type'));
				
				// Now before inserting new list item into open menu, make sure checkbox is checked
				listButtonElem.querySelector('.list__choice').setAttribute('checked', true);
				
				// Insert new list item into open menu
				newListForm.closest('.lists__container').insertBefore( listsItemElem, newListForm.closest('.lists__item') );
				
				// Clear the name input and blur button
				listNameElem.value = '';
				listSubmitElem.blur();
				
				// Reset other tippys
				resetListTippys();
				
				// After a second or two, remove status symbol
				setTimeout(function() {
					listStatusElem.classList = '';
				}, 1000);
				
			},
			callbackOnError: function(formElem, returnedData) {
				
				// Blur submit button
				listSubmitElem.blur();
				
				// Clear message after a while
				setTimeout(function() {
					
					listStatusElem.classList = '';
					listNameElem.value = '';
					formElem[0].querySelector('[data-role="result"]').innerHTML = '';
					
				}, 5000);
				
			}
		});
		
	}
	
});

//
// Logic for actually adding/removing from list
//
function changeListStatus(listElem, listChoiceElem, event) {
	
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

	initializeInlineSubmit($(listElem), '/lists/function-update_list_item.php', {
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
			
			// Don't want loading symbol to override error symbol
			statusElem.classList.remove('loading');
			statusElem.classList.add('symbol__error');
			console.log('error');
			console.log(formElem);
			console.log(returnedData);
			
		},
		
	});

}

//
// Init the tippy-powered dropdown of available lists
//
function initListTippys() {
	
	// Get list triggers
	let listsWrapperElems = document.querySelectorAll('.lists__wrapper:not(.tippy-active)');
	let listsContainerElem = document.querySelector('#template-lists-container');
	
	// Get json list of user's items in lists--will use to set 'checked' prop of buttons
	let currentListItems = document.querySelector('[data-contains="list-items"]').innerHTML;
	currentListItems = JSON.parse(currentListItems);
	
	// Loop through list triggers and assign tippy
	if(listsWrapperElems && listsWrapperElems.length) {
		listsWrapperElems.forEach(function(listsWrapperElem, index) {
			
			// Clone listsContainer so we can manipulate it for each dropdown button
			let tempElem = document.createElement('div');
			tempElem.innerHTML = listsContainerElem.innerHTML;
			let newListsContainerElem = tempElem.querySelector('.lists__container');
			
			// Get item data
			let itemId = listsWrapperElem.dataset.itemId;
			let itemType = listsWrapperElem.dataset.itemType;
			
			// Insert item data into 'add new' form
			newListsContainerElem.querySelector('[name="item_id"]').value = itemId;
			newListsContainerElem.querySelector('[name="item_type"]').value = itemType;
			
			// Get list buttons so we can loop through and set correct item data
			let listButtonElems = newListsContainerElem.querySelectorAll('.list__button');
			
			// Loop through buttons and set item data
			if(listButtonElems && listButtonElems.length) {
				listButtonElems.forEach(function(listButtonElem) {
					
					listButtonElem.dataset.itemId = itemId;
					listButtonElem.dataset.itemType = itemType;
					
					// Get id and 'for' attribute from list button
					let listId = listButtonElem.dataset.listId;
					let forId = listButtonElem.getAttribute('for');
					
					if( currentListItems ) {
						
						if( currentListItems[listId] && currentListItems[listId][itemType] && currentListItems[listId][itemType][itemId] ) {
							
							// Checkbox element may or may not be within button--either way, set checked to true if this item is in the list already
							if( forId ) {
								document.querySelector('#' + forId).setAttribute('checked', true);
							}
							else {
								listButtonElem.querySelector('.list__choice').setAttribute('checked', true);
							}
							
						}
					}
					
				});
			}
			
			// Get the checkbox elem for the dropdown, so that the button is appropriately styled
			let listChoiceElem = listsWrapperElem.querySelector('.lists__choice');
			
			let listTip = tippy(listsWrapperElem, {
				arrow: false,
				delay: [0, 0],
				duration: 0,
				dynamicTitle: false,
				hideOnClick: true,
				html: newListsContainerElem,
				interactive: true,
				interactiveBorder: 0,
				maxWidth: 300,
				placement: 'bottom-start',
				showOnCreate: true,
				trigger: 'click',
				onShow: function() {
					this.classList.add('lists__tippy');
					listChoiceElem.checked = true;
				},
				onHidden: function() {
					listChoiceElem.checked = false;
				},
			});
			
		});
	}
	
}

initListTippys();

//
// Reset tippys when menu has changed (new list created)
//
function resetListTippys() {
	
	// Get list wrapper elements (where tippy instance is attached)
	let listWrapperElems = document.querySelectorAll('.lists__wrapper');
	
	listWrapperElems.forEach(function(listWrapperElem) {
		
		// If tippy instance isn't visible, destroy it, and we'll re-init with new menus
		if(!listWrapperElem._tippy.state.visible) {
			listWrapperElem._tippy.destroy();
		}
		
	});
	
	initListTippys();
	
}