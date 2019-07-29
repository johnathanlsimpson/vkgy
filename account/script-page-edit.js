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