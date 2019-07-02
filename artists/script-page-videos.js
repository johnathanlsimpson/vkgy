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
			
			if(!videoElem.classList.contains('video__item')) {
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