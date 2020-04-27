/*$(".collect").on("click", function() {
	$(this).removeClass("symbol__unchecked symbol__checked symbol__success symbol__loading symbol__error");
});

for(var i = 0; i < $(".collect").length; i++) {
	var thisCollectButton = $(".collect").eq(i);
	var releaseId = $(thisCollectButton).attr("data-id");
	
	initializeInlineSubmit($(thisCollectButton), "/releases/function-collect.php", {
		submitButton: $(thisCollectButton),
		statusContainer: $(thisCollectButton),
		resultContainer: $(".collect__result"),
		submitOnEvent: "click",
		preparedFormData: { "id" : releaseId, "action" : $(thisCollectButton).attr("data-action") },
		callbackOnSuccess: function(formElement, returnedData) {
				if(returnedData.is_checked) {
					$(formElement).addClass("input__radio--selected");
				}
				else {
					$(formElement).removeClass("symbol__success symbol__checked input__radio--selected").addClass("symbol__unchecked");
				}
		}
	});
}*/

// Tag releases
/*$(".release__tag").on("click", function() {
	$(this).removeClass("symbol__tag symbol__loading symbol__error");
});

for(var i = 0; i < $(".release__tag").length; i++) {
	var thisTagButton = $(".release__tag").eq(i);
	var releaseId = $(thisTagButton).attr("data-id");
	var tagId = $(thisTagButton).attr("data-tag_id");
	var action = $(thisTagButton).attr("data-action");
	
	initializeInlineSubmit($(thisTagButton), "/releases/function-tag.php", {
		submitButton: $(thisTagButton),
		statusContainer: $(thisTagButton),
		submitOnEvent: "click",
		preparedFormData: { "action" : action, "id" : releaseId, "tag_id" : tagId },
		callbackOnSuccess: function(formElement, returnedData) {
			if(returnedData.is_checked) {
				$(formElement).addClass("symbol__tag any__tag--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
			}
		}
	});
}*/

// Copy info to clipboard
var shareButton = document.getElementsByClassName("release__share-link")[0];
shareButton.onclick = function() {
	let shareTextarea = document.getElementById("release__share");
	shareTextarea.select();
	document.execCommand("copy");
};