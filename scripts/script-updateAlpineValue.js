// Helper to change data in Alpine item
function updateAlpineValue( containerElem, key, value ) {
	
	// Need this in case of document fragments (or something)
	if( containerElem.classList ) {
		
		// Get data and update value
		let alpineData = containerElem.getAttribute('x-data');
		let searchRegex = new RegExp( key + ": ?'?[^',]*'?", 'g' );
		let replacement = key + ": '" + value + "'";
		
		// If value is a number, let's make sure to preserve that
		if( !isNaN(value) ) {
			replacement = key + ':' + value;
		}
		
		// Set new data and replace in container
		let newAlpineData = alpineData.replace( searchRegex, replacement );
		containerElem.setAttribute( 'x-data', newAlpineData );
		
		// Update associated input if necessary
		let associatedElem = containerElem.querySelector('[x-data="' + key + '"]');
		if( associatedElem ) {
			associatedElem.value = value;
			triggerChange(associatedElem);
		}
		
	}
	
}