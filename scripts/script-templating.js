function defineTemplate(templateId, returnedData) {
	
	// Find the <template>, then get the html within it
	let templateElem = document.querySelector(templateId);
	let templateInner = templateElem.content.firstElementChild;
	
	// Loop through returned data to try to insert it into template
	for(const [key, value] of Object.entries(returnedData)) {
		
		// Let's try to find the element within which to insert the value
		let keyElem;
		
		// If key element is [data-get="key"]
		if( templateInner.querySelector('[data-get="' + key + '"]') ) {
			
			keyElem = templateInner.querySelector('[data-get="' + key + '"]');
			
			// If data-get-into specified, set value there
			if( keyElem.dataset.getInto ) {
				keyElem.setAttribute( keyElem.dataset.getInto, value );
			}
			
			// Otherwise just insert value in innerhtml
			else {
				keyElem.innerHTML = value;
			}
			
		}
		
		// Else if key element has [data-key]
		else if( templateInner.dataset[key] ) {
			templateInner.dataset[key] = value;
		}
		
	}
	
}