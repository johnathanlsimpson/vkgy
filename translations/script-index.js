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
			},
			
			callbackOnSuccess : function(event, returnedData) {
				
				// Update num votes
				voteNumElem.dataset.numTags = returnedData.num_votes;
				
				// Uncheck other vote if appropriate
				if(isSelected) {
					otherVoteElem.checked = false;
				}
				
				// Get container of entire phrase block
				let phraseContainer = voteElem.closest('.details__container');
				
				// Hide non-accepted proposals
				let acceptedElems = phraseContainer.querySelectorAll('.details__accepted[data-language="' + returnedData.language + '"]');
				if(acceptedElems) {
					
					acceptedElems.forEach(function(acceptedElem) {
						
						if(returnedData.accepted_id !== null && acceptedElem.dataset.id == returnedData.accepted_id) {
							acceptedElem.classList.remove('any--hidden');
						}
						else {
							acceptedElem.classList.add('any--hidden');
						}
						
					});
					
				}
				
			}
		});
		
		return false;
		
	});
});


// Add string
// ========================================================

// Watch 'add string' button
let addStringContainer = document.querySelector('.string__container');
let contentElem = addStringContainer.querySelector('[name="content"]');
let contextElem = addStringContainer.querySelector('[name="context"]');
let folderElem = addStringContainer.querySelector('[name="folder"]');
let idElem = addStringContainer.querySelector('[name="id"]');

// When string submitted, fire submit
initializeInlineSubmit($(addStringContainer), '/translations/function-add_string.php', {
	
	submitOnEvent: 'submit',
	
	callbackOnSuccess: function(event, returnedData) {
		
		// Clear content element
		idElem.value = '';
		contextElem.value = '';
		contentElem.value = '';
		contentElem.focus();
		
	},

	callbackOnError: function(event, returnedData) {
	}
	
});

// Watch edit links
let editStringElems = document.querySelectorAll('.accepted__edit');

// When edit string clicked, populate area down below
editStringElems.forEach(function(editStringElem) {
	editStringElem.addEventListener('click', function(event) {
		
		event.preventDefault();
		
		// Get container and values
		let editContainerElem = editStringElem.closest('.accepted__row');
		let stringContent = editContainerElem.querySelector('.accepted__en').textContent;
		let stringContextElem = editContainerElem.querySelector('.accepted__context');
		let stringContext = stringContextElem ? stringContextElem.textContent : '';
		let stringFolder = editContainerElem.querySelector('.accepted__folder').dataset.folder;
		let stringId = editContainerElem.querySelector('.id').textContent;
		
		console.log(stringFolder);
		console.log(folderElem);
		console.log(folderElem.selectize);
		
		// Fill in inputs and focus
		contextElem.value = stringContext;
		contentElem.value = stringContent;
		folderElem.selectize.setValue(stringFolder);
		idElem.value = stringId;
		contentElem.focus();
		
	});
});

// Filter/sort
// ========================================================

// Setup list.js for filtering/sorting
let listOptions = {
	valueNames: [ 'languages', 'section', 'no-filter' ]
};
let stringList = new List('translations-list', listOptions);

// Filter by string section
let filterSectionElem = document.querySelector('[name="filter_section"]');
filterSectionElem.addEventListener('change', function(event) {
	
	let filterSection = filterSectionElem.value;
	
	stringList.filter(function(item) {
		
		if(!item.values().section || item.values().section === filterSection) {
			return true;
		}
		else {
			return false;
		}
		
	});
	
});

// Filter by language
let filterLanguageElem = document.querySelector('[name="filter_language"]');
filterLanguageElem.addEventListener('change', function(event) {
	
	// Get language
	let filterLanguage = filterLanguageElem.value;
	
	// If language specified, hide columns from other languages
	let translationsContainer = document.querySelector('#translations-list');
	translationsContainer.dataset.filterLang = filterLanguage;
	
});

// On page load, filter to UI section
filterSectionElem.selectize.setValue('php');