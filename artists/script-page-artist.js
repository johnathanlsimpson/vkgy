// Get all session-band indicators and make them highlight in pairs
let sessionElems = document.querySelectorAll('session');
if(sessionElems.length) {
	sessionElems.forEach(function(sessionElem) {
		var forSession = sessionElem.getAttribute('data-for-session');
		var isSession = sessionElem.getAttribute('data-is-session');
		var siblingElem;
		
		if(forSession) {
			siblingElem = document.querySelector('session[data-is-session="' + forSession + '"]');
		}
		else {
			siblingElem = document.querySelector('session[data-for-session="' + isSession + '"]');
		}
		
		sessionElem.addEventListener('mouseover', function() {
			sessionElem.classList.add('lineup__session--hovered');
			siblingElem.classList.add('lineup__session--hovered');
		});
		sessionElem.addEventListener('mouseout', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
		});
		sessionElem.addEventListener('click', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
			sessionElem.classList.toggle('lineup__session--clicked');
			siblingElem.classList.toggle('lineup__session--clicked');
		});
	});
}