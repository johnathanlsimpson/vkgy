// Tag musicians
$(".musician__tag").on("click", function() {
	$(this).removeClass("symbol__tag symbol__loading symbol__error");
});

for(var i = 0; i < $(".musician__tag").length; i++) {
	var thisTagButton = $(".musician__tag").eq(i);
	var musicianId = $(thisTagButton).attr("data-id");
	var tagId = $(thisTagButton).attr("data-tag_id");
	var action = $(thisTagButton).attr("data-action");
	
	initializeInlineSubmit($(thisTagButton), "/musicians/function-tag.php", {
		submitButton: $(thisTagButton),
		statusContainer: $(thisTagButton),
		submitOnEvent: "click",
		preparedFormData: { "action" : action, "id" : musicianId, "tag_id" : tagId },
		callbackOnSuccess: function(formElement, returnedData) {
			if(returnedData.is_checked) {
				$(formElement).addClass("symbol__tag any__tag--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
			}
		}
	});
}