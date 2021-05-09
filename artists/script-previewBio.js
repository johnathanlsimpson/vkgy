var previewTimeout = null;

function previewBio(inputElem, targetElem, artistId) {
	if(previewTimeout != null) {
		clearTimeout(previewTimeout);
	}
	
	previewTimeout = setTimeout(function() {
		var content  = $(inputElem).val();
		var formData = new FormData();
		
		formData.append("content", content);
		formData.append("artist", artistId);
		
		$.ajax({
			url:         "/artists/get-bio_preview.php",
			data:        formData,
			processData: false,
			contentType: false,
			type:        "post"
		})
		.done(function(returnedData) {
			$(targetElem).html(returnedData);
		});
		
	}, 250);
}