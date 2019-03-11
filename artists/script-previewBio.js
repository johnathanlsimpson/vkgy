function previewBio(inputElem, targetElem, artistId) {
	var content  = $(inputElem).val();
	var formData = new FormData();
	
	formData.append("content", content);
	formData.append("artist", artistId);
	
	$.ajax({
		url:         "/artists/function-preview_bio.php",
		data:        formData,
		processData: false,
		contentType: false,
		type:        "post"
	})
	.done(function(returnedData) {
		$(targetElem).html(returnedData);
	});
}