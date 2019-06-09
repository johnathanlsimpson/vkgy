// Set language selector to save setting upon click (actual switching handled by CSS)

let enButton = document.querySelector('[for="language-en"]');
let jaButton = document.querySelector('[for="language-ja"]');

function setLang(event) {
	let url = '/php/function-set_lang.php';
	let lang = 'en';
	let connection = new XMLHttpRequest();

	if(event.target.htmlFor === 'language-ja') {
		lang = 'ja';
	}

	connection.open('POST', url);
	connection.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	connection.send("lang=" + lang);
}

enButton.addEventListener('click', setLang);
jaButton.addEventListener('click', setLang);