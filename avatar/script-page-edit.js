// Activate submit button
initializeInlineSubmit($("[name=form__avatar]"), "/avatar/function-edit.php", { "submitOnEvent" : "submit" });

// Add active class to active group
var avatarGroupLinks = document.querySelectorAll('.avatar__nav .tertiary-nav__link');
avatarGroupLinks.forEach(function(elem) {
	elem.addEventListener('click', function(event) {
		var activeElem = elem.parentElement.querySelector('.tertiary-nav--active');
		if(activeElem && activeElem.classList) {
			activeElem.classList.remove('tertiary-nav--active');
		}
		elem.classList.add('tertiary-nav--active');
	});
});