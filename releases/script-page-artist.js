$(document).on("click", "[data-sort]", function(event) {
	event.preventDefault();
	
	var sortType = $(this).data("sort");
	
	var sortDir = $(this).data("dir");
	var oppDir = sortDir === "asc" ? "desc" : "asc";
	
	var direction = sortDir === "asc" ? "up" : "down";
	var oppDirection = direction === "down" ? "up" : "down";
	
	var selected = $(this).hasClass("input__radio--selected");
	
	if(selected) {
		sortDir = oppDir;
		
		$(this).data("dir", oppDir);
		$(this).removeClass("symbol--" + direction).addClass("symbol--" + oppDirection);
	}
	
	$("[data-sort]").removeClass("input__radio--selected");
	$(this).addClass("input__radio--selected");
	
	tinysort($(".release__container"), {
		attr : "data-" + sortType,
		order : sortDir
	});
	
});



//
$(document).on("click", "[data-filter]", function() {
	$("[data-filter]").removeClass("input__radio--selected");
	$(this).addClass("input__radio--selected");
});


// Collect
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
					$(formElement).addClass("symbol__checked");
				}
				else {
					$(formElement).removeClass("symbol__success symbol__checked input__radio--selected").addClass("symbol__unchecked");
				}
		}
	});
}*/