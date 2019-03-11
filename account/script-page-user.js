// Lazy load release jackets
var myLazyLoad = new LazyLoad();

// Animate rareness bar
setTimeout(function() {
	$(".collection__rareness").css("background-size", $(".collection__rareness").attr("data-background"));
}, 500);

// Set up "for sale" buttons
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
				$(formElement).addClass("input__checkbox-label--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__checked input__checkbox-label--selected").addClass("symbol__unchecked");
			}
		}
	});
}

// Filter
$(document).on("click", "[data-filter]", function() {
	$("[data-filter]").removeClass("input__checkbox-label--selected");
	$(this).addClass("input__checkbox-label--selected");
});