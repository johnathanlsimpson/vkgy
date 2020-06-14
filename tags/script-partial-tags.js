// Listen for permanent deletions
/*let deleteTagElems = document.querySelectorAll('.tag__wrapper .tag__vote[data-action="permanent_delete"]');
if(deleteTagElems && deleteTagElems.length) {
	
	deleteTagElems.forEach(function(deleteTagElem) {
		
		// If list choice input was found, watch it for changes (a.k.a. clicks to parent)
		if(deleteTagElem) {
			deleteTagElem.addEventListener('click', handleTag.bind(null, deleteTagElem));
		}
		
	});
	
}*/

// Listen for clicks on list buttons
let tagElems = document.querySelectorAll('.tag__wrapper .tag__vote');
if(tagElems && tagElems.length) {
	
	// Watch checkbox inside each list button, trigger handleTag when it changes
	tagElems.forEach(function(tagElem) {
		
		// List choice input is probably inside the list button, but could also be outside
		let tagChoiceElem = tagElem.querySelector('.input__choice');
		if(!tagChoiceElem) {
			tagChoiceElem = document.querySelector( '#' + tagElem.getAttribute('for') );
		}
		
		// If list choice input was found, watch it for changes (a.k.a. clicks to parent)
		if(tagChoiceElem) {
			tagChoiceElem.addEventListener('change', handleTag.bind(tagElem, tagElem, tagChoiceElem));
		}
		
	});
	
}

// Handle clicks on list buttons
function handleTag(tagElem, tagChoiceElem, event) {
	
	let tagId         = tagElem.dataset.tagId;
	let itemId        = tagElem.dataset.id;
	let itemType      = tagElem.dataset.itemType;
	let vote          = tagElem.dataset.vote;
	let statusElem    = tagElem.querySelector('.tag__status');
	let numElem       = document.querySelector('.tag__wrapper .tag__num[data-tag-id="' + tagId + '"]');
	let action        = 'add';
	let oppositeVote  = vote === 'upvote' ? 'downvote' : 'upvote';
	let oppositeElems;
	
	// Check action
	if(tagChoiceElem && typeof tagChoiceElem !== 'undefined') {
		action = tagChoiceElem.checked ? 'add' : 'remove';
	}
	
	// If adding a vote, try to make sure opposite elements aren't checked
	if(action === 'add') {
		oppositeElems = document.querySelectorAll('.tag__wrapper [data-vote="' + oppositeVote + '"][data-tag-id="' + tagId + '"] .input__choice');
		oppositeElems.forEach(function(oppositeElem) {
			oppositeElem.checked = false;
		});
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

	initializeInlineSubmit($(tagElem), '/tags/function-tag.php', {
		preparedFormData: {
			'vote': vote,
			'action': action,
			'id': itemId,
			'tag_id': tagId,
			'item_type': itemType
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			
			// Debug
			//console.log(vote + action);
			//console.log(returnedData);
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading');
				statusElem.classList.remove('symbol__success');
			}
			
			// Update num upvotes
			if(numElem) {
				numElem.dataset.numTags = returnedData.num_upvotes;
			}
			
			// Hide element if necessary
			if(returnedData.hide_element) {
				
				tagElem.setAttribute('disabled', true);
				
				setTimeout(function() {
					tagElem.classList.add('any--fade-out');
				}, 1000);
				
				setTimeout(function() {
					tagElem.style.width = '0px';
					tagElem.style.marginRight = '-13px';
					tagElem.style.overflow = 'hidden';
					tagElem.style.paddingRight = '0px';
					tagElem.style.pointerEvents = 'none';
					tagElem.style.whiteSpace = 'nowrap';
				}, 1300);
				
			}
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			//console.log('error');
			//console.log(returnedData);
		}
	});

}