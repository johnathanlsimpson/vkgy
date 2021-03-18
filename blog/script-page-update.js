// When main artist is set, automatically grab artist's image

// JS version of friendly
function friendly(inputString) {
	
	let outputString = inputString;
	
	// Replace all non-alphanumeric characters with hyphens
	outputString = outputString.replace(/[^A-Za-z0-9]/g, '-');
	
	// Eliminate double hyphens
	outputString = outputString.replace(/-{2,}/g, '-');
	
	// Eliminate starting/ending hyphens
	outputString = outputString.replace(/^-|-$/g, '');
	
	// Make sure output is at least one hyphen
	outputString = outputString.length ? outputString : '-';
	
	// Make lowercase
	outputString = outputString.toLowerCase();
	
	return outputString;
	
}

// Set up junks
let formElem = document.querySelector('[name="form__update"]');
let titleElem = formElem.querySelector('[name="name"]');
let friendlyElem = formElem.querySelector('[name="friendly"]');
let friendlyEditLink = formElem.querySelector('.friendly__edit-link');
let urlSlugElem = formElem.querySelector('.friendly__slug');
let saveContainerElem = formElem.querySelector('.save__container');
let previewTitleElem = formElem.querySelector('.preview__title');
let contentElem = formElem.querySelector('[name="content"]');
let previewStatusElem = formElem.querySelector('.preview__status');
let previewContentElem = formElem.querySelector('.update__preview');
let isQueuedElem = formElem.querySelector('[name="is_queued"]');
let dateScheduledElem = formElem.querySelector('[name="date_scheduled"]');
let timeScheduledElem = formElem.querySelector('[name="time_scheduled"]');
let scheduledElem = formElem.querySelector('.save__scheduled');
let linkElem = formElem.querySelector('.save__link');
let saveStatusElem = formElem.querySelector('.save__status');
let submitButton = formElem.querySelector('[name="form__update"] [name="submit"]');
let idElem = formElem.querySelector('[name="id"]');

let datePreviewElem  = formElem.querySelector('.preview__datetime');
let dateOccurredElem = formElem.querySelector('[name="date_occurred"]');
let sourcesElem = document.querySelector('[name="sources"]');
let supplementalElem = document.querySelector('[name="supplemental"]');


// Author
// ========================================================

let authorPreviewElem  = formElem.querySelector('.preview__user a');
let userIdElem = formElem.querySelector('[name="user_id"]');

// Update author preview
userIdElem.addEventListener('change', function() {
	let authorUsername = userIdElem.querySelector('option[selected]').innerHTML;
 authorPreviewElem.href = '/users/' + authorUsername + '/';
	authorPreviewElem.innerHTML = authorUsername;
});

// Update slug and preview link when friendly is updated
function updateUrlSlug() {
	if(friendlyElem && urlSlugElem) {
		urlSlugElem.innerHTML = friendlyElem.value;
	}
	if(friendlyElem && linkElem) {
		linkElem.href = '/blog/' + friendlyElem.value + '/';
	} 
}
friendlyElem.addEventListener('keyup', function() {
	updateUrlSlug();
});
friendlyElem.addEventListener('change', function() {
	updateUrlSlug();
});

// Auto udate friendly
function updateFriendly() {
	
	// Only autochange friendly if article is unpublished and isn't translation
	if(!checkState('published') && !checkState('translation')) {
		if(titleElem && friendlyElem) {
			
			// If title is set, put through friendly function, then update friendly elem
			let newFriendly = titleElem.value;
			if(newFriendly.length) {
				newFriendly = friendly(newFriendly);
				friendlyElem.value = newFriendly;
				friendlyElem.dispatchEvent(new Event('change'));
			}
			
		}
	}
}

// Auto update title preview
function updatePreviewTitle() {
	let newTitle = titleElem.value;
	previewTitleElem.innerHTML = newTitle ? newTitle : 'Untitled';
}

// Update friendly and preview title when title is changed
titleElem.addEventListener('keyup', function() {
	updateFriendly();
	updatePreviewTitle();
});

// Unhide and focus on friendly input when edit link is clicked
friendlyEditLink.addEventListener('click', function() {
	this.classList.add('any--hidden');
	setTimeout(function() {
		friendlyElem.focus();
	}, 1);
});



// Previews
// ========================================================

