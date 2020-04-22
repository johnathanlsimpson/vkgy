// Activate submit button
initializeInlineSubmit($("[name=form__avatar]"), "/avatar/function-edit.php", { "submitOnEvent" : "submit" });

// Add active class to active group
var avatarGroupLinks = document.querySelectorAll('.avatar__nav .tertiary-nav__link');
avatarGroupLinks.forEach(function(elem) {
	elem.addEventListener('click', function(event) {
		var activeElem = elem.parentElement.querySelector('.tertiary-nav--active');
		if(activeElem && activeElem.classList) {
			activeElem.classList.remove('tertiary-nav--active');
		}
		elem.classList.add('tertiary-nav--active');
	});
});

// Randomize
var randButton = document.getElementsByClassName('avatar__random')[0];
randButton.addEventListener('click', function(event) {
	event.preventDefault();
	chooseAvatarParts('random')
});

// Reset
var resetButton = document.getElementsByClassName('avatar__reset')[0];
resetButton.addEventListener('click', function(event) {
	event.preventDefault();
	chooseAvatarParts('reset')
});

// Randomize/reset avatar parts (loop through and hide all but one layer)
function chooseAvatarParts(method) {
	var radioElems = document.querySelectorAll('[name=form__avatar] .input__choice');
	var elemName, prevName, selectedNum;
	
	for(var i=0; i<radioElems.length; i++) {
		elemName = radioElems[i].getAttribute('name');
		
		if(prevName != elemName) {
			selectedElems = document.querySelectorAll('[name=' + elemName + ']');
			
			if(method === 'random') {
				selectedNum = Math.floor(Math.random() * Math.floor(selectedElems.length));
			}
			else if(method === 'reset') {
				selectedNum = 0;
			}
			
			for(var n=0; n<selectedElems.length; n++) {
				selectedElems[n].checked = false;
			}
			
			console.log(selectedElems[selectedNum]);
			selectedElems[selectedNum].checked = true;
		}
		
		prevName = elemName;
	}
}