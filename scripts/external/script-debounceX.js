// Debounce, from https://davidwalsh.name/javascript-debounce-function
// Eventually we need to rename this to debounce and replace all other old debounces with it
function debounceX(func, wait, immediate) {
	var timeout;
	
	return function executedFunction() {
		var context = this;
		var args = arguments;
		
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		
		var callNow = immediate && !timeout;
		
		clearTimeout(timeout);
		
		timeout = setTimeout(later, wait);
		
		if (callNow) func.apply(context, args);
	};
}