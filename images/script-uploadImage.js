//
// Helper: Trigger change
// Alpinejs can't seem to trigger change, so we wait a millisecond to trigger change after value has been updated e_e
//
function triggerChange(elem) {
	setTimeout(function() {
		elem.dispatchEvent( new Event( 'change', { bubbles:true } ) );
	}, 100);
}



// Return updated description
function getDescription(targetElem) {
	
	let imageElem = targetElem.closest('.image__template');
	
	// Default values
	let imageType = 'other';
	let isDefault = 0;
	let description = '';
	let artistName = '';
	let musicianName = '';
	let releaseName = '';
	
	// Image type
	let typeElem = imageElem.querySelector('[name^="image_type"]:checked');
	if(typeElem) {
		imageType = typeElem.closest('.input__radio').innerText;
	}
	
	// Image is default
	let defaultElem = imageElem.querySelector('[name="is_default"]:checked');
	if(defaultElem) {
		isDefault = defaultElem.value;
	}
	
	// Artist/musician/release name
	let artistElem = imageElem.querySelector('[name="image_artist_id[]"]');
	if(artistElem && artistElem.selectedIndex > -1) { 
		artistName = artistElem.options[artistElem.selectedIndex].text;
	}
	
	let musicianElem = imageElem.querySelector('[name="image_musician_id[]"]');
	if(musicianElem && musicianElem.selectedIndex > -1) { 
		musicianName = musicianElem.options[musicianElem.selectedIndex].text;
	}
	
	let releaseElem = imageElem.querySelector('[name="image_release_id[]"]');
	if(releaseElem && releaseElem.selectedIndex > -1) { 
		releaseName = releaseElem.options[releaseElem.selectedIndex].text;
	}
	
	// Prepend artist/musician/release name if necessary
	if(imageType == 1) {
		description += musicianName + ' ';
	}
	else if(imageType == 4) {
		description += releaseName + ' ';
	}
	else {
		description += artistName + ' ';
	}
	
	// Set rest of image description
	if(imageType == 'musician') {
		description += 'solo photo';
	}
	else if(imageType == 'release' && isDefault) {
		description += 'cover';
	}
	else if(imageType == 'other' || imageType == 'release') {
		description += 'photo';
	}
	else {
		description += imageType;
	}
	
	// If release specified and not release image (i.e. group photo tagged to release), mention title
	if( releaseName && imageType != 'release' ) {
		description += ' for ' + releaseName;
	}
	
	return description;
	
}


//
// Facial detection
//

// Send image to API to find faces
function getFaces(imageElem, imageUrl) {
	
	let detectedFaces = new Promise(function(response, rejection) {
		
		initializeInlineSubmit( $(imageElem), '/images/function-get_faces.php', {
			
			'preparedFormData' : { 'image_url' : imageUrl },
			
			'callbackOnSuccess': function(event, returnedData) {
				
				response(returnedData);
				
			},
			
			'callbackOnError': function(event, returnedData) {
				
				rejection(returnedData);
				
			}
			
		});
		
	});
	
	return detectedFaces;
	
}

// Given coordinates, render faces for tagging
function getFaceHtml(imageElem, imageUrl, detectedFaces) {
	
	let faceHtml = new Promise(function(response, rejection) {
		
		// Given faces, calculate bounding boxes from image
		initializeInlineSubmit( $(imageElem), '/images/function-get_face_html.php', {
			
			'preparedFormData' : { 'image_url' : imageUrl, 'faces' : detectedFaces },
			
			'callbackOnSuccess': function(event, returnedData) {
				
				response(returnedData);
				
			},
			
			'callbackOnError': function(event, returnedData) {
				
				response(returnedData);
				
			}
			
		});
		
	});
	
	return faceHtml;
	
}

