// Update image data
function updateImageData(parentElem) {
	var statusElem = $(parentElem).find(".image__status");
	var dataElems = $(parentElem).find("[name^=image_]");
	var formData = {};
	
	for(var i = 0; i < $(dataElems).length; i++) {
		var dataElem = $(dataElems).eq(i);
		var key = $(dataElem).attr("name").replace("image_", "");
		var value = $(dataElem).val();
		
		if(key === "is_default" || key === "is_exclusive") {
			if($(dataElem).is(":checked")) {
				formData[key] = value;
			}
		}
		else {
			formData[key] = value;
		}
	}
	
	initializeInlineSubmit($(parentElem), "/php/function-update_image.php", {
		"statusContainer" : $(statusElem),
		"preparedFormData" : formData
	});
	
	//console.log("trying to update");
}



// Trigger updateImageData() whenever image data input is changed
$(document).on("change", "[name^=image_]", function(event) {
	var parentElem = $(this).parents(".image__template");
	
	updateImageData($(parentElem));
	
	//console.log("jquery update image data");
});



// Set id/for attributes on checkbox/label pairs
function updateIdFor() {
	var checkboxes = $(".image__template .input__checkbox");
	for(var i = 0; i < checkboxes.length; i++) {
		$(".image__template .input__checkbox").eq(i).attr("id", "checkbox" + i)
		.next(".input__checkbox-label").attr("for", "checkbox" + i);
	}
}
updateIdFor();



// Core image upload
$("[name=images]").on("change", function(event) {
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
		
		if(!!thisFile.type.match(/image.*/)) {
			initializeInlineSubmit($(resultElem), "/php/function-upload_image.php", {
				"statusContainer" : $(statusElem),
				"preparedFormData" : { "image" : thisFile },
				"resultContainer" : $(resultTextElem),
				"callbackOnSuccess" : function() { $(resultElem).find("[name=image_id]").trigger("change"); }
			});
		}
	}
});



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