// Preview content
function previewContent(inputElem, statusElem, outputElem) {
	
	// Get updated input text and set as formData
	let inputText = inputElem.value;
	
	// Send text to Markdown parser and update preview element
	if(inputText.length) {
		initializeInlineSubmit($(inputElem), "/blog/function-preview_entry.php", {
			preparedFormData  : { content: inputText, sources: sourcesElem.value, supplemental: supplementalElem.value },
			statusContainer   : $(statusElem),
			resultContainer   : $(outputElem),
			preserveResult    : true,
			callbackOnSuccess : function(event, returnedData) {
				
				// If summary was supplied, update that element too
				if(returnedData.summary) {
					let summaryPreviewElem = document.querySelector('.preview__summary');
					summaryPreviewElem.innerHTML = returnedData.summary;
				}
				
				// Check if main artist was updated
				updateMainArtist(returnedData.artist);
				
				// Remove check mark
				setTimeout(function() {
					statusElem.classList.remove('symbol__success');
				}, 500);
				
				// Re-enable lazy load on images in preview
				var lazyLoad = new LazyLoad();
				
			}
		});
	}
	else {
		outputElem.innerHTML = '';
	}
	
}

// Preview initial content on page load
previewContent(contentElem, previewStatusElem, previewContentElem);

// When content changes, update preview
['change', 'input', 'paste', 'propertychange'].forEach( event => contentElem.addEventListener( event, function() {
	previewContent(contentElem, previewStatusElem, previewContentElem);
}, false) );

// Trigger autosave when using rich editor (has debounce built in)
['change', 'propertychange'].forEach( event => contentElem.addEventListener( event, function() {
	autosaveEntry();
}, false) );

// Trigger autosave when using plain editor (no debounce)
['input', 'paste'].forEach( event => contentElem.addEventListener( event, debounce( () => {
	autosaveEntry();
}, 350 ), false ) );

// Auto save
function autosaveEntry() {
	
	// If entry not yet saved, but title is entered, and at least a little content, let's save it as draft
	if(checkState('queued') && titleElem.value.length && friendlyElem.value.length && contentElem.value.length) {
		
		saveEntry();
		
	}
	
}

// When sources updated, update preview
['change'].forEach( event => sourcesElem.addEventListener( event, function() {
	previewContent(contentElem, previewStatusElem, previewContentElem);
} ) );

// When supplemental updated, update preview
['change'].forEach( event => supplementalElem.addEventListener( event, function() {
	previewContent(contentElem, previewStatusElem, previewContentElem);
} ) );

// When title updated, update page title
titleElem.addEventListener('change', function() {
	document.title = 'Edit: ' + titleElem.value + ' | vk.gy (ブイケージ)';
});



// Artist
// ========================================================

let artistIdElem = document.querySelector('[name="artist_id"]');

// Automatically set main artist from article preview
function updateMainArtist(inputArtist) {
	
 let previewArtistLink = document.querySelector('.artist__link');
 let previewArtistName = document.querySelector('.artist__name');
 let previewArtistRomaji = document.querySelector('.artist__romaji');
	
	if(previewArtistLink && inputArtist && inputArtist.id.length) {
		
		// Show artist link
		previewArtistLink.dataset.id = inputArtist.id;
		previewArtistLink.href = '/artists/' + inputArtist.friendly + '/';
		previewArtistName.innerHTML = inputArtist.name;
		previewArtistRomaji.innerHTML = inputArtist.romaji;
		
		// Update dropdown
		artistIdElem.selectize.setValue(inputArtist.id);
		
		// Get artist's image as article image
		getArtistImage(inputArtist.id);
		
		updateDefaultImageArtist( inputArtist.id, inputArtist.romaji ? inputArtist.romaji + '(' + inputArtist.name + ')' : inputArtist.name );
		
	}
	else {
		
		// Clear artist link and dropdown
		if(previewArtistLink) {
			previewArtistLink.dataset.id = '';
		}
		artistIdElem.selectize.clear();
		
	}
	
}

