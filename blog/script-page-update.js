// JS version of friendly
function friendly(inputString) {
	
	let outputString = inputString;
	
	// Replace all non-alphanumeric characters with hyphens
	outputString = outputString.replace(/[^A-z0-9]/g, '-');
	
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
let draftElem = formElem.querySelector('[name="is_queued"]');
let dateScheduledElem = formElem.querySelector('[name="date_scheduled"]');
let timeScheduledElem = formElem.querySelector('[name="time_scheduled"]');
let scheduledElem = formElem.querySelector('.save__scheduled');
let linkElem = formElem.querySelector('.save__link');
let tokenElem = formElem.querySelector('[name="token"]');
let saveStatusElem = formElem.querySelector('.save__status');
let submitButton = formElem.querySelector('[name="form__update"] [name="submit"]');
let idElem = formElem.querySelector('[name="id"]');

let datePreviewElem  = formElem.querySelector('.preview__date');
let dateOccurredElem = formElem.querySelector('[name="date_occurred"]');


// Author
// ========================================================

let authorPreviewElem  = formElem.querySelector('.preview__user a');
let authorElem = formElem.querySelector('[name="user_id"]');

// Update author preview
authorElem.addEventListener('change', function() {
	let authorUsername = authorElem.querySelector('option[selected]').innerHTML;
 authorPreviewElem.href = '/users/' + authorUsername + '/';
	authorPreviewElem.innerHTML = authorUsername;
});


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
	
	return state == 1 ? true : false;
	
}

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
	
	// Only autochange friendly if article is unpublished
	if(!checkState('published')) {
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

// Preview content
function previewContent(inputElem, statusElem, outputElem) {
	
	// Get updated input text and set as formData
	let inputText = inputElem.value;
	
	// Send text to Markdown parser and update preview element
	if(inputText.length) {
		initializeInlineSubmit($(inputElem), "/blog/function-preview_entry.php", {
			preparedFormData  : { content: inputText },
			statusContainer   : $(statusElem),
			resultContainer   : $(outputElem),
			preserveResult    : true,
			callbackOnSuccess : function(event, returnedData) {
				
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


// Artist
// ========================================================

let artistIdElem = document.querySelector('[name="artist_id"]');

// Automatically set main artist from article preview
function updateMainArtist(inputArtist) {
	
 let previewArtistLink = document.querySelector('.artist__link');
 let previewArtistName = document.querySelector('.artist__name');
 let previewArtistRomaji = document.querySelector('.artist__romaji');
	//let artistIdElem = document.querySelector('[name="artist_id"]');
	
	if(inputArtist && inputArtist.id.length) {
		
		// Show artist link
		previewArtistLink.dataset.id = inputArtist.id;
		previewArtistLink.href = '/artists/' + inputArtist.friendly + '/';
		previewArtistName.innerHTML = inputArtist.name;
		previewArtistRomaji.innerHTML = inputArtist.romaji;
		
		// Update dropdown
		artistIdElem.selectize.setValue(inputArtist.id);
		
	}
	else {
		
		// Clear artist link and dropdown
		previewArtistLink.dataset.id = '';
		artistIdElem.selectize.clear();
		
	}
	
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
		draftElem.checked = true;
		draftElem.disabled = true;
		draftElem.dispatchEvent(new Event('change'));
		
		// Update date preview
		datePreviewElem.innerHTML = dateScheduledElem.value;
		
	}
	else {
		
		// Unset scheduled flag
		saveContainerElem.dataset.isScheduled = 0;
		
		// Re-allow to switch from draft to published
		draftElem.disabled = false;
		
		// Reset preview to date_occurred
		datePreviewElem.innerHTML = dateOccurredElem.value;
		
	}
}

// Update 'is draft' state, and reset save status
draftElem.addEventListener('change', function() {
	saveContainerElem.dataset.isQueued = draftElem.checked ? '1' : '0';
	saveStatusElem.classList.remove('symbol__success', 'symbol__error', 'symbol__loading');
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
	if(event.details.targetElem.name === 'image_is_default') {
		var imagePreviewElem = document.querySelector('.update__image');
		
		if(event.details.targetElem.checked) {
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
	
	initDelete(
		$(deleteButton),
		'/blog/function-delete_entry.php',
		{ 'id': deleteButton.getAttribute('data-id') },
		function() { changeState('add'); }
	);
}

initDeleteButton();

// Save entry
function saveEntry() {
	
	let isFirstAutosave = !idElem.value.length && checkState('queued') ? 1 : 0;
	console.log(isFirstAutosave);
	
	// Submit
	initializeInlineSubmit($('[name=form__update]'), '/blog/function-update_entry.php', {
		
		showEditLink: checkState('queued') ? false : true,
		
		callbackOnError: function(event, returnedData) {

			// Change state to show data not saved
			saveContainerElem.dataset.isSaved = '0';

		},
		
		callbackOnSuccess: function(event, returnedData) {
			
			console.log(returnedData);
			
			// Re-initialize delete button (assuming ID changed)
			initDeleteButton();
			
			// Set flag if was first autosave for draft (i.e. post created automatically)
			saveContainerElem.dataset.isFirstAutosave = isFirstAutosave;
			
			// Updating showing entry was saved
			saveContainerElem.dataset.isSaved = '1';
			
			// Update showing entry was published or unpublished
			if(saveContainerElem.dataset.isQueued == '0') {
				saveContainerElem.dataset.isPublished = '1';
			}
			else {
				saveContainerElem.dataset.isPublished = '0';
			}
			
			// Trigger event showing that ID was changed
			var e = new Event('item-id-updated');
			e.details = {
				'id' : returnedData.id,
				'is_queued' : returnedData.is_queued,
			};
			document.dispatchEvent(e);
			
			// Make save status reset
			setTimeout(function() {
				saveStatusElem.classList.remove('symbol__success');
			}, 2000);
			
			// De-focus submit button
			submitButton.blur();
			
		}
		
	});
	
}

// Fire save on form submit
formElem.addEventListener('submit', function(event) {
	event.preventDefault();
	saveEntry();
});


// Change states
function changeState(state) {
	var text = { "add" : "Add entry", "edit" : "Edit entry" };
	var elems = [".update__header", "[name=submit]"];
	
	for(var i = 0; i < elems.length; i++) {
		$(elems[i]).html(text[state]);
	}
	
	if(state === "edit") {
		document.title = text[state] + ": " + $("[name=title]").val() + " |  vk.gy (ブイケージ)";
		history.pushState("", "", "/blog/" + $("[name=friendly]").val() + "/edit/");
	}
	else if(state === "add") {
		elems = [
			"[data-id]",
			"[name=form__update] input",
			"[name=form__update] textarea",
			"[name=form__update] option",
			".update__preview",
			".update__image"
		];
		
		$("body").removeClass("any--pulse").addClass("any--pulse");
		$(".image__template:nth-of-type(n + 2)").remove();
		$("[name=delete]").removeClass("symbol__success symbol__loading symbol__error").addClass("symbol--standalone").html("");
		
		for(i = 0; i < elems.length; i++) {
			$(elems[i]).html("").val("").attr("selected", false).attr("checked", false).attr("src", "").attr("data-id", "");
		}
		
		document.title = text[state] + " | vk.gy (ブイケージ)";
		history.pushState("", "", "/blog/add/");
	}
}


// Attempt to see when images are added
document.addEventListener('image-updated', function() {
	
	// Get current image elements
	let currentImages = document.querySelectorAll('.image__results .image__template .image__image');
	let snsImageContainer = document.querySelector('.sns__container');
	let currentSnsImageId = document.querySelector('[name="sns_image_id"]:checked');
	
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
			
			console.log(snsImageId, snsImageThumbnail);
			
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

// Generate SNS post
// ========================================================

// Get SNS preview and insert into preview elem
function getSnsPost() {
	
	// Elements
	let tagElems = document.querySelectorAll('[name="tags[]"]:checked');
	let tweetPreviewElem = document.querySelector('.sns__tweet');
	
	// Values
	let title = titleElem.value;
	let url = '/blog/' + friendlyElem.value + '/';
	let id = idElem.value;
	let artistId = artistIdElem.value;
	let authorId = authorElem.value;
	let postType = 'blog_post';
	
	// Get tag values
	if(tagElems && tagElems.length) {
		tagElems.forEach(function(tagElem) {
			if(tagElem.dataset.friendly === 'interview') {
				postType = 'interview';
			}
		});
	}
	
	// Build form data
	let snsData = {
		title: title,
		url: url,
		id: id,
		artist_id: artistId,
		author_id: authorId,
		post_type: postType
	}
	
	// Get SNS preview
	initializeInlineSubmit($(tweetPreviewElem), '/blog/function-generate_sns.php', {
		
		preparedFormData: snsData,
		
		callbackOnSuccess: function(event, returnedData) {
			
			// Output SNS previews
			if(tweetPreviewElem && returnedData && returnedData.sns_post && returnedData.sns_post.content) {
				tweetPreviewElem.innerHTML = returnedData.sns_post.content.replace(/\n/g, '<br />');
			}
			
		},
		
		callbackOnError: function(event, returnedData) {
			
			console.log('error');
			console.log(returnedData);
			
		}
		
	});
	
}

// Fire SNS preview update when certain fields are changed
// credit
let interviewTagElem = document.querySelector('[name="tags[]"][data-friendly="interview"]');
[ titleElem, authorElem, artistIdElem, interviewTagElem ].forEach(function(elem) {
	elem.addEventListener('change', function() {
		getSnsPost();
	});
});

getSnsPost();