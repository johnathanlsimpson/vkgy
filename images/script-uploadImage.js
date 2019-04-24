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
		'is_queued',
		'credit'
	];
	imageElemNames.forEach(function(imageElemName) {
		var imageElems = document.querySelectorAll('[name^="image_' + imageElemName + '"]');
		
		imageElems.forEach(function(imageElem) {
			imageElem.addEventListener('change', function() {
				updateImageData(imageElem);
			});
		});
	});
}

// Init delete buttons
function initImageDeleteButtons() {
	var imageDeleteButtons = document.querySelectorAll('.image__delete');
	var itemType = document.querySelector('[name=image_item_type]').value;
	var itemId = document.querySelector('[name=image_item_id]').value;
	
	imageDeleteButtons.forEach(function(imageDeleteButton) {
		
		var parentElem = getParent(imageDeleteButton, 'image__template');
		var imageId = parentElem.querySelector('[name=image_id]').value;
		
		initDelete($(imageDeleteButton), '/images/function-delete_image.php', { 'id' : imageId, 'item_type': itemType, 'item_id': itemId }, function(deleteButton) {
			parentElem.classList.add('any--fade-out');
			
			setTimeout(function() {
				parentElem.remove();
			}, 300);
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
	var resultElem = parentElem.querySelector('.image__result');
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
	var itemType = imageUploadElem.parentNode.querySelector('[name=image_item_type]').value;
	var itemId = imageUploadElem.parentNode.querySelector('[name=image_item_id]').value;
	var itemName = imageUploadElem.parentNode.querySelector('[name=image_item_name]').value;
	var isQueued = imageUploadElem.parentNode.querySelector('[name=image_is_queued]').value;
	var defaultDescription = imageUploadElem.parentNode.querySelector('[name=image_description]').value;
	
	for(var i=0; i<imageUploadElem.files.length; i++) {
		var thisImage = imageUploadElem.files[i];
		
		if(!!thisImage.type.match(/image.*/)) {
			
			var newImageElem = document.importNode(imageTemplate.content, true);
			var itemIdElem = newImageElem.querySelector('[name^=image_' + itemType + '_id]');
			var newOptionElem = document.createElement('option');
			
			newImageElem.querySelector('[name=image_item_type]').value = itemType;
			newImageElem.querySelector('[name=image_item_id]').value = itemId;
			
			newOptionElem.value = itemId;
			newOptionElem.innerHTML = itemName;
			newOptionElem.selected = true;
			
			itemIdElem.prepend(newOptionElem);
			
			initializeInlineSubmit($(newImageElem), '/images/function-upload_image.php', {
				'preparedFormData' : { 'image' : thisImage, 'item_type' : itemType, 'item_id' : itemId, 'is_queued': isQueued, 'default_description' : defaultDescription },
				'callbackOnSuccess': function() {
				}
			});
			
			imagesElem.prepend(newImageElem);
			lookForSelectize();
			initImageEditElems();
			initImageDeleteButtons();
			
			document.dispatchEvent(new Event('image-added'));
		}
	}
});

// Handle item ID change
document.addEventListener('item-id-updated', function(event) {
	var imageItemIdElems = document.querySelectorAll('[name=image_item_id]');
	var imageIsQueuedElems = document.querySelectorAll('[name=image_is_queued]');
	var isQueued;
	
	if(imageItemIdElems.length) {
		imageItemIdElems.forEach(function(imageItemIdElem, i) {
			imageItemIdElem.value = event.details.id;
			imageItemIdElem.setAttribute('value', event.details.id);
			
			isQueued = event.details.is_queued == 1 ? 1 : 0;
			imageIsQueuedElems[i].value = isQueued;
			imageIsQueuedElems[i].setAttribute('value', isQueued);
			
			if(!imageItemIdElem.disabled) {
				imageItemIdElem.dispatchEvent(new Event('change'));
			}
			if(!imageIsQueuedElems[i].disabled) {
				imageIsQueuedElems[i].dispatchEvent(new Event('change'));
			}
		});
	}
});

// Init elements
lookForSelectize();
initImageEditElems();
initImageDeleteButtons();