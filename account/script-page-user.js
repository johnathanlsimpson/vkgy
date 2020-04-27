/*// Set up "for sale" buttons
$(".collect").on("click", function() {
	$(this).removeClass("symbol__unchecked symbol__checked symbol__success symbol__loading symbol__error");
});

for(var i = 0; i < $(".collect").length; i++) {
	var thisCollectButton = $(".collect").eq(i);
	var releaseId = $(thisCollectButton).attr("data-id");
	
	initializeInlineSubmit($(thisCollectButton), "/releases/function-collect.php", {
		submitButton: $(thisCollectButton),
		statusContainer: $(thisCollectButton),
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