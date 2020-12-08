document.body.addEventListener('change', function(event) {
	if(event.target.classList.contains('vote__vote')) {
		vote({ target: event.target });
	}
});

// Tagging also activates voting; expects itemId, itemType, direction, action
document.body.addEventListener('tag', function(event) {
	if(event.voteData && event.voteData.length) {
		vote(event.voteData);
	}
});

function vote(voteData = {}) {
	
	let checkboxElem;
	
	// If event target (checkboxElem) was passed through vote data, get that
	if( voteData.target ) {
		checkboxElem = voteData.target;
		delete voteData.target;
	}
	
	// Otherwise get checkbox based on vote type and item id/type
	else {
		checkboxElem = document.querySelector('.vote__container[data-item-id="' + voteData.itemId + '"][data-item-type="' + voteData.itemType + '"] .vote--' + voteData.type);
	}
	
	// Get other elements
	let labelElem = checkboxElem.closest('.vote__label');
	let containerElem = labelElem.closest('.vote__container');
	let scoreElem = containerElem.querySelector('.vote__score');
		
	// If voteData has no other entries, get data from elements
	if(!voteData.length) {
		voteData = {
			direction: labelElem.classList.contains('vote--upvote') ? 'upvote' : 'downvote',
			action: checkboxElem.checked ? 'add' : 'remove',
			itemId: containerElem.dataset.itemId,
			itemType: containerElem.dataset.itemType
		};
	}
	
	// If adding vote, make sure opposite checkbox is unchecked
	if( voteData.action === 'add' ) {
		containerElem.querySelector( '.vote--' + (voteData.direction === 'upvote' ? 'downvote' : 'upvote') + ' .vote__vote' ).checked = false;
	}
	
	// Send vote
	initializeInlineSubmit($(containerElem), '/votes/function-vote.php', {
		
		preparedFormData: voteData,
		
		callbackOnSuccess: function(formElem, returnedData) {
			
			// Update score
			scoreElem.dataset.score = returnedData.score;
			
			// Create event so tagging system etc can handle upvote
			let voteEvent = new Event('vote', {bubbles: true});
			voteEvent.data = voteData;
			checkboxElem.dispatchEvent(voteEvent);
			
			console.log('success');
			console.log(returnedData);
			
		},
		
		callbackOnError: function(formElem, returnedData) {
			
			console.log('error');
			console.log(returnedData);
			
		}
		
	});
	
}