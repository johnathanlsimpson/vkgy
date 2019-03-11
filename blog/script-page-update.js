// Preview entry
function previewEntry() {
	var entryContent = $("[name=content]").val();
	var formData = new FormData();
	
	formData.append("content", entryContent);
	
	if(entryContent.length > 0) {
		initializeInlineSubmit($("[name=form__update]"), "/blog/function-preview_entry.php", {
			"preparedFormData" : formData,
			"statusContainer" : $(".update__preview-status"),
			"resultContainer" : $(".update__preview")
		});
	}
}

previewEntry();

var typingTimer;
$("[name=content]").on("input propertychange paste", function() {
	clearTimeout(typingTimer);
	typingTimer = setTimeout(previewEntry, 200);
});



// Autosize
autosize($(".autosize"));



// Init selectize() on dropdowns
lookForSelectize();



// Change preview image
function changePreviewImage() {
	var parentElem = $("[name=image_is_entry_default]:checked").parents(".image__template");
	var extRegex         = new RegExp(/\.(jpg|jpeg|gif|png)\)$/);
	var imageId          = $(parentElem).find("[name=image_id]").val();
	var imageUrl         = $(parentElem).find(".image__result").text();
	var imageExt         = extRegex.exec(imageUrl)[1];
	var imagePreviewElem = $(".update__image");
	
	if(imageId.length && imageExt.length) {
		$(imagePreviewElem).attr("src", "/images/" + imageId + "." + imageExt);
	}
}



// Update image and change preview image, when appropriate
var watchElems = ["[name=image_is_entry_default]", "[name=image_id]"];
for(var n = 0; n < watchElems.length; n++) {
	var watchElem = watchElems[n];
	$(document).on("change", watchElem, function() {
		var parentElem       = $(this).parents(".image__template");
		var entryId          = $("[name=id]").val();
		var imageId          = $(parentElem).find("[name=image_id]").val();
		var statusContainer  = $(parentElem).find(".image__status");
		var isDefault        = $(parentElem).find("[name=image_is_entry_default]").is(":checked");
		
		if(imageId.length) {
			if(entryId.length) {
				if((watchElem === "[name=image_is_entry_default]") || (watchElem === "[name=image_id]" && isDefault)) {
					initializeInlineSubmit($("[name=form__update]"), "/blog/function-update_image.php", { "preparedFormData" : { "id" : entryId, "image_id" : imageId }, "statusContainer" : statusContainer });
				}
			}
			
			changePreviewImage();
		}
		
		initDeleteWrapper();
	});
}



// Submit
initializeInlineSubmit($("[name=form__update]"), "/blog/function-update_entry.php", { "submitOnEvent" : "submit", "showEditLink" : true });



/*// Change states
$("[data-role=edit]").on("click", function() {
	$(".update__header").html("Edit entry");
	$("body").removeClass("any--pulse").addClass("any--pulse");
	$("[name=submit]").html("Edit entry");
	$("[name=delete]").removeClass("symbol__success symbol__loading symbol__error").addClass("symbol--standalone").html("");
	document.title = "Edit entry: " + $("[name=title]").val() + " | weloveucp.com";
	history.pushState("", "", "/blog/" + $("[name=friendly]").val() + "/edit/");
});*/



// Change states
function changeState(state) {
	var text = { "add" : "Add entry", "edit" : "Edit entry" };
	var elems = [".update__header", "[name=submit]"];
	
	for(var i = 0; i < elems.length; i++) {
		$(elems[i]).html(text[state]);
	}
	
	if(state === "edit") {
		document.title = text[state] + ": " + $("[name=title]").val() + " | weloveucp.com";
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
		
		document.title = text[state] + " | weloveucp.com";
		history.pushState("", "", "/blog/add/");
	}
}



function initDeleteWrapper() {
	$("[name=delete]").off("click");
	initDelete($("[name=delete]"), "/blog/function-delete_entry.php", { "id" : $("[name=id]").val() }, function() { changeState("add"); });
}

initDeleteWrapper();
// Delete entry
//$(document).on("click", "[name=delete]", function() {
	/*initDelete($("[name=delete]"), "/blog/function-delete_entry.php", { "id" : $("[name=id]").val() }, function() {
		$("[data-id]").attr("data-id", "");
		$("[name=submit]").html("Add entry");
		$(".update__header").html("Add entry");
		$("body").removeClass("any--pulse").addClass("any--pulse");
		$("[name=form__update] input").val("");
		$("[name=form__update] textarea").val("");
		$("[name=form__update] select option:selected").attr("selected", false);
		$("[name=form__update] input:checked").attr("checked", false);
		$(".image__template:nth-of-type(n + 2)").remove();
		$(".update__preview").html("");
		$(".update__image").attr("src", "");
		$("[name=delete]").removeClass("symbol__success symbol__loading symbol__error").addClass("symbol--standalone").html("");
		document.title = "Add blog entry | weloveucp.com";
		history.pushState("", "", "/blog/add/");
		
		console.log("hello?");
	});*/
//});