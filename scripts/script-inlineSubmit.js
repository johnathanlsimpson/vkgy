// Set default arguments for submit()
function setArguments(formElement, inputArgs) {
	var outputArgs = {};
	
	var defaultArgs = {
		returnIntoAttr         : "data-get-into",
		returnKey              : "data-get",
		removeClasses          : "any--hidden any--slide-down symbol__error symbol__loading symbol__success",
		statusClassStem        : "symbol__",
		loadingClass           : "symbol__loading",
		hideClass              : "any--hidden",
		transitionOutClass     : "any--slide-out",
		transitionInClass      : "any--slide-in",
		attentionClass         : "any--pulse",
		
		submitButton           : formElement.find("[data-role=submit]"),
		submitContainer        : formElement.find("[data-role=submit-container]"),
		statusContainer        : formElement.find("[data-role=status]"),
		resultContainer        : formElement.find("[data-role=result]"),
		editButton             : formElement.find("[data-role=edit]"),
		editContainer          : formElement.find("[data-role=edit-container]"),
		
		attentionTarget        : $("body"),
		returnIntoElem         : formElement.find("[data-get]"),
		preparedFormData       : null,
		submitOnEvent          : "submit",
		callbackOnSuccess      : null,
		showEditLink           : false
	}
	
	for(var key in defaultArgs) {
		if(typeof inputArgs[key] !== "undefined") {
			outputArgs[key] = inputArgs[key];
		}
		else {
			outputArgs[key] = defaultArgs[key];
		}
	}
	
	return outputArgs;
}



// Initialize submit() on specificed element, set to fire when appropriate
function initializeInlineSubmit(formElement, processorUrl, inputArgs = {}) {
	if(typeof inputArgs.submitOnEvent !== "undefined") {
		if(inputArgs.submitOnEvent === "submit") {
			$(formElement).on(inputArgs.submitOnEvent, function(event) {
				event.preventDefault();
				submit(formElement, processorUrl, inputArgs);
			});
		}
		else {
			$(inputArgs.submitButton).on(inputArgs.submitOnEvent, function(event) {
				event.preventDefault();
				submit(formElement, processorUrl, inputArgs);
			});
		}
	}
	else {
		submit(formElement, processorUrl, inputArgs);
	}
}



// Core submit() function
function submit(formElement, processorUrl, inputArgs) {
	var args = setArguments(formElement, inputArgs);
	
	//args.resultContainer.html("");
	args.statusContainer.removeClass(args.removeClasses);
	args.statusContainer.addClass(args.loadingClass);
	
	var formData;
	var objectType = Object.prototype.toString.call(formElement[0]);

	if(objectType === "[object HTMLFormElement]") {
		formData = new FormData(formElement[0]);
	}
	else {
		formData = new FormData();
	}
	
	for(var key in args.preparedFormData) {
		formData.append(key, args.preparedFormData[key]);
	}
		
	$.ajax({
		url:         processorUrl,
		data:        formData,
		processData: false,
		contentType: false,
		method:      "post"
	})
	.done(function(returnedData) {
		if(returnedData !== "") {
			returnedData = JSON.parse(returnedData);
			
			if(returnedData != null && "status" in returnedData) {
				args.submitButton.each(function() {
					$(this).blur();
				});
				
				args.statusContainer.each(function() {
					$(this).removeClass(args.removeClasses);
					$(this).addClass(args.statusClassStem + returnedData.status);
				});
				
				args.resultContainer.each(function() {
					$(this).html(returnedData.result);
					$(this).removeClass(args.hideClass).addClass(args.revealClass);
				});
				
				if(returnedData.status === "success") {
					$.each(args.returnIntoElem, function() {
						var key = $(this).attr(args.returnKey);
						var value = returnedData[key];
						
						if($(this).is("[" + args.returnIntoAttr + "]")) {
							var attribute = $(this).attr(args.returnIntoAttr);
							$(this).attr(attribute, value);
						}
						else {
							$(this).html(value);
						}
					});
					
					if(args.showEditLink === true) {
						args.submitContainer.each(function() {
							$(this).addClass("any--hidden");
						});
						args.editContainer.each(function() {
							$(this).removeClass("any--hidden");
						});
						
						args.editButton.on("click", function(event) {
							event.preventDefault();
							
							args.editContainer.each(function() {
								$(this).addClass(args.hideClass);
							});
							args.statusContainer.each(function() {
								$(this).removeClass(args.removeClasses);
							});
							args.submitContainer.each(function() {
								$(this).removeClass(args.removeClasses);
							});
						});
					}
					
					if(typeof args.callbackOnSuccess === "function") {
						args.callbackOnSuccess(formElement, returnedData);
					}
				}
			}
		}
	});
}