// Dynamically insert JSON list
function getJsonLists( artistId, listTypes ) {
	
	// Assume we're getting items associated with a certain artist--may need to generalize at some point
	if( artistId ) {
		
		// Assume we at least have an artist template to insert these after
		let artistListElem = document.querySelector('[data-contains="artists"]');
		
		let formData = new FormData();
		formData.append('id_column', 'artist_id');
		formData.append('item_id', artistId);
		
		listTypes.forEach(function(listType) {
			formData.append('list_types[]', listType);
		});
		
		// Get musician/release lists
		fetch('/php/get-json_lists.php', {
			method: 'POST',
			body: formData
		})
		
		.then((response) => {
			return response.json();
		})
		
		.then((data) => {
			
			if( data.status === 'success' ) {
				
				// Add lists to page
				if( data.lists ) {
					
					Object.values(data.lists).forEach(list => {
						
						// Create element and append on artist list
						artistListElem.after( document.createRange().createContextualFragment( list ) );
						
					});
					
				}
				
			}
			
		});
		
	}
	
}