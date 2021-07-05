// ========================================================
// Shared
// ========================================================

let deleteButton = document.querySelector('[data-role="delete"]');
let deleteContainerElem = null;
let resultElem = document.querySelector('[data-role="result"]');
let titleHasBeenAddedAsSong = false;

// ========================================================
// When artist changes (release or track), update songs
// ========================================================

// Watch for artist changes
document.addEventListener('change', function(event) {
	
	let elemName = event.target.name;
	
	// Only care about release artist or track artist
	if( elemName === 'artist_id' || elemName === 'tracklist[artist_id][]' ) {
		
		let targetArtistId = event.target.value;
		
		// Check if the list was already gotten
		if( !targetArtistId.isNaN && !document.querySelector('[data-contains="songs_' + targetArtistId + '"]') ) {
			
			// And if not, get it
			getJsonLists( targetArtistId, [ 'songs' ] );
			
		}
		
		// If this is a release artist, may need to change track artists
		if( elemName === 'artist_id' ) {
			
			// Update the selected artist for all tracks if necessary
			updateReleaseArtist( targetArtistId );
			
			// Reset the flag that says whether custom song was added from title
			titleHasBeenAddedAsSong = false;
			
		}
		
		// If this is a track artist, update track's song list
		else {
			updateTrackArtist( event.target, targetArtistId );
		}
		
		// Reset easyautocomplete so it will get new list of songs
		resetEasyAutocomplete();
		
	}
	
});

// ========================================================
// When release artist changes, update certain tracks
// ========================================================

function updateReleaseArtist( newArtistId ) {
	
	// Save artist id before change
	let originalArtistIdElem = document.querySelector('[name="original_artist_id"]');
	let originalArtistId = originalArtistIdElem.value;
	
	// Change songs source in template
	let templateTrackNameElem = document.querySelector('.track__template [name="tracklist[name][]"]');
	templateTrackNameElem.setAttribute('data-src', 'songs_' + newArtistId);
	
	// Change songs source of existing tracks that were set to original artist (i.e. changing from Dali to DALI will only update Dali tracks)
	let trackNameElems = document.querySelectorAll('[name="tracklist[name][]"][data-src="songs_' + originalArtistId + '"]');
	
	if( trackNameElems && trackNameElems.length > 0 ) {
		trackNameElems.forEach(function(trackNameElems) {
			
			trackNameElems.setAttribute('data-src', 'songs_' + newArtistId);
			
		});
	}
	
	// Change artist dropdown beside track
	let trackArtistElems = document.querySelectorAll('[name="tracklist[artist_id][]"]');
	
	if( trackArtistElems && trackArtistElems.length > 0 ) {
		trackArtistElems.forEach(function(trackArtistElem) {
			
			// Just clear the value and destroy the dropdown
			trackArtistElem.value = null;
			if( trackArtistElem.selectize ) {
				trackArtistElem.selectize.clear();
			}
			
		});
	}
	
	// Now update the original artist id
	originalArtistIdElem.value = newArtistId;
	
}

// ========================================================
// When track artist changes, update songs list
// ========================================================

function updateTrackArtist( trackArtistElem, newArtistId ) {
	
	let trackElem = trackArtistElem.closest('.track');
	let trackNameElem = trackElem.querySelector('[name="tracklist[name][]"]');
	
	// Update songs list for this track
	trackNameElem.setAttribute('data-src', 'songs_' + newArtistId);
	
}

// ========================================================
// Songs/tracks
// ========================================================

// When track name or romaji is changed, clear out the associated song ID
// The PHP will attempt to find the new song/update or create one if necessary
document.addEventListener('change', function(event) {
	
	let targetName = event.target.getAttribute('name');
	
	if( targetName && ( targetName == 'tracklist[name][]' || targetName == 'tracklist[romaji][]' ) ) {
		
		// If is_custom is checked, then we keep the song ID
		if( event.target.closest('.track').querySelector('[name="tracklist[is_custom][]"]').value != 1 ) {
			event.target.closest('.track').querySelector('[name="tracklist[song_id][]"]').value = '';
		}
		
	}
	
});

