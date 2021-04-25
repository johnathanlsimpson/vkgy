// Activate search when clicking link on mobile
var searchLink = document.querySelector('.primary-nav__container [href="/search/"]');
var stickyNavContainer = document.querySelector('.primary-nav__container');

searchLink.addEventListener('click', function(event) {
	event.preventDefault();
	stickyNavContainer.classList.add('primary-nav--searching');
	document.querySelector('.primary-nav__search').focus();
});
searchElem.addEventListener('blur', function() {
	stickyNavContainer.classList.remove('primary-nav--searching');
});