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

initYouTubeLazyLoad();