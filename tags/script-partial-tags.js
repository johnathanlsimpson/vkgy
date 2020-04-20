// Tag items
$('.tag__wrapper .any__tag').on("click", function() {
	$(this).removeClass("symbol__tag symbol__loading symbol__error");
});

for(var i = 0; i < $(".any__tag").length; i++) {
	var thisTagButton = $(".any__tag").eq(i);
	var itemId = $(thisTagButton).attr("data-id");
	var itemType = $(thisTagButton).attr('data-item-type');
	var tagId = $(thisTagButton).attr("data-tag-id");
	var action = $(thisTagButton).attr("data-action");
	
	initializeInlineSubmit($(thisTagButton), "/tags/function-tag.php", {
		submitButton: $(thisTagButton),
		statusContainer: $(thisTagButton),
		submitOnEvent: "click",
		preparedFormData: { "action" : action, "id" : itemId, "tag_id" : tagId, 'item_type': itemType },
		callbackOnSuccess: function(formElement, returnedData) {
			if(returnedData.is_checked) {
				$(formElement).addClass("symbol__tag any__tag--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
			}
		},
		callbackOnError: function(formElement, returnedData) {
		}
		
	});
}