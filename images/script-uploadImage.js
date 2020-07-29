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
			
			// Trigger data update when these elements are changed
			imageElem.addEventListener('change', function() {
				updateImageData(imageElem);
			});
			
			// For description specifically, update Markdown while typing
			if(imageElemName === 'description') {
				imageElem.addEventListener('keyup', function() {
					updateMarkdown(imageElem);
				});
			}
			
		});
	});
}

// Update Markdown as description is altered
function updateMarkdown(descriptionElem) {
	let parentElem = getParent(descriptionElem, 'image__template');
	let markdownElem = parentElem.querySelector('[data-get="image_markdown"]');
	let markdown = markdownElem.textContent.split('](');
	markdown = '![' + descriptionElem.value + '](' + markdown[1];
	markdownElem.textContent = markdown;
}

let noDefaultElem = document.querySelector('.image__no-default:last-of-type [name="image_is_default"]');
if(noDefaultElem) {
	noDefaultElem.addEventListener('change', function() {
		let otherDefaultElems = document.querySelectorAll('.image__results .image__template [name="image_is_default"]');
		if(otherDefaultElems && otherDefaultElems.length) {
			otherDefaultElems.forEach(function(otherDefaultElem) {
				otherDefaultElem.dispatchEvent(new Event('change'));
			});
		}
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
		else if(inputElem.type === 'checkbox' || inputElem.type === 'radio') {
			preparedFormData[inputElem.name] = inputElem.checked ? 1 : 0;
		}
		else {
			preparedFormData[inputElem.name] = inputElem.value;
		}
	});
	
	// Grab element that says whether or not image is new, as we'll need to change it
	let imageIsNewElem = parentElem.querySelector('[name="image_is_new"]');
	
	// Update image data
	initializeInlineSubmit($(parentElem), '/images/function-update_image.php', {
		'statusContainer' : $(statusElem),
		'preparedFormData' : preparedFormData,
		'callbackOnSuccess': function() {
			
			// Make sure that image is set as 'not new' after first update
			imageIsNewElem.value = 0;
		}
	});
	
	var event = new Event('image-updated');
	event.details = {
		'parentElem': parentElem,
		'targetElem': changedElem,
	};
	document.dispatchEvent(event);
}


// Set image template elements, some variables
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
			dropElem.classList.add('image__drop--hover');
		});
	});

	// When leave drop elem, remove active class
	['dragleave', 'dragend', 'drop'].forEach(function(event) {
		dropElem.addEventListener(event, function() {
			dropElem.classList.remove('image__drop--hover');
		});
	});

	// Core function: grab whatever was dropped and do something
	dropElem.addEventListener('drop', function(e) {
		
		// For files from system, pass directly along to uploader
		let files = e.dataTransfer.files ? e.dataTransfer.files : null;
		let newImageSection;
		
		// Loop through each file and separately make new image section + send file to uploader
		if(files && files.length) {
			for(let i=0; i<files.length; i++) {
				handleFiles(files[i], showImageSection());
			}
		}
		
		// If <img> elements from another website, have to create fake file and upload that
		else {
			
			// Since the drop will be a string of HTML, grab the src from the <img> element
			let dropHTML = e.dataTransfer.getData('text/html');
			let match = dropHTML && /\ssrc="?([^"\s]+)"?\s*/.exec(dropHTML);
			
			// If dragged element is actually <img>
			if(match && match[1]) {
				
				// Clean URL before sending it through
				let dropURL = match && match[1];
				dropURL = cleanDroppedURL(dropURL);
				
				// If URL not empty after being cleaned
				if(dropURL) {
					
					// Grab data from URL and transform to blob (this function also sends blob to uploader, since can't pass blob back)
					handleFiles(dropURL, showImageSection(), 'url');
					
				}
				
			}
			else {
				// Dragged element is not <img>
			}
			
		}
		
	});
	
}


