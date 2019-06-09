// Observe when 'hero' home link is visible, and increase padding in main nav

var heroHomeButton = document.querySelector('.secondary-nav__home');
var stickyNavContainer = document.querySelector('.primary-nav__container');

var navObserver = new IntersectionObserver(function(entries) {
	if(entries[0]['isIntersecting']) {
		stickyNavContainer.classList.add('primary-nav__container--hidden');
	}
	else {
		stickyNavContainer.classList.remove('primary-nav__container--hidden');
	}
});

navObserver.observe(heroHomeButton);