// Set artist image as article image
function getArtistImage(artistId) {
	
	let existingImage = document.querySelector('.image__template');
	
	// If no image set, grab artist's image and set default
	if( !existingImage ) {
			
		initializeInlineSubmit($('<div></div>'), '/blog/function-get_artist_image.php', {
			
			preparedFormData: { artist_id: artistId },
			
			callbackOnSuccess: function(event, returnedData) {
				
				// Pass data to fakeUpload from script-uploadImage.js
				fakeUpload(returnedData);
				
				// Get is_default and check it
				document.querySelector('.image__template [name="image_is_default"]').checked = true;
				
			},
			
			callbackOnError: function(event, returnedData) {
				
			}
			
		});
		
	}
	
}

// Page states
// ========================================================

// Get elems affected by state
let headingTitleElem = document.querySelector('.entry__title');
let headingDefaultTitleElem = document.querySelector('.entry__default-title');
let editNavLinkElem = document.querySelector('.tertiary-nav--active');

// Copy edit link in tertiary nav and turn into view link (active when editing)
let viewNavLinkElem = friendlyElem.value ? document.querySelector('.tertiary-nav__link[href="/blog/' + friendlyElem.value + '/"]') : null;
if(!viewNavLinkElem) {
	viewNavLinkElem = editNavLinkElem.cloneNode(true);
	viewNavLinkElem.innerHTML = 'Preview article';
	viewNavLinkElem.href = '';
	viewNavLinkElem.classList.remove('tertiary-nav--active');
	viewNavLinkElem.classList.add('any--hidden');
	editNavLinkElem.parentNode.insertBefore(viewNavLinkElem, editNavLinkElem.nextSibling);
}

// Change states (e.g. add article -> edit article)
function changeState(state) {
	
	// When changing from 'add article' to 'edit article'
	if( state === 'edit' && !document.body.classList.contains('article--edit') ) {
		
		// Update heading title and hide default title
		headingTitleElem.innerHTML = titleElem.value;
		headingDefaultTitleElem.classList.add('any--hidden');
		
		// Update text of edit link
		editNavLinkElem.innerHTML.replace('Add', 'Edit');
		
		// Unhide preview link and update URL
		viewNavLinkElem.href = '/blog/' + friendlyElem.value + '/';
		viewNavLinkElem.classList.remove('any--hidden');
		
		// Update history
		if(window.location.pathname != '/blog/' + friendlyElem.value + '/edit/') {
			document.title = 'Edit: ' + titleElem.value + ' | vk.gy (ブイケージ)';
			history.pushState('', '', '/blog/' + friendlyElem.value + '/edit/');
		}
		
		// Add class to body
		document.body.classList.add('article--edit');
		
	}
	
	else if( state === 'add' && document.body.classList.contains('article--edit') ) {
		
		// Hide heading title and show default title
		headingTitleElem.innerHTML = '';
		headingTitleElem.classList.add('any--hidden');
		headingDefaultTitleElem.classList.remove('any--hidden');
		
		// Update text of edit link
		editNavLinkElem.innerHTML.replace('Edit', 'View');
		
		// Hide preview link and clear URL
		viewNavLinkElem.href = '';
		viewNavLinkElem.classList.add('any--hidden');
		
		// Update history
		if(window.location.pathname != '/blog/add/') {
			document.title = 'Add article | vk.gy (ブイケージ)';
			history.pushState('', '', '/blog/add/');
		}
		
		// Remove class to body
		document.body.classList.remove('article--edit');
		
	}
	
}

// Check current state of form
function checkState(stateType) {
	
	let state;
	let stateElem = document.querySelector('.save__container');
	
	if(stateType === 'published') {
		state = stateElem.dataset.isPublished;
	}
	else if(stateType === 'saved') {
		state = stateElem.dataset.isSaved;
	}
	else if(stateType === 'queued') {
		state = stateElem.dataset.isQueued;
	}
	else if(stateType === 'translation') {
		state = stateElem.dataset.isTranslation;
	}
	
	return state == 1 ? true : false;
	
}


// Update 'is scheduled' state
['keyup', 'change'].forEach( event => dateScheduledElem.addEventListener(event, function() {
	checkIfScheduled();
}) );
['keyup', 'change'].forEach( event => timeScheduledElem.addEventListener(event, function() {
	checkIfScheduled();
}) );