// Given a URL, try to grab as blob image
function urlToBlob(inputURL, newImageTemplateArgs, tryProxy = true) {
	
	// If an <img> src was specified, grab it
	if(inputURL) {
		
		// If inputURL seems to be jpeg, use that for MIME type; otherwise default to png
		let extPattern = /(\.|format=)[jpeg|jpg]/;
		let mimeType;
		let extMatch = inputURL.match(extPattern);
		if(extMatch && extMatch[0]) {
			mimeType = 'image/jpeg';
		}
		else {
			mimeType = 'image/png';
		}
		
		// Create an invisible Image canvas
		var img = new Image();
		var c = document.createElement("canvas");
		var ctx = c.getContext("2d");
		
		// This is for "if from different origin" but no clue what it does
		img.crossOrigin = '';
		
		// In case of error loading the image
		img.onerror = function(errorMessage) {
			
			// If failure came while trying proxy (a.k.a. probably got 403 forbidden), try without
			if(tryProxy) {
				urlToBlob(inputURL, newImageTemplateArgs, false);
			}
			
			// If already tried uploading w/out proxy, ...well....
			else {
				
				// Presumably there will be no bg since we're getting 403'd, so hide the symbol so it doesn't look weird/ugly by itself
				let thumbnailStatusElem = newImageTemplateArgs.thumbnailElem.querySelector('.image__status');
				thumbnailStatusElem.classList.add('any--hidden');
				
				// Display error message
				let imageResultElem = newImageTemplateArgs.thisImageElem.querySelector('.image__result');
				imageResultElem.innerHTML = 'This source has disabled image copying. Please save the image and upload it manually.';
				
				// If couldn't upload, fade out after 4 seconds, then remove element as soon as animation is done
				setTimeout(function() {
					newImageTemplateArgs.thisImageElem.classList.add('any--fade-out');
					setTimeout(function() {
						newImageTemplateArgs.thisImageElem.remove();
					}, 300);
				}, 5000);
				
			}
			
		}
		
		// Set the fake <img>'s src to the URL that we grabbed earlier; this loads the image
		img.src = tryProxy ? proxyURL(inputURL) : inputURL;
		
		// After loading the <img> src
		img.onload = function() {
			
			// Make canvas match size of <img>
			c.width = this.naturalWidth;
			c.height = this.naturalHeight;
			
			// Draw image into canvas
			ctx.drawImage(this, 0, 0);
			
			// Grab the canvas content as a PNG blob
			c.toBlob(function(blob) {
				
				// If blob successful, turn into pseudo fileList and pass to uploader
				handleFiles( blobToFiles(blob, inputURL), newImageTemplateArgs );
				
			}, mimeType);
		};
		
	}
}


// Given a pasted blob image, transform it into a pseudo fileList
function blobToFiles(inputBlob, dropURL) {
	
	// Set a file name based on URL; req'd for file
	let blobFileName = urlToFileName(inputBlob.type, dropURL);
	
	// Set a date modified; req'd for file
	let blobModified = new Date();
	
	// Transform blob into file object by adding name and type
	let outputFile = new File([inputBlob], blobFileName, {
		type: inputBlob.type
	});
	
	// Return as array so we can pretend it's a fileList
	return outputFile;
	
}


// Given an image's URL, attempt to get a pretty file name
function urlToFileName(inputBlobType, inputURL) {
	
	// Grab canonical extension from blob
	let fileType = inputBlobType.split('/');
	let fileExt = fileType[1];
	
	// Using the extension, grab a possible file name
	let fileNamePattern = new RegExp('\\/([^\\/]+?)\\.' + (fileExt === 'jpeg' ? '(jpg|jpeg)' : fileExt) + '');
	let fileNameMatch = inputURL.match(fileNamePattern);
	let fileName = (fileNameMatch && fileNameMatch[1] ? fileNameMatch[1] : 'upload') + '.' + (fileExt ? fileExt : 'png');
	
	return fileName;
	
}


// Prepend URL with proxy to avoid CORS issues
function proxyURL(inputURL) {
	
	// Use proxy prefix to allow grabbing CORS-protected resources
	// Will need to change whenever the proxy inevitably self-immolates
	let proxyPrefix = 'https://cors-anywhere.herokuapp.com/';
	proxyPrefix = 'https://pacific-hollows-34727.herokuapp.com/';
	return proxyPrefix + inputURL;
	
}


