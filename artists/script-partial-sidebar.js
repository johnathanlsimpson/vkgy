// Init artist name pronunciation button
var pronunciationButton = document.querySelectorAll('[data-pronunciation]');
pronunciationButton.forEach(function(item, index) {
	var pronunciation = item.getAttribute('data-pronunciation');
	
	item.addEventListener('click', function() {
		pronounce(pronunciation);
		item.blur();
	});
});