// If scheduled, make sure set to draft
function checkIfScheduled() {
	if(dateScheduledElem.value.match(/\d{4}-\d{2}-\d{2}/) && timeScheduledElem.value.match(/\d{2}:\d{2}/)) {
		
		// Update notice that entry is scheduled
		scheduledElem.querySelector('.any__note').innerHTML = dateScheduledElem.value + ' ' + timeScheduledElem.value;
		
		// Set flag that entry is scheduled
		saveContainerElem.dataset.isScheduled = 1;
		
		// Make sure entry is set to draft and can't be un-drafted
		isQueuedElem.checked = true;
		isQueuedElem.disabled = true;
		isQueuedElem.dispatchEvent(new Event('change'));
		
		// Update date preview
		datePreviewElem.innerHTML = dateScheduledElem.value + ' ' + timeScheduledElem.value;
		
	}
	else {
		
		// Unset scheduled flag
		saveContainerElem.dataset.isScheduled = 0;
		
		// Re-allow to switch from draft to published
		isQueuedElem.disabled = false;
		
		// Reset preview to date_occurred
		datePreviewElem.innerHTML = dateOccurredElem.value;
		
	}
}

// Update 'is draft' state, and reset save status
isQueuedElem.addEventListener('change', function() {
	saveContainerElem.dataset.isQueued = isQueuedElem.checked ? '1' : '0';
	saveStatusElem.classList.remove('symbol__success', 'symbol__error', 'symbol__loading');
	
	var e = new Event('item-id-updated');
	e.details = {
		'id' : idElem.value,
		'is_queued' : isQueuedElem.checked ? 1 : 0,
	};
	document.dispatchEvent(e);
	
});

// Init inputmask() on appropriate elements
var inputMaskElems = document.querySelectorAll('[data-inputmask]');
inputMaskElems.forEach(function(inputMaskElem) {
	$(inputMaskElem).inputmask();
});

// Autosize
autosize($(".autosize"));

// Update preview image
document.addEventListener('image-updated', function(event) {
	
	// If 'is default' was changed, or if image ID changed and has 'is default' checked (i.e. uploaded image to brand new post and 'is default' was auto set), update image preview
	if( event.details.targetElem.name === 'image_is_default' || (event.details.targetElem.name === 'image_id' && event.details.parentElem.querySelector('[name="image_is_default"]').checked) ) {
		
		var imagePreviewElem = document.querySelector('.update__image');
		
		if(event.details.targetElem.checked || event.details.parentElem.querySelector('[name="image_is_default"]').checked) {
			var newImageStyle = event.details.parentElem.querySelector('.image__image').style.backgroundImage;
			
			newImageStyle = newImageStyle.replace('.thumbnail.', '.large.');
			
			imagePreviewElem.style.backgroundImage = newImageStyle;
		}
		else {
			imagePreviewElem.style.backgroundImage = '';
		}
		
	}
});

// Init delete button
function initDeleteButton() {
	var deleteButton = document.querySelector('[name=delete]');
	var newDeleteButton = deleteButton.cloneNode(true);
	deleteButton.parentNode.replaceChild(newDeleteButton, deleteButton);
	
	deleteButton = document.querySelector('[name=delete]');
	deleteButton.setAttribute('data-state', null);
	
	initDelete( $(deleteButton), '/blog/function-delete_entry.php',
		{
			'id': deleteButton.getAttribute('data-id'),
			'is_translation': deleteButton.getAttribute('data-is-translation')
		},
		function() {
			changeState('add');
			window.location.href = '/blog/add/';
		}
	);
}

initDeleteButton();