// Given a pasted image source, try to get the best version of the image and apply proxy
function cleanDroppedURL(inputURL) {
	
	// Do super crazy basic check that "URL" seems like a URL
	if(inputURL.length && inputURL.includes('/') && inputURL.includes('.')) {
		
		// Set up output and patterns
		let outputURL = inputURL;
		let wixPattern = /\/v\d\/fill\/[A-z0-9_,\.\/\%\-]+/;
		let wpPattern = /-\d+x\d+\./;
		let twitterPatternA = /:[thumb|small|medium|large]$/;
		let twitterPatternB = /name=[A-z0-9]+/;
		let twitterPatternC = /_\d+x\d+\./;
		let iTunesPattern = /\d+x\d+[A-z]*\./;
		let amebaOwndPattern = /\?width=\d+/;
		let lastfmPattern = /\/u\/\d+x\d+\//;
		
		// Make sure starts with protocol
		if(outputURL.indexOf('http') != 0) {
			outputURL = 'http://' + outputURL;
		}
		
		// Remove HTML-encoded ampersands; may need broader solution
		outputURL = outputURL.replace(/&amp;/g, '&');
		
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
		
		// If from last.fm, "
		if(outputURL.includes('lastfm.freetls.fastly.net')) {
			outputURL = outputURL.replace(lastfmPattern, '/u/');
			outputURL = outputURL.replace('.webp', '.jpg');
		}
		
		// Return cleaned URL (or nothing)
		return outputURL;
		
	}
	
}


// Set template elements for showImageSection()
var imageUploadElem = document.querySelector('[name=images]');
var imagesElem = document.querySelector('.image__results');
var imageTemplate = document.querySelector('#image-template');


// Grab image template, clone, prepend to images area, then pass the clone so we can update it later
function showImageSection() {
	
	// Get default variables for newly uploaded images
	let imageUploadParent = imageUploadElem.parentNode;
	var itemType = imageUploadParent.querySelector('[name=image_item_type]').value;
	var itemId   = imageUploadParent.querySelector('[name=image_item_id]').value;
	var itemName = imageUploadParent.querySelector('[name=image_item_name]').value;
	
	// Gather template parts for this image section
	var newImageElem   = document.importNode(imageTemplate.content, true);
	let imageIdElem    = newImageElem.querySelector('[name="image_id"]');
	var itemIdElem     = newImageElem.querySelector('[name^=image_' + itemType + '_id]');
	var newOptionElem  = document.createElement('option');
	var thumbnailElem  = newImageElem.querySelector('.image__image');
	
	// Set certain image fields to defaults specified before loop
	newImageElem.querySelector('[name=image_item_type]').value = itemType;
	newImageElem.querySelector('[name=image_item_id]').value = itemId;
	
	// This selects the appropriate ID for whatever item type this is (e.g. artist, release)
	newOptionElem.value     = itemId;
	newOptionElem.innerHTML = itemName;
	newOptionElem.selected  = true;
	itemIdElem.prepend(newOptionElem);
	
	// (Before ajax done,) append new image, add loading symbol
	newImageElem.querySelector('.image__status').classList.add('loading');
	imagesElem.prepend(newImageElem);
	
	// If this is the first image for the item, automatically make it the default image
	let isDefaultElems = document.querySelectorAll('.image__template [name="image_is_default"]');
	if(isDefaultElems.length === 1) {
		isDefaultElems[0].checked = true;
	}
	
	// Return template and a few vars
	return {
		newImageElem: newImageElem,
		thisImageElem: imagesElem.querySelector('.image__template:first-of-type'),
		thumbnailElem: thumbnailElem,
		imageIdElem: imageIdElem,
		itemType: itemType,
		itemId: itemId
	};
}