// Populate the faces container with the individual faces to be tagged
async function populateFacesContainer(refElem) {
	
	let imageElem = refElem.closest('.image__template');
	let facesElem = imageElem.querySelector('[name="image_face_boundaries"]');
	let facesContainer = imageElem.querySelector('.image__faces');
	
	// First get image url and filetype
	// Btw href resolves to full path instead of relative which will cause file_exists to fail
	let imageUrl = imageElem.querySelector('.image__image').getAttribute('href');
	let imageExtension = imageUrl.split(/\./).pop();
	
	// Show button to manually add face
	facesContainer.querySelector('.face__add').classList.remove('any--hidden');
	
	// Don't bother with gifs--can only tag by reference
	if( imageExtension == 'gif' ) {
		
		facesContainer.classList.add('any--hidden');
		
	}
	
	// If not gif, move on and populate the container
	else if( !facesElem.value || !facesElem.value.length ) {
		
		facesContainer.querySelector('.face__loading').classList.remove('any--hidden');
		facesContainer.querySelector('.face__add').classList.add('any--hidden');
		
		let faces = null;
		
		let returnedFaceData = await getFaces(imageElem, imageUrl);
		
		if( returnedFaceData.status == 'success' && returnedFaceData.result ) {
			
			// Save face boundaries for later
			updateFaceBoundaries(imageElem, returnedFaceData.result);
			
		}
		
		// If we have face boundaries now, get the html representing them
		if( faces ) {
			
			let faceHtml = null;
			let returnedFaceHtml = await getFaceHtml(imageElem, imageUrl, faces);
			
			if( returnedFaceHtml.status == 'success' && returnedFaceHtml.result ) {
				
				// Insert html and init selects
				facesContainer.innerHTML = returnedFaceHtml.result + facesContainer.innerHTML;
				lookForSelectize();
				
			}
			
		}
		
		// Hide loading indicator and show button to manually add
		facesContainer.querySelector('.face__loading').classList.add('any--hidden');
		facesContainer.querySelector('.face__add').classList.remove('any--hidden');
		
	}

}

// Update the image's field which has all face boundaries from members
function updateFaceBoundaries(imageElem, faceBoundary, action = 'add') {
	
	let faceBoundariesElem = imageElem.querySelector('[name="image_face_boundaries"]');
	let extantBoundaries = faceBoundariesElem.value;
	let newBoundaries = '';
	
	// Remove brackets if necessary
	faceBoundary = faceBoundary.replace(/\[|\]/g, '', faceBoundary);
	
	// Adding boundary to empty boundaries
	if( action === 'add' && !extantBoundaries ) {
		newBoundaries = '[' + faceBoundary + ']';
	}
	
	// Adding boundary to extant boundaries
	else if( action === 'add' ) {
		newBoundaries = extantBoundaries.slice(0,-1) + ',' + faceBoundary + ']';
	}
	
	// Removing boundary from extant boundaries
	else if( action === 'remove' ) {
		newBoundaries = extantBoundaries;
		newBoundaries = newBoundaries.replace(faceBoundary, '');
		newBoundaries = newBoundaries.replace('[,', '[');
		newBoundaries = newBoundaries.replace(',]', ']');
		newBoundaries = newBoundaries.replace(',,', ',');
		newBoundaries = newBoundaries.replace('[]', '');
	}
	
	faceBoundariesElem.value = newBoundaries;
	triggerChange(faceBoundariesElem);
	
}

// Remove face
function removeFace(deleteElem) {
	
	let faceElem = deleteElem.closest('.face__container');
	let faceBoundaries = faceElem.querySelector('[data-face]').dataset.face;
	let imageElem = faceElem.closest('.image__template');
	
	// Remove face element (next step removes from DB)
	faceElem.remove();
	
	// Remove face from master list of face boundaries--triggers change event which will remove from DB
	updateFaceBoundaries(imageElem, faceBoundaries, 'remove');

}

// Given center point, calculate bounding box coordinates and get image to tag face
async function addFace(event) {
	
	let imageUrl = event.target.src;
	let imageElem = event.target.closest('.image__template');
	let facesContainer = imageElem.querySelector('.image__faces');
	
	let rectangle = event.target.getBoundingClientRect();
	let clickX = event.clientX - rectangle.left;
	let clickY = event.clientY - rectangle.top;
	
	let boxWidth = rectangle.width;
	let boxHeight = rectangle.height;
	let imageWidth = event.target.naturalWidth;
	let imageHeight = event.target.naturalHeight;
	
	let leftRatio = ( clickX - 50 ) / boxWidth;
	let widthRatio = 100 / boxWidth;
	let topRatio = ( clickY - 50 ) / boxHeight;
	let heightRatio = 100 / boxHeight;
	
	let left = Math.round(imageWidth * leftRatio);
	let width = Math.round(imageWidth * widthRatio);
	let top = Math.round(imageHeight * topRatio);
	let height = Math.round(imageHeight * heightRatio);
	
	let data = {
		'image_width': imageWidth,
		'image_height': imageHeight,
		'image_url': imageUrl,
	};
	
	let face = [{
		'start_x': left,
		'end_x': left + width,
		'start_y': top,
		'end_y': top + height,
	}];
	
	face = JSON.stringify(face);
	
	let returnedFaceHtml = await getFaceHtml(imageElem, imageUrl, face);
	
	if( returnedFaceHtml && returnedFaceHtml.status === 'success' ) {
		
		// Update master list of face boundaries
		updateFaceBoundaries(imageElem, returnedFaceHtml.face_boundaries);
		
		// Insert html and init selects
		facesContainer.innerHTML = returnedFaceHtml.result + facesContainer.innerHTML;
		lookForSelectize();
		
	}
	
}

