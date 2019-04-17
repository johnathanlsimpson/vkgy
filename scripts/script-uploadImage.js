// Loop through image <input>s and attach updateImageData
var imageElemNames = [
	'id',
	'item_type',
	'item_id',
	'description',
	'is_default',
	'artist_ids',
	'musician_ids',
	'release_ids',
	'is_exclusive',
	'credit'
];
imageElemNames.forEach(function(imageElemName) {
	var imageElems = document.querySelectorAll('[name^="' + imageElemName + '"]');
	
	imageElems.forEach(function(imageElem) {
		imageElem.addEventListener('change', function() {
			updateImageData(imageElem);
		});
	});
});

// Update image data
function updateImageData(changedElem) {
	var parentIsFound = false;
	var currentElem = changedElem;
	var parentElem;
	
	while(!parentIsFound) {
		currentElem = currentElem.parentNode;
		
		if(currentElem.classList.contains('image__template')) {
			parentElem = currentElem;
			
			parentIsFound = true;
		}
	}
	
	var statusElem = parentElem.querySelector('.image__status');
	
	initializeInlineSubmit($(currentElem), '/images/function-update_image.php', {
		'statusContainer' : $(statusElem),
	});
}

// Core image upload
var imageUploadElem = document.querySelector('[name=images]');
var imageTemplate = document.querySelector('#image-template');
var imagesElem = document.querySelector('.image__results');

imageUploadElem.addEventListener('change', function() {
	var newImageElem = document.importNode(imageTemplate.content, true);
	
	for(var i=0; i<imageUploadElem.files.length; i++) {
		var thisImage = imageUploadElem.files[i];
		
		if(!!thisImage.type.match(/image.*/)) {
			initializeInlineSubmit($(newImageElem), '/php/function-upload_image.php', {
				'preparedFormData' : { 'image' : thisImage },
				'callbackOnSuccess' : function(returnedData) { console.log('success...?'); console.log(returnedData); }
			});
		}
	}
	
	
	
	
	
	imagesElem.prepend(newImageElem);
	//console.log(imageUploadElem, imageTemplate, imagesElem, newImageElem);
});


		//let newCommentateTemplate = document.importNode(this.commentateTemplate.content, true);


// Core image upload
/*$("[name=images]").on("change", function(event) {
	var inputElem = $(this);
	var resultContainer = $(".image__results");
	var resultTemplate = $(".image__template:first-of-type");
	
	for(var i = 0; i < $(inputElem)[0].files.length; i++) {
		$(resultTemplate).after($(resultTemplate)[0].outerHTML);
		
		var resultElem = $(resultContainer).find(".image__template:nth-of-type(2)");
		var imageElem = $(resultElem).find(".image__image");
		var statusElem = $(resultElem).find(".image__status");
		var resultTextElem = $(resultElem).find(".image__result");
		var thisFile = $(inputElem)[0].files[i];
		
		
		updateIdFor();
		lookForSelectize();
		
		$(resultElem).removeClass("any--hidden").addClass("any--fade-in");
		
		if(!!thisFile.type.match(/image./)) {
			initializeInlineSubmit($(resultElem), "/php/function-upload_image.php", {
				"statusContainer" : $(statusElem),
				"preparedFormData" : { "image" : thisFile },
				"resultContainer" : $(resultTextElem),
				"callbackOnSuccess" : function() { $(resultElem).find("[name=image_id]").trigger("change"); }
			});
		}
	}
});*/



// Delete images
$(document).on("click", ".image__delete", function() {
	var deleteButton = $(this);
	
	if(deleteButton.is("[data-id]:not([data-id=''])")) {
		var imageId = deleteButton.data("id");
		
		initDelete(deleteButton, "/php/function-delete_image.php", { "id" : imageId }, function(deleteButton) {
			var parentElem = $(deleteButton).parents(".image__template");
			
			$(parentElem).addClass("any--fade-out");
			setTimeout(function() {
				$(parentElem).addClass("any--hidden");
			}, 300);
		}, true);
	}
});