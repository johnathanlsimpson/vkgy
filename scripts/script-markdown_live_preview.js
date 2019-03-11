function live_preview_markdown(inputElement, previewElement, loadingElement, previewUrl) {
	function doneTyping () {
		var data = $(inputElement).serialize();
		previewElement.load(
			previewUrl,
			data,
			function() {
				loadingElement.addClass("previewloadinghidden");
			}
		);
	}

	doneTyping();

	// initialize timer
	var typingTimer;
	// set time to wait, in ms
	var doneTypingInterval = 500;

	// after pressing a key in the input element, 
	$(inputElement).keyup(function(){
		clearTimeout(typingTimer);
		if($(inputElement).val()) {
			loadingElement.removeClass("previewloadinghidden");
			typingTimer = setTimeout(doneTyping, doneTypingInterval);
		}
	});
}