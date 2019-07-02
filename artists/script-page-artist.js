// Tag artists
$(".artist__tag").on("click", function() {
	$(this).removeClass("symbol__tag symbol__loading symbol__error");
});

for(var i = 0; i < $(".artist__tag").length; i++) {
	var thisTagButton = $(".artist__tag").eq(i);
	var artistId = $(thisTagButton).attr("data-id");
	var tagId = $(thisTagButton).attr("data-tag_id");
	var action = $(thisTagButton).attr("data-action");
	
	initializeInlineSubmit($(thisTagButton), "/artists/function-tag.php", {
		submitButton: $(thisTagButton),
		statusContainer: $(thisTagButton),
		submitOnEvent: "click",
		preparedFormData: { "action" : action, "id" : artistId, "tag_id" : tagId },
		callbackOnSuccess: function(formElement, returnedData) {
			if(returnedData.is_checked) {
				$(formElement).addClass("symbol__tag any__tag--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
			}
		}
	});
}

// Init 'add video'
var addVideoForm = document.querySelector('[name=form__add-video]');
var urlElem = addVideoForm.querySelector('[name=url]');
initializeInlineSubmit($(addVideoForm), '/artists/function-add_video.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElement, returnedData) {
		urlElem.value = '';
	}
});

// Approve videos
var approveElems = document.querySelectorAll('.video__approve');
for(var i=0; i<approveElems.length; i++) {
	
	approveElems[i].addEventListener('click', function(event) {
		event.preventDefault();
		
		initializeInlineSubmit($(this), this.getAttribute('href'), {
			submitButton: $(this),
			statusContainer: $(this),
			callbackOnSuccess: function(formElement, returnedData) {
				
				console.log(returnedData);
				
				// Fade out 'flagged' notice
				var statusElem = formElement[0].parentElement;
				statusElem.classList.add('any--fade-out');
				
				setTimeout(function() {
					statusElem.remove();
				}, 200);
				
			}
		});
	});
}

// Report videos
var reportElems = document.querySelectorAll('.video__report');
for(var i=0; i<reportElems.length; i++) {
	
	reportElems[i].addEventListener('click', function(event) {
		event.preventDefault();
		
		initializeInlineSubmit($(this), this.getAttribute('href'), {
			submitButton: $(this),
			statusContainer: $(this),
			callbackOnSuccess: function(formElement, returnedData) {
				
				// Show 'awaiting approval' dialog
				var noticeElem = formElement[0].parentElement.parentElement.parentElement.querySelector('.video__flag-notice');
				if(noticeElem && noticeElem.classList) {
					noticeElem.classList.add('any--fade-in');
					noticeElem.classList.remove('any--hidden');
				}
			}
			
		});
	});
	
}

// Delete video
var deleteElems = document.querySelectorAll('.video__delete');
for(var i=0; i<deleteElems.length; i++) {
	
	initDelete(
		$(deleteElems[i]),
		'/artists/function-update_video.php',
		{
			method: 'delete',
			id: deleteElems[i].dataset.id
		},
		function(formElement, returnedData) {
			
			// Get parent video element
			var videoElem = formElement[0].parentElement.parentElement;
			
			if(!videoElem.classList.contains('artist__video')) {
				videoElem = videoElem.parentElement;
			}
			
			// Fade out video
			videoElem.classList.add('any--fade-out');
			setTimeout(function() {
				videoElem.remove();
			}, 200);
			
		}
	);
}

// Init artist name pronunciation button
var pronunciationButton = document.querySelectorAll('[data-pronunciation]');
pronunciationButton.forEach(function(item, index) {
	var pronunciation = item.getAttribute('data-pronunciation');
	
	item.addEventListener('click', function() {
		pronounce(pronunciation);
		item.blur();
	});
});

// Get all session-band indicators and make them highlight in pairs
let sessionElems = document.querySelectorAll('session');
if(sessionElems.length) {
	sessionElems.forEach(function(sessionElem) {
		var forSession = sessionElem.getAttribute('data-for-session');
		var isSession = sessionElem.getAttribute('data-is-session');
		var siblingElem;
		
		if(forSession) {
			siblingElem = document.querySelector('session[data-is-session="' + forSession + '"]');
		}
		else {
			siblingElem = document.querySelector('session[data-for-session="' + isSession + '"]');
		}
		
		sessionElem.addEventListener('mouseover', function() {
			sessionElem.classList.add('lineup__session--hovered');
			siblingElem.classList.add('lineup__session--hovered');
		});
		sessionElem.addEventListener('mouseout', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
		});
		sessionElem.addEventListener('click', function() {
			sessionElem.classList.remove('lineup__session--hovered');
			siblingElem.classList.remove('lineup__session--hovered');
			sessionElem.classList.toggle('lineup__session--clicked');
			siblingElem.classList.toggle('lineup__session--clicked');
		});
	});
}