$(".sign-in__container").on("submit", function(event) {
	event.preventDefault();
	
	var username    = $(this).find("[name=username]").val();
	var password    = $(this).find("[name=password]").val();
	var addClass    = "body--signed-in any--pulse";
	var removeClass = "body--signed-out";
	
	initializeInlineSubmit($(this), "/account/function-sign_in.php", {
		"preparedFormData" : {
			"username" : username,
			"password" : password
		},
		"callbackOnSuccess" : function(formElement, returnedData) {
			if(returnedData.status === "success") {
				if(formElement.hasClass('sign-in--refresh')) {
					location.reload();
				}
				else if(formElement.hasClass('sign-in--back')) {
					window.history.back();
				}
				else {
					$("body").removeClass(removeClass).addClass(addClass);
				}
			}
			else {
				$("body").removeClass(addClass).addClass(removeClass);
			}
		}
	});
});