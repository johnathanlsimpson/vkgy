// Preview entry
function previewEntry() {
	var entryContent = $("[name=content]").val();
	var formData = new FormData();
	
	formData.append("content", entryContent);
	
	if(entryContent.length > 0) {
		initializeInlineSubmit($("[name=form__update]"), "/blog/function-preview_entry.php", {
			"preparedFormData" : formData,
			"statusContainer" : $(".update__preview-status"),
			"resultContainer" : $(".update__preview"),
			'callbackOnSuccess' : function() {
				var lazyLoad = new LazyLoad();
			}
		});
	}
}

previewEntry();

var typingTimer;
$("[name=content]").on("input propertychange paste", function() {
	clearTimeout(typingTimer);
	typingTimer = setTimeout(previewEntry, 200);
});

// Init inputmask() on appropriate elements
var inputMaskElems = document.querySelectorAll('[data-inputmask]');
inputMaskElems.forEach(function(inputMaskElem) {
	$(inputMaskElem).inputmask();
});

// Autosize
autosize($(".autosize"));

// Update preview image
document.addEventListener('image-updated', function(event) {
	if(event.details.targetElem.name === 'image_is_default') {
		var imagePreviewElem = document.querySelector('.update__image');
		
		if(event.details.targetElem.checked) {
			var newImageStyle = event.details.parentElem.querySelector('.image__image').style.backgroundImage;
			
			newImageStyle = newImageStyle.replace('.thumbnail.', '.medium.');
			
			imagePreviewElem.style.backgroundImage = newImageStyle;
		}
		else {
			imagePreviewElem.style.backgroundImage = '';
		}
	}
});

// Init delete button
function initDeleteButton() {
	var deleteButton = document.querySelector('[name=delete]');
	var newDeleteButton = deleteButton.cloneNode(true);
	deleteButton.parentNode.replaceChild(newDeleteButton, deleteButton);
	
	deleteButton = document.querySelector('[name=delete]');
	deleteButton.setAttribute('data-state', null);
	
	initDelete(
		$(deleteButton),
		'/blog/function-delete_entry.php',
		{ 'id': deleteButton.getAttribute('data-id') },
		function() { changeState('add'); }
	);
}

initDeleteButton();

// Submit
initializeInlineSubmit($("[name=form__update]"), "/blog/function-update_entry.php", {
	"submitOnEvent" : "submit",
	"showEditLink" : true,
	'callbackOnSuccess' : function(event, returnedData) {
		initDeleteButton();
		
		var e = new Event('item-id-updated');
		e.details = {
			'id' : returnedData.id,
			'is_queued' : returnedData.is_queued,
		};
		document.dispatchEvent(e);
	},
});

// Change states
function changeState(state) {
	var text = { "add" : "Add entry", "edit" : "Edit entry" };
	var elems = [".update__header", "[name=submit]"];
	
	for(var i = 0; i < elems.length; i++) {
		$(elems[i]).html(text[state]);
	}
	
	if(state === "edit") {
		document.title = text[state] + ": " + $("[name=title]").val() + " |  vk.gy (ブイケージ)";
		history.pushState("", "", "/blog/" + $("[name=friendly]").val() + "/edit/");
	}
	else if(state === "add") {
		elems = [
			"[data-id]",
			"[name=form__update] input",
			"[name=form__update] textarea",
			"[name=form__update] option",
			".update__preview",
			".update__image"
		];
		
		$("body").removeClass("any--pulse").addClass("any--pulse");
		$(".image__template:nth-of-type(n + 2)").remove();
		$("[name=delete]").removeClass("symbol__success symbol__loading symbol__error").addClass("symbol--standalone").html("");
		
		for(i = 0; i < elems.length; i++) {
			$(elems[i]).html("").val("").attr("selected", false).attr("checked", false).attr("src", "").attr("data-id", "");
		}
		
		document.title = text[state] + " | vk.gy (ブイケージ)";
		history.pushState("", "", "/blog/add/");
	}
}