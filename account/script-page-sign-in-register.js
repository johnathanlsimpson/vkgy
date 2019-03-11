// Submit registration
// --------------------------------------------------------
/*$("[name=form__register]").on("submit", function(event) {
	event.preventDefault();
	
	var addClass    = "body--signed-in any--pulse";
	var removeClass = "body--signed-out";
	
	initializeInlineSubmit($(this), "/account/function-register.php", {
		"callbackOnSuccess" : function(formElement, returnedData) {
			if(returnedData.status === "success") {
				//$("body").removeClass(removeClass).addClass(addClass);
			}
			else {
				//$("body").removeClass(addClass).addClass(removeClass);
			}
		}
	});
});*/

// Init register
initializeInlineSubmit($('[name=register__form]'), "/account/function-register.php", {
	'submitOnEvent' : 'submit',
});

// Init sign in
/*initializeInlineSubmit($('[name=form__main-signin]'), "/account/function-sign_in.php", {
	'submitOnEvent' : 'submit',
});*/

// Show password
function togglePassword() {
	var passwordElem = document.querySelector('[name=register_password]');
	
	if(passwordElem.type === 'password') {
		passwordElem.type = 'text';
	}
	else {
		passwordElem.type = 'password';
	}
}