// Trigger addFace when clicking on full image (Alpine can't handle async I guess)
document.addEventListener('click', function(event) {
	if(event.target.classList.contains('add-face__image')) {
		addFace(event);
	}
});

// When un-hiding the 'tag musicians' area, populate the faces container
// (we set it as an event cause otherwise Alpine waits for the results
// before showing the container. There's prob a better way to do it...)
document.addEventListener('show-faces', function(event) {
	populateFacesContainer(event.target);
});


// Replaces initImageEditElems
document.addEventListener('change', function(event) {
	
	let eventName = event.target.name;
	
	if( eventName.startsWith('image_') ) {
		
		updateImageData(event.target);
		
	}
	
});


// Update Markdown based on description
function getMarkdown(descriptionElem) {
	
	let parentElem = descriptionElem.closest('image__template');
	let markdownElem = parentElem.querySelector('[data-get="image_markdown"]');
	
	let markdown = markdownElem.textContent.split('](');
	markdown = '![' + descriptionElem.value + '](' + markdown[1];
	
	return markdown;
	
}


// Copy Markdown
function copyMarkdown(markdownElem) {
	
	let markdown = markdownElem.textContent;
	let dummyElem = document.createElement('textarea');
	
	// Create dummy element and insert Markdown so we can copy it e_e
	dummyElem.value = markdown;
	document.body.appendChild(dummyElem);
	dummyElem.select();
	document.execCommand("Copy");
	dummyElem.remove();
	
}


// Handle 'no default image'
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
		
		initDelete($(imageDeleteButton), '/images/function-delete_image.php', {
			'id' : imageId,
			'item_type': itemType,
			'item_id': itemId
		},
		function(deleteButton) {
			parentElem.classList.add('any--fade-out');
			
			setTimeout(function() {
				parentElem.remove();
			}, 300);
		});
	});
}


// Helper to get specified parent
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


// Manually add face to be tagged
document.addEventListener('click', function(event) {
	if(event.target.classList.contains('image__add-face')) {
		
		let linkElem = event.target;
		let imageUrl = linkElem.dataset.image;
		
	}
});


