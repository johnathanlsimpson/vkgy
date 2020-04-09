// Look for dropdowns, apply selectize() when appropriate
// --------------------------------------------------------
lookForSelectize();

// Submit edits
// --------------------------------------------------------
initializeInlineSubmit($("[name=form__edit]"), "/account/function-edit.php", {
	showEditLink : true,
	submitOnEvent: "submit"
});

// Init inputmask() on appropriate elements
// --------------------------------------------------------
$(":input").inputmask();


// Quick style switching (need to re-do)
// --------------------------------------------------------
var themeZeroButton = document.getElementById('site_theme_0');
var themeOneButton = document.getElementById('site_theme_1');
var themeCSSLink = document.getElementById('stylesheet_theme');

// This button may not exist if only editing user roles
if(themeZeroButton) {
	themeZeroButton.onclick = function(event) {
		themeCSSLink.setAttribute('href', '/style/style-colors-0.css');
	};
	themeOneButton.onclick = function(event) {
		themeCSSLink.setAttribute('href', '/style/style-colors-1.css');
	};
}


// Show/hide pronouns option
// --------------------------------------------------------
var pronounsSelector = document.querySelector('[name="pronouns"]');
var pronounsElem = document.querySelector('[name="custom_pronouns"]');

// This button may not exist if only editing user roles
if(pronounsSelector) {
	if(pronounsSelector.value === 'custom') {
		pronounsElem.classList.remove('any--hidden');
	}
	pronounsSelector.addEventListener('change', function(event) {
		console.log('a'); console.log(pronounsSelector.value);
		if(pronounsSelector.value === 'custom') { console.log('b');
			pronounsElem.classList.remove('any--hidden');
			pronounsElem.focus();
		}
		else { console.log('c');
			pronounsElem.classList.add('any--hidden');
		}
	});
}


// Move/update tooltip with "fan since" range
// --------------------------------------------------------
let sinceElem = document.querySelector('.fan-since__input');
let sinceLabel = document.querySelector('.fan-since__tooltip');

// This button may not exist if only editing user roles
if(sinceElem) {
	sinceElem.addEventListener('change', () => {
		let sinceValue = sinceElem.value;
		sinceLabel.innerHTML = sinceValue > sinceElem.min ? sinceValue : '~' + sinceValue;
		sinceLabel.style.setProperty('--fan-since', sinceValue);
	});
}


// Associate permissions with roles
// --------------------------------------------------------
// Check if there's a permission field on page (meaning we're allowed to set permissions)
if(document.querySelector('[name^="can_"]')) {
	
	let isPairings = {
		'is_vip' : [ 'can_access_drafts' ],
		'is_editor' : [ 'can_add_data' ],
		'is_moderator' : [ 'can_approve_data', 'can_delete_data', 'can_edit_roles' ]
	};
	let isElems = document.querySelectorAll('[name^="is_"]');
	let isName, isChecked;
	
	// Every time role input is changed, get name and status
	isElems.forEach(function(elem) {
		elem.addEventListener('change', function() {
			
			isName = elem.name;
			isChecked = elem.checked;
			
			// Check/unchecked associated permission inputs
			if(isPairings[isName]) {
				isPairings[isName].forEach(function(canName) {
					document.querySelector('[name="' + canName + '"]').checked = isChecked;
				});
			}
			
		});
	});
	
}