// Takes file, uploads, then updates image template
function handleFiles(input, newImageTemplateArgs, inputType = 'files') {
	
	// Define this so we don't have an error later
	let file;
	
	// If input is URL, grab blob from it (below function sends back to handleFiles, since blob doesn't pass around well)
	if(inputType === 'url') {
		urlToBlob(input, newImageTemplateArgs);
	}
	
	// If input is single file, go ahead
	else if(inputType === 'files') {
		file = input;
	}
	
	// Upload file
	if(file) {
		
		// Set current image in loop
		let thisImage = file;
		
		// Make sure we're actually working with an image
		if(!!thisImage.type.match(/image.*/)) {
			
			// Set thumbnail preview while uploading
			newImageTemplateArgs.thumbnailElem.style.backgroundImage = 'url(' + window.URL.createObjectURL(thisImage) + ')';
			
			// Flag image as new upload right before updating data
			let imageIsNewElem = newImageTemplateArgs.thisImageElem.querySelector('[name="image_is_new"]');
			imageIsNewElem.value = 1;
			
			// Using core submit function, actually upload the image
			initializeInlineSubmit( $(newImageTemplateArgs.thisImageElem), '/images/function-upload_image.php', {
				
				'preparedFormData' : { 'image' : thisImage, 'item_type' : newImageTemplateArgs.itemType, 'item_id' : newImageTemplateArgs.itemId },
				'callbackOnSuccess': function(event, returnedData) {
					
					// When image finished uploading, remove loading symbol and add status symbol
					let statusElem = newImageTemplateArgs.thisImageElem.querySelector('.image__status');
					statusElem.classList.remove('loading');
					statusElem.classList.add('symbol__' + returnedData.status);
					
					// After image is actually updated, grab the ID and insert it into the image_id elem
					let idElem = newImageTemplateArgs.thisImageElem.querySelector('[name="image_id"]');
					idElem.value = returnedData.image_id;
					
					// While uploading, thumbnail may be a huge blob of image data, so replace with actual thumbnail after
					let thumbnailElem = newImageTemplateArgs.thisImageElem.querySelector('.image__image');
					thumbnailElem.setAttribute('style', returnedData.image_style);
					
					// After ID is set init buttons in new image element
					lookForSelectize();
					initImageEditElems();
					initImageDeleteButtons();
					
					// Trigger change on ID elem so that new ID (and description, etc) is saved in DB
					idElem.dispatchEvent(new Event('change'));
					
				},
				'callbackOnError': function(event, returnedData) {
					
					// Get error result
					let imageResultElem = newImageTemplateArgs.thisImageElem.querySelector('.image__result');
					imageResultElem.innerHTML = returnedData.result;
					
					// Make sure status elem isn't stuck on loading animation forever
					let statusElem = newImageTemplateArgs.thisImageElem.querySelector('.image__status');
					statusElem.classList.remove('loading');
					statusElem.classList.add('symbol__error');
					
					// If couldn't upload, fade out after 4 seconds, then remove element as soon as animation is done
					setTimeout(function() {
						newImageTemplateArgs.thisImageElem.classList.add('any--fade-out');
						setTimeout(function() {
							newImageTemplateArgs.thisImageElem.remove();
						}, 300);
					}, 4000);
					
				}
				
			});
			
		}
		
	}
	
}


// Upload images via paste
let imagePasteElem = document.querySelector('[name="image_url"]');
imagePasteElem.addEventListener('paste', function(event) {
	
	// Loop through pasted items
	let items = event.clipboardData.items;
	if(items && items.length) {
		
		for(let i=0; i<items.length; i++) {
			
			// If pasted item is plain text (i.e. URL)
			if(items[i].type.includes('text/plain')) {
				
				// Get text object as string
				items[i].getAsString(function(pastedString) {
					
					pastedString = cleanDroppedURL(pastedString);
					
					// If URL not empty after being cleaned
					if(pastedString) {
						
						// Grab data from URL and transform to blob (this function also sends blob to uploader, since can't pass blob back)
						handleFiles(pastedString, showImageSection(), 'url');
						
					}
					
				});
				
			}
			
			// Or if pasted item is an image
			else if(items[i].type.includes('image')) {
				
				// Retrieve image on clipboard as blob, then transform it into URL
				let imageBlob = items[i].getAsFile();
				let urlObj = window.URL || window.webkitURL;
				let imageSrc = urlObj.createObjectURL(imageBlob);
				
				// Pass blob URL directly to be urlToBlob; will remake blob into an <img> and then upload it
				urlToBlob(imageSrc, showImageSection(), false);
				
			}
			
		}
		
	}
	
});


// Manually upload images via <file> input
imageUploadElem.addEventListener('change', function() {
	
	// Send files for upload
	if(imageUploadElem.files.length) {
		
		for(let i=0; i<imageUploadElem.files.length; i++) {
		
			handleFiles(imageUploadElem.files[i], showImageSection());
			
		}
	}
	
	// Make sure <file> input is empty after files are accepted; otherwise you'll get dupes
	imageUploadElem.value = '';
	
});


// If *item* ID changes (e.g. adding release or blog), update all images' item IDs to match
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