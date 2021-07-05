document.addEventListener('click', function(event) {
	
	if( event.target.classList && event.target.classList.contains('video__thumbnail') && event.target.getAttribute('data-id') ) {
		
		event.preventDefault();
		
		let videoElem = event.target;
		
		var height = videoElem.offsetHeight;
		var width = videoElem.offsetWidth;
		var iframe = document.createElement('iframe');

		iframe.setAttribute('frameborder', '0' );
		iframe.setAttribute('height', height );
		iframe.setAttribute('width', width );
		iframe.setAttribute('src', 'https://youtube.com/embed/' + videoElem.dataset.id + '?rel=0&showinfo=0&autoplay=1' );

		videoElem.innerHTML = '';
		videoElem.parentNode.replaceChild(iframe, videoElem);

		// Log the click as a 'view'
		fetch('/videos/function-log_view.php?id=' + videoElem.dataset.id)
		.then((response) => {
		});
		
	}
	
});
/*
function initYouTubeLazyLoad() {
	var youtubeElems = document.querySelectorAll('.video__thumbnail[data-id]');
	
	for(var i=0; i<youtubeElems.length; i++) {
		youtubeElems[i].addEventListener('click', function(event) {
			event.preventDefault();
			
			var height = this.offsetHeight;
			var width = this.offsetWidth;
			var iframe = document.createElement('iframe');
			
			iframe.setAttribute('frameborder', '0' );
			iframe.setAttribute('height', height );
			iframe.setAttribute('width', width );
			iframe.setAttribute('src', 'https://youtube.com/embed/' + this.dataset.id + '?rel=0&showinfo=0&autoplay=1' );
			
			this.innerHTML = '';
			this.parentNode.replaceChild(iframe, this);
			
			// Log the click as a 'view'
			fetch('/videos/function-log_view.php?id=' + this.dataset.id)
			.then((response) => {
			});
			
		});
	}
}

initYouTubeLazyLoad();*/