// Save entry
function saveEntry() {
	
	let isFirstAutosave = !idElem.value.length && checkState('queued') ? 1 : 0;
	
	// Submit
	initializeInlineSubmit($('[name=form__update]'), '/blog/function-update_entry.php', {
		
		showEditLink: checkState('queued') ? false : true,
		
		callbackOnError: function(event, returnedData) {
			
			// Change state to show data not saved
			saveContainerElem.dataset.isSaved = '0';
			
		},
		
		callbackOnSuccess: function(event, returnedData) {
			
			// Re-initialize delete button (assuming ID changed)
			initDeleteButton();
			
			// Set flag if was first autosave for draft (i.e. post created automatically)
			saveContainerElem.dataset.isFirstAutosave = isFirstAutosave;
			
			// Updating showing entry was saved
			saveContainerElem.dataset.isSaved = '1';
			
			// Update 'is published' flag, and also update nav links based on state
			if(saveContainerElem.dataset.isQueued == '0') {
				saveContainerElem.dataset.isPublished = '1';
				editNavLinkElem.innerHTML = 'Edit article';
				viewNavLinkElem.innerHTML = 'View article';
			}
			else {
				saveContainerElem.dataset.isPublished = '0';
				editNavLinkElem.innerHTML = 'Edit draft';
				viewNavLinkElem.innerHTML = 'Preview draft';
			}
			
			// Trigger event showing that ID was changed for first time
			if(isFirstAutosave) {
				var e = new Event('item-id-updated');
				e.details = {
					'id' : returnedData.id,
					'is_queued' : returnedData.is_queued,
				};
				document.dispatchEvent(e);
			}
			
			// Make save status reset
			setTimeout(function() {
				saveStatusElem.classList.remove('symbol__success');
			}, 2000);
			
			// De-focus submit button
			submitButton.blur();
			
		}
		
	});
	
	// Update page state
	changeState('edit');
	
}

// Fire save on form submit
formElem.addEventListener('submit', function(event) {
	event.preventDefault();
	saveEntry();
});


// SNS images
// ========================================================

// Attempt to see when images are added
document.addEventListener('image-updated', function() {
	
	// Get current image elements
	let currentImages = document.querySelectorAll('.image__results .image__template .image__image');
	let snsImageContainer = document.querySelector('.sns__img-container');
	let currentSnsImageId = document.querySelector('[name="override_image_id"]:checked');
	
	// Clear current list of SNS images
	snsImageContainer.innerHTML = '';
	
	if(currentImages && currentImages.length) {
		
		// Loop through current images, create new SNS label, insert into SNS image list
		currentImages.forEach(function(currentImage) {
			
			// Create new thumbnail template
			let snsImageTemplate = document.querySelector('#template-sns').innerHTML;
			
			// Replace thumbnail attributes from image, then clear remaining
			let snsImageId = currentImage.getAttribute('href').split(/\/images\/|\./)[1];
			let snsImageThumbnail = currentImage.getAttribute('href').replace('.', '.thumbnail.');
			
			snsImageTemplate = snsImageTemplate.replace('{image_id}', snsImageId);
			snsImageTemplate = snsImageTemplate.replace('{image_thumb}', snsImageThumbnail);
			snsImageTemplate = snsImageTemplate.replace('{is_checked}', currentSnsImageId && currentSnsImageId.value == snsImageId ? 'checked' : '');
			snsImageTemplate = snsImageTemplate.replace(/{.+?}/g, '');
			
			// Create div so we can turn thumbnail template into node
			let newSnsImage = document.createElement('div');
			newSnsImage.innerHTML = snsImageTemplate;
			
			// Insert new node before last element (add button) of parent wrapper
			snsImageContainer.insertBefore(newSnsImage.firstElementChild, snsImageContainer.lastElementChild);
			
		});
		
	}
	
});

// Listen for SNS image changes
let snsImgPreviewElem = document.querySelector('.sns__image');
function initSnsImageListener(elem) {
	elem.addEventListener('change', function() {
		let snsImgThumb = '/images/' + elem.value + '.small.jpg';
		snsImgPreviewElem.style.backgroundImage = 'url(' + snsImgThumb + ')';
	});
}

// Init SNS image update listener at load
let overrideImageIdElems = document.querySelectorAll('[name="override_image_id"]');
overrideImageIdElems.forEach(function(elem) {
	initSnsImageListener(elem);
});

// Grab SNS image on initial load
let snsImgOnLoad = document.querySelector('[name="override_image_id"]:checked');
if(snsImgOnLoad) {
		let snsImgThumb = '/images/' + snsImgOnLoad.value + '.small.jpg';
		snsImgPreviewElem.style.backgroundImage = 'url(' + snsImgThumb + ')';
}


// Generate SNS post
// ========================================================

// Field elems
let contributorIdsElem = document.querySelector('[name="contributor_ids[]"]');

// Set preview elems
let tweetPreviewContainer = document.querySelector('.sns__container');
let tweetPreviewLength = document.querySelector('.sns__length');

// Set preview parts
let tweetPreviewHeading = document.querySelector('.tweet__heading .sns__text');
let tweetPreviewBody = document.querySelector('.tweet__body .sns__text');
let tweetPreviewMentions = document.querySelector('.tweet__mentions .sns__text');
let tweetPreviewAuthors = document.querySelector('.tweet__authors .sns__text');

