/**

Wrapper to handle "delete" buttons and deletion process

1. On click of "delete" button, show confirmation.
2. Init inline submit to fire on confirmation click of button.
3. Call supplied callback when successful.

**/



// Attach confirmation, init submit on second click.
function initDelete(deleteButton, processorUrl, data, callbackFn = function() {}, triggerClick = false) {
	
	$(deleteButton).off('click');
	
	$(deleteButton).on("click", function(event) {
		event.preventDefault();
		
		var buttonState = $(deleteButton).attr("data-state");
		
		if(buttonState === "confirmation") {
			initializeInlineSubmit(deleteButton, processorUrl, {
				statusContainer   : deleteButton,
				submitButton      : deleteButton,
				preparedFormData  : data,
				callbackOnSuccess : callbackFn
			});
			
			if($(deleteButton).hasClass("symbol__success")) {
				$(deleteButton).removeClass("symbol__success symbol__loading symbol__error").addClass("symbol--standalone").attr("data-state", "");
				$(deleteButton).html("");
			}
		}
		else {
			$(deleteButton).removeClass("symbol--standalone").attr("data-state", "confirmation");
			$(deleteButton).html("Delete?");
		}
	});
		
	if(triggerClick) {
		$(deleteButton).triggerHandler("click");
	}
}