// ========================================================
// Debug songs
// ========================================================

// On toggle of is_custom, change value of text input,
// since checkbox won't send if not checked
document.addEventListener('click', function(event) {
	
	if( event.target.classList && event.target.classList.contains('track__is-custom') ) {
		
		let checkboxElem = event.target;
		let labelElem = event.target.closest('label');
		let isCustomElem = labelElem.querySelector('[name="tracklist[is_custom][]"]');
		
		isCustomElem.value = checkboxElem.checked ? 1: 0;
		
	}
	
});

// When turning on song debug for a track, remove notes from name and move to notes elem
function splitNotesFromTrack( trackElem ) {
	
	let trackNameElem = trackElem.querySelector('[name="tracklist[name][]"]');
	let trackRomajiElem = trackElem.querySelector('[name="tracklist[romaji][]"]');
	let trackNotesNameElem = trackElem.querySelector('[name="tracklist[notes_name][]"]');
	let trackNotesRomajiElem = trackElem.querySelector('[name="tracklist[notes_romaji][]"]');
	
	// Move notes from data to value for notes fields
	trackNotesNameElem.value = trackNotesNameElem.getAttribute('data-value');
	trackNotesRomajiElem.value = trackNotesRomajiElem.getAttribute('data-value');
	
	// Remove JP notes from name field
	if( trackNotesNameElem.value.length > 0 ) {
		trackNameElem.value = trackNameElem.value.replace( trackNotesNameElem.value, '' );
	}
	
	// Remove romaji notes from romaji field
	if( trackNotesRomajiElem.value.length > 0 ) {
		trackRomajiElem.value = trackRomajiElem.value.replace( trackNotesRomajiElem.value, '' );
	}
	
}

// ========================================================
// Swap between page states/templates
// ========================================================

let formElem = document.querySelector('[name=add]');
let artistElem = document.querySelector('[name=artist_id]');
let omnibusId = 0;

// When artist changed, swap template
$(artistElem).on('change', function() {
	swapTemplate(formElem, artistElem.value);
});

// Show appropriate template on page load
swapTemplate(formElem, artistElem.value);

// Show certain elements if artist is omnibus
function swapTemplate(formElem, artistId) {
	if( artistId.length > 0 && artistId == omnibusId ) {
		showElem({
			'data_show': 'track--show-artist'
		});
	}
}

// Change page state between add/edit
function changePageState(state) {
	var hideClass = "any--hidden";
	var attnClass = "any--pulse";
	
	$("body").removeClass(attnClass);
	
	if(state === "add") {
		$("h1").addClass(hideClass);
		$("h2").html("Add release");
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		$("[name=friendly]").attr("value", "");
		$("[name=id]").attr("value", "");
		
		if( deleteButton && deleteContainerElem ) {
			deleteContainerElem.classList.add(hideClass);
		}
		
		document.querySelector('[name=image_item_id]').value = null;
		document.querySelector('[name=image_item_name]').value = null;
		var images = document.querySelectorAll('.image__results .image__template');
		if(images && images.length) {
			images.forEach(function(image) {
				image.remove();
			});
		}
		
		
		history.pushState(null, null, "/releases/add/");
	}
	else if(state === "edit") {
		$("h1").removeClass(hideClass);
		$("h2").html("Edit release");
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		
		if( deleteButton && deleteContainerElem ) {
			deleteContainerElem.classList.remove(hideClass);
		}
		
		history.pushState(null, null, $("[data-get=url]").attr("href") + "edit/");
	}
	
	setTimeout(function() {
		$("body").addClass(attnClass);
	}, 1);
}

// Change to edit state
$(document).on("click", "[data-role=edit]", function(event) {
	event.preventDefault();
	changePageState("edit");
});

