// Pin/hide item
document.body.addEventListener('change', function(event) {
	if(event.target.classList.contains('tag__pin') || event.target.classList.contains('tag__hide')) {
		
		let pinCheckbox = event.target;
		let pinLabel = pinCheckbox.closest('.tag__moderation');
		
		handlePin(pinLabel, pinCheckbox);
		
	}
});

// Handle pin/hide
function handlePin(pinLabel, pinCheckbox) {
	
	let statusElem = pinLabel.querySelector('.tag__status');
	let itemsTagsId = pinLabel.dataset.itemsTagsId;
	let itemType = pinLabel.dataset.itemType;
	let direction = pinLabel.dataset.direction;
	let action = 'add';
	
	// Check action
	if(pinCheckbox && typeof pinCheckbox !== 'undefined') {
		action = pinCheckbox.checked ? 'add' : 'remove';
	}
	
	// Set status classes
	if(statusElem) {
		statusElem.classList.remove('success');
		statusElem.classList.add('loading');
		setTimeout(function() {
			
			if(statusElem) {
				statusElem.classList.remove('loading');
				statusElem.classList.remove('symbol__success');
			}
			
		}, 1000);
	}

	initializeInlineSubmit($(pinLabel), '/tags/function-pin.php', {
		preparedFormData: {
			'action': action,
			'direction': direction,
			'items_tags_id': itemsTagsId,
			'item_type': itemType
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			console.log('success');
			console.log(returnedData);
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading', 'symbol__success');
			}
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			console.log('error');
			console.log(returnedData);
		}
	});
	
	
}

// Tag item
document.body.addEventListener('change', function(event) {
	if(event.target.classList.contains('tag__checkbox')) {
		
		let tagCheckbox = event.target;
		let tagLabel = tagCheckbox.closest('.tag__label');
		
		handleTag(tagLabel, tagCheckbox);
		
	}
});

// Handle clicks on list buttons
function handleTag(tagLabel, tagCheckbox, event) {
	
	let tagId      = tagLabel.dataset.tagId;
	let itemId     = tagLabel.dataset.id;
	let itemType   = tagLabel.dataset.itemType;
	let statusElem = tagLabel.querySelector('.tag__status');
	let action     = 'add';
	
	// Check action
	if(tagCheckbox && typeof tagCheckbox !== 'undefined') {
		action = tagCheckbox.checked ? 'add' : 'remove';
	}
	
	// Set status classes
	if(statusElem) {
		statusElem.classList.remove('success');
		statusElem.classList.add('loading');
		setTimeout(function() {
			
			if(statusElem) {
				statusElem.classList.remove('loading');
				statusElem.classList.remove('symbol__success');
			}
			
		}, 1000);
	}

	initializeInlineSubmit($(tagLabel), '/tags/function-tag.php', {
		preparedFormData: {
			'action': action,
			'id': itemId,
			'tag_id': tagId,
			'item_type': itemType
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			console.log('success');
			console.log(returnedData);
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading', 'symbol__success', 'symbol__plus');
				statusElem.classList.add(action === 'add' ? 'symbol__tag' : 'symbol__plus');
			}
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			console.log('error');
			console.log(returnedData);
		}
	});

}