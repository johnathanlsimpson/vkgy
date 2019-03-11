// Set track numbers on initial load
resetTrackNums();

// Attach showElem() to any "show element" buttons
$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();
	showElem($(this));
});

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

// Submit
initializeInlineSubmit($("[name=add]"), "/releases/function-add.php",{
	submitOnEvent : "submit",
	showEditLink : true,
	callbackOnSuccess : function() {
		var parentElems = $(".image__template:not(.any--hidden)");
		$.each(parentElems, function() {
			updateImageData($(this));
		});
	}
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
		$(".image__template:not(:first-of-type)").remove();
		$("[name=friendly]").attr("value", "");
		$("[name=image_release_id]").attr("value", "");
		$("[name=id]").attr("value", "");
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
	inputTitle = inputTitle.replace(/^(?:(\d{1,3}))?(?:([\.．・]{1}))? ?/, '');
	return inputTitle;
}

// Paste tracklist from clipboard
document.addEventListener("paste", function(event) {
	if(event.target.getAttribute("name") === "tracklist[name][]") {
		var pasteText = event.clipboardData.getData('text/plain');
		pasteText = pasteText.split("\n");
		pasteText = pasteText.filter(function(x) { return x.replace(/\s+/, ''); });

		var numPastedLines = pasteText.length;

		if(numPastedLines > 1) {
			event.preventDefault();

			var currElem = event.target;

			for(var i=0; i<numPastedLines; i++) {
				currElem.value = currElem.value + cleanSongTitle(pasteText[i]);

				if(i + 1 < numPastedLines) {
					var isParent = false;

					while(!isParent) {
						currElem = currElem.parentElement;

						if(currElem.classList.contains("track--show-song")) {
							var addTrackButton = currElem.querySelector(".track__song-controls:last-of-type .track__song-control");
							addTrackButton.click();

							currElem = currElem.nextElementSibling;
							currElem = currElem.querySelector("input[name^=\"tracklist\[name\]\"]");

							isParent = true;
						}
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