// Change back to add state
$(document).on("click", "[data-role=duplicate]", function(event) {
	event.preventDefault();
	changePageState("add");
});

// ========================================================
// Track sorting
// ========================================================

// Attach sortable() to tracklist elements
$(document).on("click", ".track__reorder", function(event) { event.preventDefault(); });

var el = document.getElementsByClassName("add__tracklist")[0];
var sortable = new Sortable(el, {
	handle: '.track__reorder',
	draggable: '.track',
	delay: 0,
	scroll: true,
	scrollSensitivity: 60,
	scrollSpeed: 10,
	ghostClass: "track__ghost"
});

// ========================================================
// Add tracks
// ========================================================

// Attach trackTemplate() to tracklist manipulation buttons
$(document).on("click", "[data-add]", function(event) {
	event.preventDefault();

	var component = $(this).attr("data-add");
	var controlContainer = $(this).parents(".track");
	
	if(component === "disc") {
		
		var firstTrack           = $(this).parents(".text").find(".track:first-of-type");
		var nextTrackContainer   = controlContainer.next(".track");
		
		if(!firstTrack.hasClass("track--show-disc")) {
			firstTrack.before(
				trackTemplate("disc")
			);
		}
		
		if(nextTrackContainer.hasClass("track--show-section")) {
			controlContainer.nextUntil(".track--show-disc", '.track').last().after(
				trackTemplate("disc"),
				trackTemplate("song", 5),
				trackTemplate("controls")
			);
		}
		else {
			controlContainer.after(
				trackTemplate("disc"),
				trackTemplate("song", 5),
				trackTemplate("controls")
			);
		}
		
	}
	
	if(component === "section") {
		var prevSongContainers   = controlContainer.prevUntil(":not(.track--show-song)").last();
		var prevSectionContainer = prevSongContainers.prev(".track--show-section");
		
		if(prevSectionContainer.html() === null || prevSectionContainer.html() === undefined) {
			prevSongContainers.before(
				trackTemplate("section")
			);
		}
		
		controlContainer.after(
			trackTemplate("section"),
			trackTemplate("song", 5),
			trackTemplate("controls")
		);
	}
	
	if(component === "songs") {
		controlContainer.before(
			trackTemplate("song", 5)
		);
	}
	
	if(component === "song") {
		controlContainer.after(
			trackTemplate("song")
		);
	}
	
	//resetTrackNums();
	lookForSelectize();
	
	let eacElems = document.querySelectorAll('[data-easyautocomplete]:not([data-easyautocompleted])');
	eacElems.forEach(function(eacElem) {
		eacElem.addEventListener('focus', function(event) {
			if(!this.hasAttribute('data-easyautocompleted')) {
			initEasyAutocomplete(this);
			}
		});
		
	});
	
	$(this).blur();
});

// ========================================================
// Submit
// ========================================================

// Grab submit button press, transform certain inputs, then submit
var submitButton = document.querySelector('[name=add]');
submitButton.addEventListener('submit', function(event) {
	event.preventDefault();
	
	// Selectize empties any <select>s that we tabbed through but didn't make a selection
	// So for tracks' artist_id, lets fill those back up with an empty value
	// Otherwise we get random missing array entries in the data sent to server
	var artistIdElems = document.querySelectorAll('[name^="tracklist[artist_id]"]:empty');
	if(artistIdElems.length > 0) {
		artistIdElems.forEach(function(artistIdElem) {
			artistIdElem.innerHTML = '<option value="" selected></option>';
		});
	} 

	// Use the initInlineSubmit function, set to fire immediately
	initializeInlineSubmit($("[name=add]"), "/releases/function-update.php",{
		showEditLink : true,
		callbackOnSuccess : function(formElement, returnedData) {
			var e = new Event('item-id-updated');
			e.details = {
				'id' : returnedData.id
			};
			document.dispatchEvent(e);
		}
	});
	
});


