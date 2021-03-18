// Init 'add video'
var addVideoForm = document.querySelector('[name=form__add-video]');
var urlElem = addVideoForm.querySelector('[name=url]');
var videosContainer = document.querySelector('.video__wrapper');

initializeInlineSubmit($(addVideoForm), '/artists/function-add_video.php', {
	submitOnEvent: 'submit',
	callbackOnSuccess: function(formElement, returnedData) {
		
		// Get innerHTML of video template, then replace strings with data
		var videoTemplate = document.querySelector('#template-video').innerHTML;
		
		// Set replacements
		for(var [key, value] of Object.entries(returnedData)) {
			videoTemplate = videoTemplate.replace(new RegExp('{' + key + '}', 'g'), value);
		}
		videoTemplate = videoTemplate.replace('{release_class}', 'any--hidden');
		videoTemplate = videoTemplate.replace(/{.+?}/g, '');
		
		// Create new video elem
		var newVideo = document.createElement('div');
		newVideo.innerHTML = videoTemplate;
		
		// Append new video elem to container
		videosContainer.insertBefore(newVideo.firstElementChild, videosContainer.lastElementChild);
		
		// Reset url input
		urlElem.value = '';
		
		initLazyLoad();
		initYouTubeLazyLoad();
		
	}
});