// Set override elems
let overrideBodyElem = document.querySelector('[name="override_body"]');
let overrideoverrideTwitterMentionsElem = document.querySelector('[name="override_twitter_mentions"]');
let overrideoverrideTwitterAuthors = document.querySelector('[name="override_twitter_authors"]');
//let overrideoverrideAuthors = document.querySelector('[name="override_authors"]');

// Override SNS parts
function initOverrideSns(inputElem, previewElem) {
	
	['keyup', 'change'].forEach(function(event) {
		
		inputElem.addEventListener(event, function() {
			
			// Get value of input element and replace linebreaks
			let inputValue = inputElem.value;
			inputValue = inputValue.replace(/\n/g, '<br />');
			
			// If value has length, override specified preview element, then update length
			if(inputValue.length) {
				previewElem.innerHTML = inputValue;
				updateSnsLength();
			}
			
			// Otherwise input has been cleared and we assume they want original text back, so rerun getSnsPost()
			else {
				getSnsPost();
			}
			
		});
		
	});
	
}

// Init override listeners
initOverrideSns(overrideBodyElem, tweetPreviewBody);
initOverrideSns(overrideoverrideTwitterMentionsElem, tweetPreviewMentions);
initOverrideSns(overrideoverrideTwitterAuthors, tweetPreviewAuthors);

// Get SNS preview and insert into preview elem
function getSnsPost() {
	
	// Get tag names => used for post type
	let tagElems = document.querySelectorAll('[name="tags[]"]:checked');
	let tags = [];
	if(tagElems && tagElems.length) {
		tagElems.forEach(function(tagElem) {
			tags.push(tagElem.dataset.friendly);
		});
	}
	
	// Get title => used for body
	let title = titleElem.value;
	
	// Get ID => used for translations
	let id = idElem.value;
	
	// Get artist ID => used for twitter mentions
	let artistId = artistIdElem.value;
	
	// Get user ID and contributor IDs => used for twitter authors and regular authors
	let userId = userIdElem.value;
	let contributorIds = [];
	if(contributorIdsElem.selectedOptions) {
		let selectedOptions = Array.from(contributorIdsElem.selectedOptions).map(o => o.value);
		selectedOptions.forEach(function(option) {
			contributorIds.push(option);
		});
	}
	
	// Get friendly and put together URL => URL
	let url = 'https://vk.gy/blog/' + friendlyElem.value + '/';
	
	// Get overrides
	let overrideBody = overrideBodyElem.value;
	let overrideTwitterMentions = overrideoverrideTwitterMentionsElem.value;
	let overrideTwitterAuthors = overrideoverrideTwitterAuthors.value;
	//let overrideAuthors = overrideoverrideAuthors.value;
	
	// Build form data
	let snsData = {
		tags:                      tags,
		title:                     title,
		id:                        id,
		artist_id:                 artistId,
		user_id:                   userId,
		contributor_ids:           contributorIds,
		url:                       url,
		override_body:             overrideBody,
		override_twitter_mentions: overrideTwitterMentions,
		override_twitter_authors:  overrideTwitterAuthors,
		//override_authors:          overrideAuthors
	}
	
	// Get preview element
	previewSnsElem = document.querySelector('.sns__container');
	
	// Get SNS preview
	initializeInlineSubmit($(previewSnsElem), '/blog/function-generate_sns.php', {
		
		preparedFormData: snsData,
		
		callbackOnSuccess: function(event, returnedData) {
			
			// Update character count
			updateSnsLength();
			
		},
		
		callbackOnError: function(event, returnedData) {
			
		}
		
	});
	
}

// Update length counter
function updateSnsLength() {
	
	// Get plain text of tweet preview and strip multiple spaces
	let tweetContent = tweetPreviewContainer.textContent;
	tweetContent = tweetContent.replace(/\s+/g, ' ');
	tweetContent = tweetContent.replace(/^\s|\s$/g, '');
	
	// Insert into dummy textarea and get text back out to decode entities
	let dummyElem = document.createElement('textarea');
	dummyElem.innerHTML = tweetContent;
	tweetContent = dummyElem.value;
	
	// Update length element
	tweetPreviewLength.dataset.length = tweetContent.length;
	
	// Set flag if too long
	if(tweetContent.length > 240) {
		tweetPreviewLength.classList.add('sns--long');
	}
	else {
		tweetPreviewLength.classList.remove('sns--long');
	}
	
}

