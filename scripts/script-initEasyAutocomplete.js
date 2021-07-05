// Given a tracklist name element, take name+romaji string and insert each into its appropriate field
function insertSong( targetElem, songId, name, romaji ) {
	
	// Undo html entities
	name = name.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
	romaji = romaji.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
	
	// Find parent of name element (given plain JS element), then get romaji element
	var parentElem = targetElem.parentElement;
	parentElem = parentElem.classList.contains('input__group') ? parentElem : parentElem.parentElement;
	var siblingElem = parentElem.querySelector('[name="tracklist[romaji][]"]');
	
	// Update song ID
	let songIdElem = targetElem.closest('.track').querySelector('[name="tracklist[song_id][]"]');
	songIdElem.value = songId;
	
	// Set value of both elements, then focus the romaji element
	targetElem.value = name;
	siblingElem.value = romaji;
	siblingElem.focus();
	
}

// Init easyAutocomplete
function initEasyAutocomplete(targetElem) {
	
	// Get data source
	let eacSrc = targetElem.dataset.src;
	eacSrc = document.querySelector('[data-contains="' + eacSrc + '"]');
	eacSrc = eacSrc ? JSON.parse(eacSrc.innerHTML) : null;
	
	let eacOptions = {
		data: eacSrc,
		getValue: targetElem.dataset.getValue,
		list: {
			match: { enabled: true },
			onChooseEvent: function() {
				
				let srcType = targetElem.dataset.src;
				
				// For now we'll just make assumptions based on data source
				if( srcType.includes('songs') ) {
					
					let name = $(targetElem).getSelectedItemData()[1];
					let romaji = $(targetElem).getSelectedItemData()[2];
					
					insertSong( $(targetElem)[0], $(targetElem).getSelectedItemData()[0], name, romaji );
					
				}
				
				else if(srcType == 'types') {
					
					let typeName = $(targetElem).getSelectedItemData().name;
					let typeRomaji = $(targetElem).getSelectedItemData().romaji;
					
					// Undo encoding
					typeName = typeName.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
					typeRomaji = typeRomaji.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
					
					document.querySelector('[name="type_name"]').value = typeName;
					document.querySelector('[name="type_romaji"]').value = typeRomaji;
					
					// If romaji included, focus on that elem
					if(typeRomaji && typeRomaji.length) {
						document.querySelector('[name="type_romaji"]').focus();
					}
					else {
						document.querySelector('[name="type_name"]').focus();
					}
					
				}
				
			}
		}
	};
	
	// Set 'autocompleted' attribute so we don't init twice
	targetElem.setAttribute('data-easyautocompleted', true);
	
	// Do the actual init
	$(targetElem).easyAutocomplete(eacOptions);
	
	// Focus on the element so user can type
	targetElem.focus();
	
	// If want to show all options from beginning, trigger jQuery event
	if(targetElem.dataset.open) {
		$(targetElem).triggerHandler(jQuery.Event("keyup", { keyCode: 65, which: 65}));
		
		// Also add event listener for focus
		targetElem.addEventListener('focus', function() {
			$(targetElem).triggerHandler(jQuery.Event("keyup", { keyCode: 65, which: 65}));
		});
		
	}
	
}

// Init easyAutocomplete on first focus
let eacElems = document.querySelectorAll('[data-easyautocomplete]:not([data-easyautocompleted])');
eacElems.forEach(function(eacElem) {
	
	eacElem.addEventListener('focus', function(event) {
		if(!this.hasAttribute('data-easyautocompleted')) {
		initEasyAutocomplete(this);
		}
	});
	
});

/* Reset easyAutocomplete() */
function resetEasyAutocomplete() {
	$("[data-easyautocompleted]").removeAttr("data-easyautocompleted");
}