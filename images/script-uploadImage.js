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
	
	if(changedElem.name === 'image_description') {
		var markdownElem = parentElem.querySelector('[data-get="image_markdown"]');
		var markdown = markdownElem.textContent.split('](');
		markdown = '![' + changedElem.value + '](' + markdown[1];
		markdownElem.textContent = markdown;
	}
	
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


// Set image template elements, some variables
var imageUploadElem = document.querySelector('[name=images]');
var imagesElem = document.querySelector('.image__results');
var imageTemplate = document.querySelector('#image-template');
var droppedFiles;
var isAdvancedUpload = true;


// Detect whether browser can handle drag and drop upload
isAdvancedUpload = function() {
	var div = document.createElement('div');
	return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}();


// If we can handle drag and drop, let's do so
if(isAdvancedUpload) {
	
	// Element which accepts dropped items
	let dropElem = document.querySelector('.image__drop');
	
	// Prevent default drag/drop behaviors on drop element
	['drag', 'dragstart', 'dragend', 'dragover', 'dragenter', 'dragleave', 'drop'].forEach(function(event) {
		dropElem.addEventListener(event, function(e) {
			e.preventDefault();
			e.stopPropagation();
		});
	});

	// When dragging over drop elem, add active class
	['dragover', 'dragenter'].forEach(function(event) {
		dropElem.addEventListener(event, function() {
			dropElem.classList.add('dragover');
		});
	});

	// When leave drop elem, remove active class
	['dragleave', 'dragend', 'drop'].forEach(function(event) {
		dropElem.addEventListener(event, function() {
			dropElem.classList.remove('dragover');
		});
	});

	// Core function: grab whatever was dropped and do something
	dropElem.addEventListener('drop', function(e) {
		
		// For files from system, pass directly along to uploader
		if(e.dataTransfer.files.length) {
			
			handleFiles(e.dataTransfer.files);
			
		}
		
		// If <img> elements from another website, have to create fake file and upload that
		else {
			
			// Since the drop will be a string of HTML, grab the src from the <img> element
			let dropHTML = e.dataTransfer.getData('text/html');
			let match = dropHTML && /\ssrc="?([^"\s]+)"?\s*/.exec(dropHTML);
			let dropURL = match && match[1];
			
			// If an <img> src was specified, grab it
			if(dropURL) {
				
				// Clean up dropped URL
				dropURL = cleanDroppedURL(dropURL);
				
				// Create an invisible Image canvas
				var img = new Image();
				var c = document.createElement("canvas");
				var ctx = c.getContext("2d");
				
				// This is for "if from different origin" but no clue what it does
				img.crossOrigin = '';
				
				// Set the fake <img>'s src to the URL that we grabbed earlier; this loads the image
				img.src = dropURL;
				
				// After loading the <img> src
				img.onload = function() {
					
					// Make canvas match size of <img>
					c.width = this.naturalWidth;
					c.height = this.naturalHeight;
					
					// Draw image into canvas
					ctx.drawImage(this, 0, 0);
					
					// Grab the canvas content as a PNG blob
					c.toBlob(function(blob) {
						
						// Transform the blob into a pseudo fileList and pass along to main uploader
						handleFiles( blobToFiles(blob, dropURL) );
						
					}, "image/png");
				};
				
				// In case of error loading the image
				img.onerror = function() {
					// Need something here to pass along error
				}
				
			}
			
		}
		
	});
	
}


// Given a pasted blob image, transform it into a pseudo fileList
function blobToFiles(inputBlob, dropURL) {
	
	// Set a file name based on URL; req'd for file
	let blobFileName = dropURL.split(/[\/]+/).pop();
	
	// Set a date modified; req'd for file
	let blobModified = new Date();
	
	// Transform blob into file object by adding name and type
	let outputFile = new File([inputBlob], blobFileName, {
		type: inputBlob.type
	});
	
	// Return as array so we can pretend it's a fileList
	return [ outputFile ];
}


