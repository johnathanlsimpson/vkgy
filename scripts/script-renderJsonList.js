// Dynamically insert JSON list
function renderJsonList(artistId) {
	
	if( artistId ) {
		
		// Get template with artist data so we can insert new ones after it
		let artistListElem = document.querySelector('[data-contains="artists"]');
		
		let formData = new FormData();
		formData.append('id_column', 'artist_id');
		formData.append('artist_id', artistId);
		
		// Get musician/release lists
		fetch('/images/function-get_json_lists.php', {
			method: 'POST',
			body: formData
		})
		
		.then((response) => {
			return response.json();
		})
		
		.then((data) => {
			
			if( data.status === 'success' ) {
				
				// Add musician list to page
				if( data.musician_list ) {
					
					let musicianListElem = document.createRange().createContextualFragment( data.musician_list );
					artistListElem.after(musicianListElem);
					
				}
				
				// Add release list to page
				if( data.release_list ) {
					
					let releaseListElem = document.createRange().createContextualFragment( data.release_list );
					artistListElem.after(releaseListElem);
					
				}
				
			}
			
		});
		
	}
	
}