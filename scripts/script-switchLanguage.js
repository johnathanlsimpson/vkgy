// Get language switch button and container
let languageSwitchContainer = document.querySelector('.language__container');
let languageSwitchElem = languageSwitchContainer.querySelector('.language__switch');

// When language switch clicked, toggle open class on container
languageSwitchElem.addEventListener('click', function(event) {
	
	if(languageSwitchContainer.classList.contains('language--open')) {
		languageSwitchContainer.classList.remove('language--open');
	}
	else {
		languageSwitchContainer.classList.add('language--open');
	}
	
});

// Get language choices
let languageChoiceElems = languageSwitchContainer.querySelectorAll('.language__choice');

// When language choice clicked, pass to function to set session/cookie, then refresh
languageChoiceElems.forEach(function(languageChoiceElem) {
	languageChoiceElem.addEventListener('click', function(event) {
		
		// Prevent default
		event.preventDefault();
		
		// Send chosen language to switcher function
		initializeInlineSubmit($(languageSwitchContainer), '/translations/function-switch_language.php', {
			
			preparedFormData: { language: languageChoiceElem.dataset.language },
			
			// Refresh page to change language
			callbackOnSuccess: function(event, returnedData) {
				location.reload();
			},
			
			callbackOnError: function(event, returnedData) {
			}
			
		});
		
	});
});