// Fire SNS preview update when certain fields are changed
let interviewTagElem = document.querySelector('[name="tags[]"][data-friendly="interview"]');
[ titleElem, userIdElem, contributorIdsElem, artistIdElem, interviewTagElem ].forEach(function(elem) {
	elem.addEventListener('change', function() {
		getSnsPost();
	});
});

getSnsPost();


// Generate preview link
// ========================================================

// Get elems
let generatePreviewLinkElem = document.querySelector('.preview__generate-link');
let previewLinkElem = document.querySelector('.preview__link');
let tokenElem = formElem.querySelector('[name="token"]');

// Generate preview token
function generatePreviewToken() {
	return Math.random().toString(36).slice(2).substring(0,6);
}

// Generate new preview URL
generatePreviewLinkElem.addEventListener('click', function(event) {
	event.preventDefault();
	
	// Get new token and URL
	let previewToken = generatePreviewToken();
	let previewURL = 'https://vk.gy/blog/' + friendlyElem.value + '/&preview=' + previewToken;
	
	// Update link
	previewLinkElem.href = previewURL;
	previewLinkElem.innerHTML = previewURL;
	
	// Update field
	tokenElem.value = previewToken;
	
});

// Update preview URL when friendly changed
friendlyElem.addEventListener('change', function() {
	
	// Get new URL
	let previewURL = 'https://vk.gy/blog/' + friendlyElem.value + '/&preview=' + tokenElem.value;
	
	// Update link
	previewLinkElem.href = previewURL;
	previewLinkElem.innerHTML = previewURL;
	
});


// Generate translation
// ========================================================

// Get translation elems
let addTranslationButton = document.querySelector('.translation__add');
let generateTranslationButton = document.querySelector('.translation__generate');
let translationLanguageElem = document.querySelector('.translation__language');
let generateContainerElem = document.querySelector('.translation__generation');
let translationContainerElem = document.querySelector('.translation__container');

// Show language generation container after clicking add translation
if(addTranslationButton) {
	addTranslationButton.addEventListener('click', function(event) {
		
		// Show language generation container
		event.preventDefault();
		generateContainerElem.classList.remove('any--hidden');
		
	});
}

// Generate new translation
if(generateTranslationButton) {
	generateTranslationButton.addEventListener('click', function(event) {
		event.preventDefault();
		
		// Get translation ID and name
		let translationLanguage = translationLanguageElem.value;
		let translationName = translationLanguageElem.options[translationLanguageElem.selectedIndex].text;
		
		// If valid language selected, send to generator
		if(translationLanguage && translationName) {
			
			// Create new <li> with current language and status elem
			let newLanguageElem = document.createElement('li');
			newLanguageElem.innerHTML = translationName + ' <span class="translation__status any--weaken-color loading">generating</span>';
			translationContainerElem.insertBefore(newLanguageElem, generateContainerElem);
			
			// Hide generator
			generateContainerElem.classList.add('any--hidden');
			
			// Call generator
			initializeInlineSubmit($(generateContainerElem), '/blog/function-generate_translation.php', {
				preparedFormData  : {
					id: idElem.value,
					title: titleElem.value,
					language: translationLanguage,
					friendly: friendlyElem.value
				},
				statusContainer: $(newLanguageElem.querySelector('.translation__status')),
				callbackOnError: function(event, returnedData) {
					
					// On error, remove newly created list element and unhide generation container
					newLanguageElem.remove();
					generateContainerElem.classList.remove('any--hidden');
					
				},
				callbackOnSuccess : function(event, returnedData) {
					
					// If language generated, remove from <select> of possibilities
					translationLanguageElem.selectize.removeOption(translationLanguage);
					translationLanguageElem.selectize.clear();
					
					// Swap out status element with edit link
					setTimeout(function() {
						newLanguageElem.innerHTML = translationName + ' <a class="translation__edit symbol__edit" href="' + returnedData.url + 'edit/" target="_blank">edit</a>';
					}, 500);
					
				}
			});
			
		}
		
	});
}