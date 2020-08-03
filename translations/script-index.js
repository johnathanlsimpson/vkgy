// Init dropdowns
lookForSelectize();

// Get 'add translation' elems
let languageElems = document.querySelectorAll('[name="language[]"]');
let translationElems = document.querySelectorAll('[name="content[]"]');
let enIdElems = document.querySelectorAll('[name="en_id[]"]');
let addTranslationElems = document.querySelectorAll('[name="add_translation[]"]');
let addTranslationButtons = document.querySelectorAll('[name="add[]"]');

// Get template for any translation proposals that will be added
let proposalTemplate = document.querySelector('#template-translation').innerHTML;

// Add translation
addTranslationElems.forEach(function(addTranslationElem, i) {
	addTranslationElem.addEventListener('submit', function(event) {
		
		event.preventDefault();
		
		// Get translation values
		let language = languageElems[i].value;
		let content = translationElems[i].value;
		let enId = enIdElems[i].value;
		let parentAddElem = addTranslationElem.querySelector('.details__add');
		let parentContainerElem = addTranslationElem.querySelector('.details__container');
		
		initializeInlineSubmit($(parentContainerElem), '/translations/function-update_translation.php', {
			preparedFormData  : { 
				language: language,
				content: content,
				en_id: enId
			},
			
			callbackOnError: function(event, returnedData) {
			},
			
			callbackOnSuccess : function(event, returnedData) {
				
				// If was edit, update existing row
				if(returnedData.is_edit) {
					
					let existingProposal = parentContainerElem.querySelector('[data-id="' + returnedData.id + '"]');
					existingProposal.innerHTML = content;
					
				}
				
				// Otherwise add new row
				else {
					
					// Create div so we can turn proposal template into node
					let newProposal = document.createElement('div');
					newProposal.innerHTML = proposalTemplate;
				
					// Update elements of new proposal
					let matches = newProposal.innerHTML.match(/\{[a-z_]+\}/gi);
					if(matches && matches.length) {
						matches.forEach(function(match) {
							
							let matchKey = match.substr(1, match.length -2);
							let replacement = returnedData[matchKey] ? returnedData[matchKey] : null;
							
							newProposal.innerHTML = newProposal.innerHTML.replace(new RegExp(match, 'g'), replacement);
							
						});
					}
					
					// Insert new proposal node before before 'add translation' row
					parentContainerElem.insertBefore(newProposal.firstElementChild, parentAddElem.previousElementSibling);
					
				}
				
			}
		});
		
		// Clear input fields
		translationElems[i].value = '';
		languageElems[i].selectize.clear();
		
	});
});

// Handle upvotes
let voteElems = document.querySelectorAll('.tag__choice');
voteElems.forEach(function(voteElem) {
	voteElem.addEventListener('change', function(event) {
		
		let voteType = voteElem.parentElement.dataset.vote;
		let isSelected = voteElem.checked ? 1 : 0;
		let id = voteElem.parentElement.dataset.id;
		let voteNumElem = voteElem.closest('.tag__voting').querySelector('[data-num-tags]');
		let otherVoteElem = voteElem.closest('.tag__voting').querySelector('.tag__' + (voteType === 'upvote' ? 'downvote' : 'upvote') + ' .tag__choice');
		
		initializeInlineSubmit($(voteElem.parentElement), '/translations/function-vote_translation.php', {
			
			preparedFormData  : { 
				id: id,
				vote_type: voteType,
				is_selected: isSelected,
			},
			
			callbackOnError: function(event, returnedData) {
				console.log('hey');
				console.log(returnedData);
			},
			
			callbackOnSuccess : function(event, returnedData) {
				console.log('hey');
				
				console.log(returnedData);
				
				// Update num votes
				voteNumElem.dataset.numTags = returnedData.num_votes;
				
				// Uncheck other vote if appropriate
				if(isSelected) {
					otherVoteElem.checked = false;
				}
				
				// Hide accepted token if necessary
				if(!returnedData.is_accepted) {
				//	voteElem.closest('.details__proposal').querySelector('.details__accepted').classList.add('any--hidden');
				}
				
				// Show accepted token
				
			}
		});
		
		
		console.log(voteElem);
		console.log(voteType, isSelected);
		
		return false;
		
	});
});