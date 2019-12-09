// Set track numbers on initial load
resetTrackNums();

// Attach showElem() to any "show element" buttons
$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();
	showElem($(this));
});


// When editing release, if main artist changed, change all track artists
var idElem = document.querySelector('[name="id"]');
if(idElem.value.length) {
	var artistIdElem = document.querySelector('[name="artist_id"]');
	var artistId = artistIdElem.value;
	
	artistIdElem.addEventListener('change', function() {
		if(artistId > 0) {
			var trackArtistElems = document.querySelectorAll('[name="tracklist[artist_id][]"]');
			
			for(var i=0; i<trackArtistElems.length; i++) {
				if(trackArtistElems[i].value === artistId) {
					trackArtistElems[i].value = null;
					trackArtistElems[i].selectize.clear();
				}
			}
		}
	});
}


// Set showElem() to fire on artist options if release artist is omnibus
if($("[name=artist_id]").val() === "0") {
	showElem({ "data_show" : "track--show-artist" });
}
$("[name=artist_id]").on("change", function(event) {
	if($(this).val() === "0") {
		showElem({ "data_show" : "track--show-artist" });
	}
});

// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();

// Attach sortable() to tracklist elements
$(document).on("click", ".track__reorder", function(event) { event.preventDefault(); });

var el = document.getElementsByClassName("add__tracklist")[0];
var sortable = new Sortable(el, {
	handle    : ".track__reorder",
	draggable : ".track--show-song",
	onEnd     : function(evt) { resetTrackNums(); },
	delay : 0,
	scroll    : true,
	scrollSensitivity : 60,
	scrollSpeed : 10,
	ghostClass : "track__ghost"
});

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
			controlContainer.nextUntil(".track--show-disc").last().after(
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
	
	resetTrackNums();
	lookForSelectize();
	
	$(this).blur();
});

// Init inputmask() on appropriate elements
$(":input").inputmask();

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
	initializeInlineSubmit($("[name=add]"), "/releases/function-add.php",{
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

// Delete
$(document).on("click", "[data-role=delete]", function(event) {
	event.preventDefault();
	
	$(this).html("Delete?");
	
	initializeInlineSubmit($("[name=add]"), "/releases/function-delete_release.php", {
		submitButton : $("[data-role=delete]"),
		submitOnEvent : "click",
		callbackOnSuccess : function(formElement, returnedData) {
			
			setTimeout(function() {
				changePageState("add");
			}, 2000);
		}
	});
});

// Reset to "add" state
function changePageState(state) {
	var hideClass = "any--hidden";
	var attnClass = "any--pulse";
	
	$("body").removeClass(attnClass);
	
	if(state === "add") {
		$("h1").addClass(hideClass);
		$("h2").html("Add release");
		$("[data-role=delete]").addClass(hideClass);
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		$("[name=friendly]").attr("value", "");
		$("[name=id]").attr("value", "");
		
		
		
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
		$("[data-role=delete]").removeClass(hideClass);
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		history.pushState(null, null, $("[data-get=url]").attr("href") + "edit/");
	}
	
	setTimeout(function() {
		$("body").addClass(attnClass);
	}, 1);
}

$(document).on("click", "[data-role=edit]", function(event) {
	event.preventDefault();
	changePageState("edit");
});

$(document).on("click", "[data-role=duplicate]", function(event) {
	event.preventDefault();
	changePageState("add");
});

// Autosize
autosize($(".autosize"));

// Clean song title
function cleanSongTitle(inputTitle) {
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
				var songOptions = document.querySelector('[data-contains="songs"]');
				songOptions = JSON.parse(songOptions.innerHTML);
				
				// Check if first and second track begin with ., :, or variant, and both begin with same one; if so, assume all do & set flag to remove later
				var firstTrack = cleanSongTitle(pasteText[0]);
				var secondTrack = cleanSongTitle(pasteText[1]);
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
					var cleanedTitle = cleanSongTitle(pasteText[i]);
					
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
						if(songOption.name === cleanedTitle) {
							insertNameAndRomaji( currElem, splitNameAndRomaji(songOption.quick_name) );
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

// Clear friendly on name update
var friendlyElem = document.querySelector('[name="friendly"]');
var nameElems = [
	document.querySelector('[name="name"]'),
	document.querySelector('[name="romaji"]'),
	document.querySelector('[name="press_name"]'),
	document.querySelector('[name="press_romaji"]'),
	document.querySelector('[name="type_name"]'),
	document.querySelector('[name="type_romaji"]')
];
nameElems.forEach(function(elem) {
	elem.addEventListener('change', function() {
		friendlyElem.value = '';
	});
});