// ========================================================
// Delete
// ========================================================

if( deleteButton ) {
	
	deleteContainerElem = deleteButton.closest('.text');
	
	initDelete( $(deleteButton), '/releases/function-delete.php', { 'id': document.querySelector('[name="id"]').value }, function(formElem, returnedData) {
		
		resultElem.innerHTML = returnedData.result;
		resultElem.classList.remove('any--hidden');
		
		setTimeout(function() {
			changePageState("add");
		}, 2000);
		
	});
	
}

// ========================================================
// Song helpers
// ========================================================

// Remove track numbers from pasted titles
function removeTrackNumFromSong(inputTitle) {
	inputTitle = inputTitle.trim();
	inputTitle = inputTitle.replace(/^(?:(\d{1,3}))?(?:([\.．・]{1}))? ?/, '');
	return inputTitle;
}

// Paste tracklist from clipboard
document.addEventListener('paste', function(event) {
	if(event.target.getAttribute("name") === "tracklist[name][]") {
		
		// Grab paste text, clean slightly, then check if it matches tracklist pattersn
		var pasteText = event.clipboardData.getData('text/plain');
		var tracklistPattern, numPastedLines;
		pasteText = pasteText.trim();
		
		// NUMBERS_AT_BOUNDARY (starts with number, plus multiple instances of numbers after a word boundary)
		if( pasteText.match(/^\d+/) && (pasteText.match(/\b\d+/g) || []).length > 1 ) {
			tracklistPattern = 'NUMBERS_AT_BOUNDARY';
		}
		
		// MULTI_LINE
		else if(pasteText.includes('\n')) {
			tracklistPattern = 'MULTI_LINE';
		}
		
		// If tracklist pattern detected, separate into lines, then clean
		if(tracklistPattern) {
			
			// Split tracks
			if(tracklistPattern === 'MULTI_LINE') {
				pasteText = pasteText.split('\n');
			}
			else if(tracklistPattern === 'NUMBERS_AT_BOUNDARY') {
				pasteText = pasteText.split(/\b\d+/);
			}
			
			// Remove empty values, set line count
			pasteText = pasteText.filter(function(x) { return x.replace(/\s+/, ''); });
			numPastedLines = pasteText.length;
			
			// If multiple lines, prevent paste event and handle song insertion
			if(numPastedLines > 1) {
				
				// Prevent event, set target
				var currElem = event.target;
				event.preventDefault();
				
				// Get current song options so we can auto-romanize
				var songOptions = document.querySelector('[data-contains="songs_' + artistElem.value + '"]');
				songOptions = JSON.parse(songOptions.innerHTML);
				
				// Check if first and second track begin with ., :, or variant, and both begin with same one; if so, assume all do & set flag to remove later
				var firstTrack = removeTrackNumFromSong(pasteText[0]);
				var secondTrack = removeTrackNumFromSong(pasteText[1]);
				var checkForPeriod;
				if( firstTrack.slice(0,1) === secondTrack.slice(0,1) && firstTrack.slice(0,1).match(/[.．。:：]/) && secondTrack.slice(0,1).match(/[.．。:：]/) ) {
					checkForPeriod = true;
				}
				
				// If first and second track are bracketed (after accounting for leading period), assume all are & set flag to remove later
				var bracketText = '「」';
				var checkForBrackets;
				if(checkForPeriod) {
					firstTrack = firstTrack.replace(/^[.．。:：]\s*/, '');
					secondTrack = secondTrack.replace(/^[.．。:：]\s*/, '');
				}
				if(firstTrack.slice(0,1) + firstTrack.slice(-1) === bracketText && secondTrack.slice(0,1) + secondTrack.slice(-1) === bracketText) {
					checkForBrackets = true;
				}
				
				// Loop through each track and transform + insert it
				for(var i=0; i<numPastedLines; i++) {
					
					// Clean song title (remove spaces, etc)
					var cleanedTitle = removeTrackNumFromSong(pasteText[i]);
					
					// Remove periods if necessary
					if(checkForPeriod) {
						if(cleanedTitle.slice(0,1).match(/[.．。:：]/)) {
							cleanedTitle = cleanedTitle.replace(/^[.．。:：]\s*/, '');
						}
					}
					
					// Remove brackets if necessary
					if(checkForBrackets) {
						if(cleanedTitle.slice(0,1) + cleanedTitle.slice(-1) === bracketText) {
							cleanedTitle = cleanedTitle.slice(1,-1);
						}
					}
					
					// Set element to pasted song title
					currElem.value = currElem.value + cleanedTitle;
					
					// Check if song title is in list of possible songs, insert romanization into neighbor if so
					songOptions.forEach(function(songOption, index) {
						
						if( songOption[1] === cleanedTitle || songOption[2] === cleanedTitle ) {
							insertSong( currElem, songOption[0], songOption[1], songOption[2] );
						}
						
					});
					
					// If not at end of tracklist, make sure we don't run out of empty inputs
					if(i + 1 < numPastedLines) {
						var isParent = false;
						
						// Move up to parent .track--show-song container, so we can move to next one
						while(!isParent) {
							currElem = currElem.parentElement;
							
							// If on .track--show-song container, check if sibling is also .track--show-song and has empty name input
							if(currElem.classList.contains("track--show-song")) {
								
								// If next .track--show-song exists and has empty name & romaji inputs, let next track fill that
								var nextElem = currElem.nextElementSibling;
								if(nextElem && nextElem.classList.contains('track--show-song') && nextElem.querySelector('[name="tracklist[name][]"]').value.length === 0) {
								}
								
								// Otherwise, press the 'add track' button and focus the newly-created name element, so next track can go there
								else {
									var addTrackButton = currElem.querySelector('.track__song-controls:last-of-type .track__song-control');
									addTrackButton.click();
								}
								
								// Select next name field and end loop
								currElem = currElem.nextElementSibling;
								currElem = currElem.querySelector('[name="tracklist[name][]"]');
								
								isParent = true;
							}
						}
					}
					
				}
				
				// After pasting all items, let's make sure the last one is focused (may be romaji or may be name)
				if(currElem) {
					var parentElem = currElem.parentElement.classList.contains('.track__song') ? currElem.parentElement : currElem.parentElement.parentElement;
					var romajiElem = parentElem.querySelector('[name="tracklist[romaji][]"]');
					
					if(romajiElem.value) {
						romajiElem.focus();
					}
					else {
						currElem.focus();
					}
				}
				
			}
		}
		
	}
});

