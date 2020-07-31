// Init dropdowns
lookForSelectize();

// Get 'add translation' elems
let languageElems = document.querySelectorAll('[name="translation_language"]');
let translationElems = document.querySelectorAll('[name="translation"]');
let addTranslationElems = document.querySelectorAll('[name="add_translation"]');

// Add translation
addTranslationElems.forEach(function(addTranslationElem, i) {
	addTranslationElem.addEventListener('click', function(event) {
		
		// Get translation values
		let language = languageElems[i].value;
		let translation = translationElems[i].value;
		
		console.log(languageElems[i]);
		console.log(language);
		console.log(translation);
		
		// Clear input fields
		translationElems[i].value = '';
		languageElems[i].selectize.clear();
		
	});
});

// Handle upvotes
let voteElems = document.querySelectorAll('.tag__vote');
voteElems.forEach(function(voteElem) {
	voteElem.addEventListener('click', function(event) {
		
		let voteType = voteElem.dataset.vote;
		let wasPreviouslySelected = voteElem.querySelector('input').checked ? true : false;
		
		console.log(voteType, wasPreviouslySelected);
		
	});
});