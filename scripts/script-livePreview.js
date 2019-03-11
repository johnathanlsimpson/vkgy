function loadPreview(inputElement, previewElement, urlToProcessorFunction) {
	previewElement.html("").addClass("symbol__loading");

	var inputData = new FormData();
	inputData.append("text", inputElement.val());

	$.ajax({
		url:         urlToProcessorFunction,
		data:        inputData,
		processData: false,
		contentType: false,
		type:        "post"
	})
	.done(function(returnData) {
		previewElement.removeClass("symbol__loading").html(returnData);
	});
}

function livePreview(inputElement, previewElement, urlToProcessorFunction) {
	var timer = null;
		
	loadPreview(inputElement, previewElement, urlToProcessorFunction);

	$(inputElement).keyup(function() {
		clearTimeout(timer);
		timer = setTimeout(function() {
			loadPreview(inputElement, previewElement, urlToProcessorFunction);
		}, 1000);
	});
}