// Clear tracklist
let clearButton = document.querySelector('[data-clear]');
clearButton.addEventListener('click', function() {
	let clearButton = this;
	let tracksElem = document.querySelector('.add__tracklist');
	let trackElems = document.querySelectorAll('.track input');
	let selectElems = document.querySelectorAll('.track select');
	let i;
	
	for(i=0; i<trackElems.length; i++) {
		trackElems[i].value = '';
	}
	for(i=0; i<selectElems.length; i++) {
		selectElems[i].value = '';
	}
	
	this.classList.add('symbol__success');
	this.blur();
	tracksElem.classList.add('any--pulse');
	
	window.setTimeout(function() {
		clearButton.classList.remove('symbol__success');
	}, 1000);
});

// ========================================================
// Clear friendly
// ========================================================

let friendlyElem = document.querySelector('[name="friendly"]');

// When these elements change, clear friendly
document.addEventListener('change', function(event) {
	
	// These elemnts will clear friendly on change
	let targetElemNames = [ 'name', 'romaji', 'press_name', 'press_romaji', 'type_name', 'type_romaji' ];
	
	if( targetElemNames.includes( event.target.name ) ) {
		friendlyElem.value = '';
	}
	
});

// ========================================================
// Add release title as song choice
// ========================================================

// Get title elems
let nameElem = document.querySelector('[name="name"]');
let romajiElem = document.querySelector('[name="romaji"]');

