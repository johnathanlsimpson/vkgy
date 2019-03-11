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



/* Insert name and romaji */
function insertNameAndRomaji(targetElem, nameAndRomaji) {
	targetElem.val(nameAndRomaji.name.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\("));
	targetElem.parent().next().val(nameAndRomaji.romaji.replace("&#92;&#41;", "\\)").replace("&#92;&#40;", "\\(")).focus();
}



/* initEasyAutocomplete() */
function initEasyAutocomplete(targetElem) {
	var options = {
		data     : JSON.parse($("[data-contains=songs]").html()),
		getValue : "name",
		list     : {
			match         : { enabled : true },
			onChooseEvent : function() { var nameAndRomaji = splitNameAndRomaji(targetElem.val()); insertNameAndRomaji(targetElem, nameAndRomaji); }
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