// Update image data
function updateImageData(changedElem, preparedData = false) {
	
	var parentElem = getParent(changedElem, 'image__template');
	var statusElem = parentElem.querySelector('.image__status');
	var resultElem = parentElem.querySelector('.image__result');
	let preparedFormData;
	
	// If preparedData provided, use that
	if( preparedData ) {
		
		preparedFormData = preparedData;
		
	}
	
	// Otherwise, get data from inputs
	else {
		
		preparedFormData = {};
		
		// Get all fields in this image container
		var inputElems = parentElem.querySelectorAll('[name]');
		
		// Loop through each field and transform values if necessary
		inputElems.forEach(function(inputElem) {
			
			// Selects
			if(inputElem.nodeName === 'SELECT') {
				
				// If select is musician tag, and has face json as data, treat that specially
				if( inputElem.name.startsWith('image_musician_id') && inputElem.value && inputElem.dataset.face ) {
					
					let idName = inputElem.name;
					let faceName = 'musician_face_boundaries[' + inputElem.value + ']';
					
					preparedFormData[idName] = inputElem.value;
					preparedFormData[faceName] = inputElem.dataset.face;
					
					
					/*if( !preparedFormData[inputElem.name] ) {
						preparedFormData[inputElem.name] = [];
						preparedFormData['image_musician_face[]'] = [];
					}
					
					//console.log('select value is ' + inputElem.value);
					preparedFormData[inputElem.name].push(inputElem.value);
					preparedFormData['image_musician_face[]'].push(inputElem.dataset.face);*/
					
					//preparedFormData[inputElem.name] = Array.prototype.map.call(inputElem.selectedOptions, function(x){ return x.value });
					//console.log('prepared data rn is');
					console.log(preparedFormData);
					
				//}
				}
				
				
				// For other selects, map selected options to array
				else {
					preparedFormData[inputElem.name] = Array.prototype.map.call(inputElem.selectedOptions, function(x){ return x.value });
					//console.log('prepared data rn is');
					console.log(preparedFormData);
				}
				
			}
			
			// Checkboxes
			else if( inputElem.type === 'checkbox' ) {
				preparedFormData[inputElem.name] = inputElem.checked ? 1 : 0;
			}
			
			else if( inputElem.type === 'radio' ) {
				if( inputElem.checked ) {
					preparedFormData[inputElem.name] = inputElem.value;
				}
			}
			
			// Rest of fields
			else {
				preparedFormData[inputElem.name] = inputElem.value;
			}
			
		});
		
	}
	
	// Grab element that says whether or not image is new, as we'll need to change it
	let imageIsNewElem = parentElem.querySelector('[name="image_is_new"]');
	
	// Update image data
	initializeInlineSubmit($(parentElem), '/images/function-update_image.php', {
		'statusContainer' : $(statusElem),
		'preparedFormData' : preparedFormData,
		'callbackOnSuccess': function(formElem, returnedData) {
			
			// Make sure that image is set as 'not new' after first update
			imageIsNewElem.value = 0;
			
		},
		'callbackOnError': function(formElem, returnedData) {
			
			console.log('error');
			console.log(returnedData);
			
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
				handleFiles(files[i], renderImageSection());
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
					handleFiles(dropURL, renderImageSection(), 'url');
					
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


// Set template elements for renderImageSection()
var imageUploadElem = document.querySelector('[name=images]');
var imagesElem = document.querySelector('.image__results');
var imageTemplate = document.querySelector('#image-template');


// Grab image template, clone, prepend to images area, then pass the clone so we can update it later
function renderImageSection() {
	
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
	let messageElem    = newImageElem.querySelector('.image__loading');
	
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
		messageElem: messageElem,
		itemType: itemType,
		itemId: itemId
	};
	
}


// Takes file, uploads, then updates image template
function handleFiles(input, newImageTemplateArgs, inputType = 'files') {
	
	// Set status
	let newImageElem = newImageTemplateArgs.thisImageElem;
	newImageElem.classList.add('image--loading');
	
	newImageTemplateArgs.messageElem.innerHTML = 'Uploading...';
	
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
			
			// Get certain elements
			let idElem = newImageTemplateArgs.thisImageElem.querySelector('[name="image_id"]');
			let statusElem = newImageTemplateArgs.thisImageElem.querySelector('.image__status');
			let thumbnailElem = newImageTemplateArgs.thisImageElem.querySelector('.image__image');
			
			// Using core submit function, actually upload the image
			initializeInlineSubmit( $(newImageTemplateArgs.thisImageElem), '/images/function-upload_image.php', {
				
				'preparedFormData' : { 'image' : thisImage, 'item_type' : newImageTemplateArgs.itemType, 'item_id' : newImageTemplateArgs.itemId },
				'callbackOnSuccess': function(event, returnedData) {
					
					if( typeof returnedData.is_dupe === 'undefined' || returnedData.is_dupe != 1 ) {
						
						newImageTemplateArgs.messageElem.innerHTML = 'Compressing...';
						
						// Do compression here
						initializeInlineSubmit( $(newImageTemplateArgs.thisImageElem), '/images/function-compress_image.php', {
							'preparedFormData' : { 'image_id' : returnedData.image_id, 'image_extension': returnedData.image_extension },
							'callbackOnSuccess': function(compressEvent, compressData) {
								
								newImageTemplateArgs.messageElem.innerHTML = 'Finishing up...';
								
								finishUpload(newImageElem, idElem, statusElem, thumbnailElem, returnedData);
								
							},
							'callbackOnError': function(compressEvent, compressData) {
								
								finishUpload(newImageElem, idElem, statusElem, thumbnailElem, returnedData);
								
							}
						});
						
					}
					else {
						
						newImageTemplateArgs.messageElem.innerHTML = 'Found duplicate image...';
						
						finishUpload(newImageElem, idElem, statusElem, thumbnailElem, returnedData);
						
					}
					
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


// Takes empty image section, inserts data from upload
function finishUpload(newImageElem, idElem, statusElem, thumbnailElem, returnedData) {
	
	// When image finished uploading, remove loading symbol and add status symbol
	statusElem.classList.remove('loading');
	statusElem.classList.add('symbol__' + returnedData.status);
	
	// While uploading, thumbnail may be a huge blob of image data, so replace with actual thumbnail after
	thumbnailElem.setAttribute('style', returnedData.image_style);
	
	// After ID is set init buttons in new image element
	lookForSelectize();
	//initImageEditElems();
	initImageDeleteButtons();
	
	console.log(returnedData);
	
	// If upload function suggests face detection, show the 'tag musicians' section to do that
	if(returnedData.needs_facial_detection) {
	}
	
	// Trigger change on ID elem so that new ID (and description, etc) is saved in DB
	idElem.dispatchEvent(new Event('change'));
	
	newImageElem.classList.remove('image--loading');
	
}


// Given image ID, insert into image section as if we've just uploaded it (fake upload)
function fakeUpload(imageData) {
	
	let emptyImageSection = renderImageSection();
	let newImageSection = emptyImageSection.thisImageElem;
	
	// Ok, so this is absolutely awful, and someday we need to refactor the image uploads for a 14th
	// time because it's just too much henny. But anyway, we're going to loop through and manually
	// set all the image's data in the html template. First we'll steal the loop from initImageUpload
	// to try to set some automatically from data-get, then we'll manually set some others.
	//
	// For example, if this were an actual upload, we wouldn't have a description or credit specified,
	// but since we're actually grabbing an existing image from an artist's profile, we need to make
	// sure those are maintained. We're just going to assume that release_id and musician_id aren't
	// set, which... is probably wrong in some very few cases.
	//
	// Then, we'll run finishUpload which inits all the buttons and dropdowns and shit, and then we
	// have to go back and select the artistId and update the image again to make sure the artist link
	// is preserved. It's a mess!
	
	// Loop through and auto-set data-get values
	$.each($(newImageSection).find('[data-get]'), function() {
		var key = $(this).attr('data-get');
		var value = imageData[key];
		
		if(value && value.length) {
			if($(this).is('[data-get-into]')) {
				var attribute = $(this).attr('data-get-into');
				$(this).attr(attribute, value);
			}
			else {
				$(this).html(value);
			}
		}
	});
	
	// Manually set some other values
	newImageSection.querySelector('[name="image_credit"]').value = imageData.credit;
	newImageSection.querySelector('[name="image_is_exclusive[]"]').checked = imageData.is_exclusive ? true : false;
	newImageSection.querySelector('[name="image_id"]').value = imageData.image_id;
	
	// Get some elements that we need to pass to finishUpload
	let idElem = newImageSection.querySelector('[name="image_id"]');
	let statusElem = newImageSection.querySelector('.image__status');
	let thumbnailElem = newImageSection.querySelector('.image__image');
	
	// Get artist id element, and set data-populate-on-click to false, so that
	// initSelectize will init the dropdown as soon as possible instead of on click
	// (this occurs in finishUpload), so we can take the selectize attr and set it to
	// the artist's id as soon as possible. Again, a mess.
	let artistIdElem = newImageSection.querySelector('[name="image_artist_id[]"]');
	artistIdElem.dataset.populateOnClick = false;
	
	// Send to finishUpload to clean up the template and init some edits and things
	finishUpload(newImageSection, idElem, statusElem, thumbnailElem, imageData);
	
	// Set the artist dropdown to make sure we preserve the artist link
	artistIdElem.selectize.setValue(imageData.artist_id);
	
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
						handleFiles(pastedString, renderImageSection(), 'url');
						
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
				urlToBlob(imageSrc, renderImageSection(), false);
				
			}
			
		}
		
	}
	
});


// Manually upload images via <file> input
imageUploadElem.addEventListener('change', function() {
	
	// Send files for upload
	if(imageUploadElem.files.length) {
		
		for(let i=0; i<imageUploadElem.files.length; i++) {
		
			handleFiles(imageUploadElem.files[i], renderImageSection());
			
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
//initImageEditElems();
initImageDeleteButtons();