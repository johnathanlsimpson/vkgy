/* Get songs */
function getSongs() {
	var artistId     = $("[name=artist_id]").val();
	var dataElem     = $("[data-contains=songs]");
	var data         = { "artist_id" : artistId };
	var processorUrl = "/releases/function-get_song_list.php";
	
	$(dataElem).load(processorUrl, data);
}



/* Fire getSongs() if artist selected on load, or if artist changed */
var artistIdElem = $("[name=artist_id]");

if(artistIdElem.val().length > 0) {
	getSongs();
}

artistIdElem.on("change", function() {
	getSongs();
	resetEasyAutocomplete();
});



/* Split name and romaji */
function splitNameAndRomaji(inputText) {
	inputText = inputText.replace(/\\\(/g, "&#92;&#40;").replace(/\\\)/g, "&#92;&#41;");

	var match = inputText.match(/^(.+?)(?: \((.+)\))?$/);
	var output = { "name" : (match[2] ? match[2] : match[1]), "romaji" : (match[2] ? match[1] : null) };
	
	return output;
}



// Given a tracklist name element, take name+romaji string and insert each into its appropriate field
function insertNameAndRomaji(targetElem, nameAndRomaji) {
	
	// Split string into name and romaji
	var name = nameAndRomaji.name || '';
	name = name.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
	
	var romaji = nameAndRomaji.romaji || '';
	romaji = romaji.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
	
	// Find parent of name element (given plain JS element), then get romaji element
	var parentElem = targetElem.parentElement;
	parentElem = parentElem.classList.contains('input__group') ? parentElem : parentElem.parentElement;
	var siblingElem = parentElem.querySelector('[name="tracklist[romaji][]"]');
	
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
				if(srcType == 'songs') {
					
					let nameAndRomaji = splitNameAndRomaji($(targetElem).val());
					insertNameAndRomaji($(targetElem)[0], nameAndRomaji);
					
				}
				
				else if(srcType == 'types') {
					
					let typeAndRomaji = splitNameAndRomaji($(targetElem).val());
					
					// Split string into name and romaji
					let name = typeAndRomaji.name || '';
					name = name.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
					
					let romaji = typeAndRomaji.romaji || '';
					romaji = romaji.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(");
					
					document.querySelector('[name="type_name"]').value = name;
					document.querySelector('[name="type_romaji"]').value = romaji;
					
					// If romaji included, focus on that elem
					if(romaji && romaji.length) {
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