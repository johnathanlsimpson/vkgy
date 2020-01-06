// Look for dropdowns, apply selectize() when appropriate
// --------------------------------------------------------
lookForSelectize();

// Submit edits
// --------------------------------------------------------
initializeInlineSubmit($("[name=form__edit]"), "/account/function-edit.php", {
	submitOnEvent: "submit"
});

// Init inputmask() on appropriate elements
// --------------------------------------------------------
$(":input").inputmask();


// Quick style switching (need to re-do)
// --------------------------------------------------------
var themeZeroButton = document.getElementById('site_theme_0');
var themeOneButton = document.getElementById('site_theme_1');
var themeCSSLink = document.getElementById('stylesheet_theme');

themeZeroButton.onclick = function(event) {
	themeCSSLink.setAttribute('href', '/style/style-colors-0.css');
};
themeOneButton.onclick = function(event) {
	themeCSSLink.setAttribute('href', '/style/style-colors-1.css');
};


// Show/hide pronouns option
// --------------------------------------------------------
var pronounsSelector = document.querySelector('[name="pronouns"]');
var pronounsElem = document.querySelector('[name="custom_pronouns"]');
if(pronounsSelector.value === 'custom') {
	pronounsElem.classList.remove('any--hidden');
}
pronounsSelector.addEventListener('change', function(event) {
	console.log('a'); console.log(pronounsSelector.value);
	if(pronounsSelector.value === 'custom') { console.log('b');
		pronounsElem.classList.remove('any--hidden');
		pronounsElem.focus();
	}
	else { console.log('c');
		pronounsElem.classList.add('any--hidden');
	}
});