// This will be used when adding custom songs
let numSongs = 0;

// Watch title elems for change
[ nameElem, romajiElem ].forEach(function(potentialSongElem) {
	potentialSongElem.addEventListener('change', function(event) {
		
		let potentialName = nameElem.value;
		let potentialRomaji = romajiElem.value;
		let titleHasMultipleSongs = false;
		
		// If this is the first custom song we're adding to the list, get the length of the current songs
		// so that we're not just endlessly adding songs as the person revises the title
		if( !titleHasBeenAddedAsSong ) {
			
			// Make sure songs elem exists and can get songs
			let currentSongsElem = document.querySelector('[data-contains="songs_' + artistElem.value + '"]');
			if( currentSongsElem ) {
				
				// Transform to json and count
				let currentSongs = JSON.parse(currentSongsElem.innerHTML);
				numSongs = currentSongs.length;
				
			}
			
		}
		
		// Try to guess if release name is single and coupling
		let couplingStrings = [ 'c/w', ' / ', '/' ];
		
		// Loop through each coupling string and see if we have it
		couplingStrings.every(function(couplingString) {
			if( potentialName.includes(couplingString) && potentialRomaji.includes(couplingString) ) {
				
				// Break name into array of potential songs
				let potentialNames = potentialName.split(couplingString);
				let potentialRomajis = potentialRomaji.split(couplingString);
				
				// Loop through each title and add it to songs list
				potentialNames.forEach(function(nameValue, i) {
					addSongToList( artistElem.value, nameValue, potentialRomajis[i], i );
				});
				
				// Set a flag so we know we don't need to add the entire title as a song
				titleHasMultipleSongs = true;
				
			}
			
			// If current coupling string now found, make sure loop continues
			else {
				return true;
			}
			
		});
		
		// If the title wasn't a coupling situation, add the whole title as a song
		if( !titleHasMultipleSongs ) {
			addSongToList( artistElem.value, potentialName, potentialRomaji );
		}
		
	});
});

// Given artist id and song name, add to list of songs for that artist
function addSongToList( artistId, name, romaji, index ) {
	
	if( !artistId.isNaN && name.length > 0 ) {
		
		// If songs elem exists, get current songs
		let currentSongsElem = document.querySelector('[data-contains="songs_' + artistId + '"]');
		if( currentSongsElem ) {
			
			// Transform to json
			let currentSongs = JSON.parse(currentSongsElem.innerHTML);
		
			// Prepare song object (id, name, romaji, friendly, quick)
			let song = [
				'',
				name,
				romaji,
				'-',
				romaji.length > 0 && romaji != name ? romaji + ' (' + name + ')' : name
			];
			
			// How many songs to replace (if replacing multiple previously-inserted songs with one new song, eliminate them w/ arbitrary number)
			let numSongsToReplace = index && !index.isNaN ? 1 : 10;
			
			// Used when inserting multiple songs into the list at once, for the position of insertion
			index = numSongs + ( index && !index.isNaN ? index : 0 );
			
			// Splice new song into position and replace previous custom songs if necessary
			currentSongs.splice( index, numSongsToReplace, song );
			
			// Set flag showing we've added a custom song
			titleHasBeenAddedAsSong = true;
			
			// Return songs to json and update list
			currentSongsElem.innerHTML = JSON.stringify(currentSongs);
			
			// Reset easyautocomplete so it will get new list of songs
			resetEasyAutocomplete();
			
		}
		
	}
	
}

// ========================================================
// Inits
// ========================================================

// Init inputmask() on appropriate elements
$(":input").inputmask();

// Attach showElem() to any "show element" buttons
$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();
	showElem($(this));
});

// Autosize
autosize($(".autosize"));