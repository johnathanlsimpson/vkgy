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



/* initEasyAutocomplete() */
function initEasyAutocomplete(targetElem) {
	var options = {
		data     : JSON.parse($("[data-contains=songs]").html()),
		getValue : 'quick_name',
		list     : {
			match         : { enabled : true },
			onChooseEvent : function() { var nameAndRomaji = splitNameAndRomaji(targetElem.val()); insertNameAndRomaji(targetElem[0], nameAndRomaji); }
		}
	};

	targetElem.easyAutocomplete(options).attr("data-easyautocompleted", true);
}



/* Fire init when appropriate */
$("body").on("focus", '[name="tracklist[name][]"]:not([data-easyautocompleted])', function() {
	initEasyAutocomplete($(this));
	$(this).focus();
});



/* Reset easyAutocomplete() */
function resetEasyAutocomplete() {
	$("[data-easyautocompleted]").removeAttr("data-easyautocompleted");
}