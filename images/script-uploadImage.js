// Loop through image <input>s and attach updateImageData
function initImageEditElems() {
	var imageElemNames = [
		'id',
		'item_type',
		'item_id',
		'description',
		'is_default',
		'artist_id',
		'musician_id',
		'release_id',
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
}

// Function get specified parent
function getParent(childElem, parentClass) {
	var currentElem = childElem;
	var parentElem;
	var parentIsFound = false;
	
	while(!parentIsFound) {
		currentElem = currentElem.parentNode;
		
		if(currentElem.classList.contains(parentClass)) {
			parentElem = currentElem;
			
			parentIsFound = true;
		}
	}
	
	return parentElem;
}

// Update image data
function updateImageData(changedElem) {
	var parentElem = getParent(changedElem, 'image__template');
	var statusElem = parentElem.querySelector('.image__status');
	var preparedFormData = {};
	
	var inputElems = parentElem.querySelectorAll('[name]');
	inputElems.forEach(function(inputElem) {
		if(inputElem.nodeName === 'SELECT') {
			preparedFormData[inputElem.name] = Array.prototype.map.call(inputElem.selectedOptions, function(x){ return x.value });
		}
		else if(inputElem.type === 'checkbox') {
			preparedFormData[inputElem.name] = inputElem.checked ? 1 : 0;
		}
		else {
			preparedFormData[inputElem.name] = inputElem.value;
		}
	});
	
	initializeInlineSubmit($(parentElem), '/images/function-update_image.php', {
		'statusContainer' : $(statusElem),
		'preparedFormData' : preparedFormData,
	});
	
	var event = new Event('image-updated');
	event.details = {
		'parentElem': parentElem,
		'targetElem': changedElem,
	};
	document.dispatchEvent(event);
}

// Core image upload
var imageUploadElem = document.querySelector('[name=images]');
var imageTemplate = document.querySelector('#image-template');
var imagesElem = document.querySelector('.image__results');

imageUploadElem.addEventListener('change', function() {
	var itemType = imageUploadElem.parentNode.querySelector('[name=item_type]').value;
	var itemId = imageUploadElem.parentNode.querySelector('[name=item_id]').value;
	var itemName = imageUploadElem.parentNode.querySelector('[name=item_name]').value;
	var defaultDescription = imageUploadElem.parentNode.querySelector('[name=description]').value;
	
	for(var i=0; i<imageUploadElem.files.length; i++) {
		var thisImage = imageUploadElem.files[i];
		
		if(!!thisImage.type.match(/image.*/)) {
			
			var newImageElem = document.importNode(imageTemplate.content, true);
			var itemIdElem = newImageElem.querySelector('[name^=' + itemType + '_id]');
			var newOptionElem = document.createElement('option');
			
			newOptionElem.value = itemId;
			newOptionElem.innerHTML = itemName;
			newOptionElem.selected = true;
			
			itemIdElem.prepend(newOptionElem);
			
			initializeInlineSubmit($(newImageElem), '/images/function-upload_image.php', {
				'preparedFormData' : { 'image' : thisImage, 'item_type' : itemType, 'item_id' : itemId, 'default_description' : defaultDescription },
			});
			
			imagesElem.prepend(newImageElem);
			lookForSelectize();
			initImageEditElems();
			
			document.dispatchEvent(new Event('image-added'));
		}
	}
});

// Delete images
var deleteElems = document.querySelectorAll('.image__delete');
deleteElems.forEach(function(deleteElem) {
	var parentElem = getParent(deleteElem, 'image__template');
	var imageId = parentElem.querySelector('[name=id]').value;
	
	initDelete($(deleteElem), '/images/function-delete_image.php', { 'id' : imageId }, function(deleteButton) {
		parentElem.classList.add('any--fade-out');
		
		setTimeout(function() {
			parentElem.remove();
		}, 300);
	});
});

// Init elements
lookForSelectize();
initImageEditElems();