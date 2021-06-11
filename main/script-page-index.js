// Init register
initializeInlineSubmit($('[name=register__form]'), "/account/function-register.php", {
	'submitOnEvent' : 'submit',
});

// Init sign in
initializeInlineSubmit($('[name=form__main-signin]'), "/account/function-sign_in.php", {
	'submitOnEvent' : 'submit',
});

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