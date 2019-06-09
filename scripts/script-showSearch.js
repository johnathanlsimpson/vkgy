// Activate search when clicking link on mobile

var searchLink = document.querySelector('.primary-nav__container [href="/search/"]');
var searchElem = document.querySelector('.primary-nav__search');

searchLink.addEventListener('click', function(event) {
	event.preventDefault();
	stickyNavContainer.classList.add('primary-nav--searching');
	searchElem.focus();
});
searchElem.addEventListener('blur', function() {
	stickyNavContainer.classList.remove('primary-nav--searching');
});