// Given a pasted image source, try to get the best version of the image and apply proxy
function cleanDroppedURL(inputURL) {
	
	let outputURL = inputURL;
	let proxyPrefix = 'https://cors-anywhere.herokuapp.com/';
	let wixPattern = /\/v\d\/fill\/[A-z0-9_,\.\/]+/;
	let wpPattern = /-\d+x\d+\./;
	let twitterPatternA = /:[thumb|small|medium|large]$/;
	let twitterPatternB = /name=[A-z0-9]+/;
	let twitterPatternC = /_\d+x\d+\./;
	let iTunesPattern = /\d+x\d+[A-z]*\./;
	let amebaOwndPattern = /\?width=\d+/;
	
	// Remove HTML-encoded ampersands; may need broader solution
	outputURL = outputURL.replace('&amp;', '&');
	
	// If from WordPress site, attempt to get biggest ver
	if(outputURL.includes('wp-content')) {
		outputURL = outputURL.replace(wpPattern, '.');
	}
	
	// If from Wix, "
	if(outputURL.includes('wixstatic.com')) {
		outputURL = outputURL.replace(wixPattern, '');
	}
	
	// If from Twitter, "
	if(outputURL.includes('twimg.com')) {
		outputURL = outputURL.replace(twitterPatternA, 'orig');
		outputURL = outputURL.replace(twitterPatternB, 'name=orig');
		outputURL = outputURL.replace(twitterPatternC, '.');
	}
	
	// If from iTunes, "
	if(outputURL.includes('mzstatic.com')) {
		outputURL = outputURL.replace(iTunesPattern, '9999x9999.');
	}
	
	// If from Ameba Ownd, "
	if(outputURL.includes('amebaowndme.com')) {
		outputURL = outputURL.replace(amebaOwndPattern, '?');
	}
	
	// Use proxy prefix to allow grabbing CORS-protected resources
	// Will need to change whenever the proxy inevitably self-immolates
	outputURL = proxyPrefix + outputURL;
	
	// Return cleaned URL
	return outputURL;
	
}


// Core upload handler
function handleFiles(files) {
	
	// Get default variables for newly uploaded images
	var itemType = imageUploadElem.parentNode.querySelector('[name=image_item_type]').value;
	var itemId = imageUploadElem.parentNode.querySelector('[name=image_item_id]').value;
	var itemName = imageUploadElem.parentNode.querySelector('[name=image_item_name]').value;
	
	// Loop through files and upload each one
	if(files && files.length) {
		
		for(var i=0; i<files.length; i++) {
			
			// Set current image in loop
			var thisImage = files[i];
			
			// Make sure we're actually working with an image
			if(!!thisImage.type.match(/image.*/)) {
				
				// Create template parts for this image
				var newImageElem  = document.importNode(imageTemplate.content, true);
				var itemIdElem    = newImageElem.querySelector('[name^=image_' + itemType + '_id]');
				var newOptionElem = document.createElement('option');
				var thumbnailElem = newImageElem.querySelector('.image__image');
				
				// Set certain image fields to defaults specified before loop
				newImageElem.querySelector('[name=image_item_type]').value = itemType;
				newImageElem.querySelector('[name=image_item_id]').value = itemId;
				
				// This selects the appropriate ID for whatever item type this is (e.g. artist, release)
				newOptionElem.value     = itemId;
				newOptionElem.innerHTML = itemName;
				newOptionElem.selected  = true;
				itemIdElem.prepend(newOptionElem);
				
				// Set thumbnail preview while uploading
				thumbnailElem.style.backgroundImage = 'url(' + window.URL.createObjectURL(thisImage) + ')';
				
				// Using core submit function, actually upload the image
				initializeInlineSubmit( $(newImageElem), '/images/function-upload_image.php', {
					
					'preparedFormData' : { 'image' : thisImage, 'item_type' : itemType, 'item_id' : itemId },
					'callbackOnSuccess': function(event, returnedData) {
						
						// Get image that was just added
						var thisImageElem = document.querySelector('[name="image_id"][value="' + returnedData.image_id + '"]');
						thisImageElem = getParent(thisImageElem, 'image__template');
						
						// When image finished uploading, remove loading symbol
						thisImageElem.querySelector('.image__status').classList.remove('loading');
						thisImageElem.querySelector('.image__status').classList.add('symbol__' + returnedData.status);
						
						// Notify page of new image, and trigger new image to update data (to set defaults)
						thisImageElem.querySelector('[name=image_id]').dispatchEvent(new Event('change'));
						document.dispatchEvent(new Event('image-added'));
						
					}
					
				});
				
				// (Before ajax done,) append new image, add loading symbol
				newImageElem.querySelector('.image__status').classList.add('loading');
				imagesElem.prepend(newImageElem);
				
				// Init buttons in new image element
				lookForSelectize();
				initImageEditElems();
				initImageDeleteButtons();
				
			}
			
		}
		
	}
	
}


// Manually upload images via <file> input
imageUploadElem.addEventListener('change', function() {
	
	// Send files for upload
	if(imageUploadElem.files.length) {
		handleFiles(imageUploadElem.files);
	}
	
	// Make sure <file> input is empty after files are accepted; otherwise you'll get dupes
	imageUploadElem.value = '';
	
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