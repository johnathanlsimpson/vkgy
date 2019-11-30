// Shim for matchAll, from https://stackoverflow.com/questions/432493/
function* matchAll(str, regexp) {
	const flags = regexp.global ? regexp.flags : regexp.flags + "g";
	const re = new RegExp(regexp, flags);
	let match;
	while((match=re.exec(str))) {
		yield match;
	}
}


// When focus contenteditable, make sure cursor is at end, from: https://stackoverflow.com/questions/4233265/
function placeCaretAtEnd(el) {
	el.focus();
	if(typeof window.getSelection != 'undefined' && typeof document.createRange != 'undefined') {
		var range = document.createRange();
		range.selectNodeContents(el);
		range.collapse(false);
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange(range);
	}
	else if(typeof document.body.createTextRange != 'undefined') {
		var textRange = document.body.createTextRange();
		textRange.moveToElementText(el);
		textRange.collapse(false);
		textRange.select();
	}
}


// Debounce function for live previews, from https://davidwalsh.name/javascript-debounce-function
function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
}


// Given a string, find all Markdown matches
function insertTributeTokens(inputString) {
	
	// Markdown patterns, minutes [DisplayName] portion
	var patterns = {
		artist: /(?<=[^\w\/]|^)(?:\((\d+)\))?\/(?! )([^\/\n]+)(?! )\/(?=\W|$)/g,
		label: /(?<=[^\w\/\=]|^)(?:\{(\d+)\})?\=(?! )([^\=\/\n]+)(?! )\=(?=\W|$)/g
	};
	
	// For each pattern, get matches
	Object.entries(patterns).forEach(([patternType, pattern]) => {
		var matches = inputString.matchAll(pattern);
		var replacedMatches = [];
		
		for(var match of matches) {
			
			// Set match data
			var fullMatch = match[0];
			var matchData = {
				id: match[1],
				name: match[2] || null,
				displayName: match[3] || null
			}
			
			// Given match data, get token that will replace it
			var matchReplacement = getTributeToken(matchData, patternType);
			
			// Replace original text with token (if we haven't done so already)
			// Splitting and rejoining since replace only grabs first, and using regex here is a mess
			if(!replacedMatches.includes(fullMatch)) {
				inputString = inputString.split(fullMatch).join(matchReplacement);
				inputString = inputString.split('\n<span').join('\n&VeryThinSpace;<span');
				replacedMatches.push(fullMatch);
			}
		}
	});
	
	return inputString;
}


// Given string, format into tribute token
function getTributeToken(input, tributeType, returnType = 'rich') {
	
	// Set vars
	var id, name, displayName, friendly;
	var richTemplate, textTemplate, symbol, url, dataText, innerText;
	
	// Determine type of input
	if(typeof input === 'object' && input.hasOwnProperty('id') && input.hasOwnProperty('name')) {
		id = input.id;
		name = input.name;
		friendly = input.friendly || null;
		displayName = input.displayName || null;
	}
	else if(typeof input === 'object' && input.hasOwnProperty('original')) {
		id = input.original[0];
		friendly = input.original[1];
		name = input.original[2].split(' (')[0];
	}
	
	// Templates
	if(tributeType === 'artist') {
		symbol    = 'symbol__artist';
		url       = friendly ? '/artists/' + friendly + '/' : null;
		innerText = '(' + id + ')' + '/' + name + '/';
		dataText  = name;
	}
	else if(tributeType === 'label') {
		symbol    = 'symbol__company';
		url       = friendly ? '/labels/' + friendly + '/' : null;
		innerText = '{' + id + '}' + '=' + name + '=';
		dataText  = name;
	}
	else if(tributeType === 'musician') {
		symbol    = 'symbol__musician';
		url       = id ? '/musicians/' + id + '/' : null;
		innerText = name;
		dataText  = name;
	}
	
	// Return requested type
	if(returnType === 'rich') {
		return '' +
			'<span contenteditable="false">' +
				'&VeryThinSpace;' +
				'<' + (url ? 'a' : 'span') + (url ? ' href="' + url + '" target="_blank"' : '') + '>' +
					'<span class="any__tribute ' + symbol + '" data-text="' + dataText + '"></span>' + 
					'<span class="any__tribute-inner">' + innerText + '</span>' +
				'</' + (url ? 'a' : 'span') + '>' +
				'&VeryThinSpace;' +
			'</span>';
	}
	else if(returnType === 'text') {
		return dataText;
	}
}


// Setup options for tribute.js
function tributeSetup(tributeType) {
	var optionList, selectLinkTemplate, selectTextTemplate, trigger, valuesx;
	var optionListIsParsed;
	
	// Depending on collection type, use different source and return different string
	if(tributeType == 'artist') {
		trigger = '/';
	}
	else if(tributeType === 'label') {
		trigger = '=';
	}
	else if(tributeType === 'musician') {
		trigger = ':';
	}
	
	var tributeOptions = {
		lookup: '2',
		
		requireLeadingSpace: true,
		
		selectTemplate: function(item) {
			return getTributeToken(item, tributeType);
		},
		
		trigger: trigger,
		
		values: function(text, returnToTribute) {
			remoteSearch(text, returnToTribute, tributeType);
		}
	}
	
	return tributeOptions;
}


// Send typed text to PHP search function
function remoteSearch(text, returnToTribute, tributeType) {
	var URL = '/php/function-tribute_search.php';
	
	xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function () {
		if(xhr.readyState === 4) {
			if(xhr.status === 200) {
				if(xhr.responseText.length && xhr.responseText != 'null') {
					var data = JSON.parse(xhr.responseText);
					returnToTribute(data);
				}
				else {
					returnToTribute([]);
				}
			}
			else if(xhr.status === 403) {
				returnToTribute([]);
			}
		}
	};
	
	xhr.open("GET", URL + '?q=' + text + '&type=' + tributeType, true);
	xhr.send();
}


// Init tribute.js object and add default collections
var defaultTribute = new Tribute({
	collection: [
		tributeSetup('artist'),
		tributeSetup('label'),
		tributeSetup('musician')
	]
});


// Clean up content from a tributing element before sending somewhere else
function cleanTributingContent(tributingElem) {
	
	// Clean output
	var cleanedOutput = tributingElem.innerHTML;
	var dummyElem = document.createElement('p');

	// There's a chrome bug where display: block inserts divs for new lines, which fucks up artist bio (etc)
	// (But if we set it to inline-block, tribute.js has issues with the cursor)
	// So replace all divs with regular line breaks, remove residual divs, then set back as innerHTML so textContent will be right
	cleanedOutput = cleanedOutput.replace(/<div><br>/g, '\n');
	cleanedOutput = cleanedOutput.replace(/<div>/g, '\n');
	cleanedOutput = cleanedOutput.replace(/<\/div>|<br>/g, '');
	//tributingElem.innerHTML = cleanedOutput;
	dummyElem.innerHTML = cleanedOutput;

	// Then we have to clean up the textContent and replace hard spaces with normal
	// And remove any VeryThinSpace's, which may or may not be used to prevent bugs with tribute.js
	//cleanedOutput = tributingElem.textContent;
	cleanedOutput = dummyElem.textContent;
	cleanedOutput = cleanedOutput.replace(/&nbsp;/g, ' ');
	cleanedOutput = cleanedOutput.replace(/ |&VeryThinSpace;|&#8202;|&#x200A;/g, '');
	
	return cleanedOutput;
}


// Find inputs which use tribute.js, replace with contenteditable clones, init tribute.js on clones
function initTribute() {
	
	// Get elements which use the tribute.js script, but ignore clones
	var tributableElems = document.querySelectorAll('.any--tributable:not(.any--tributed):not(.any--tributing)');
	
	// For each tributable input, clone it as a contenteditable div
	tributableElems.forEach(function(tributableElem, index) {
		
		// Check if original input was given focus, if so we'll move focus to clone later
		var tributableIsFocused = document.activeElement === tributableElem;
		
		// Create empty clone element (& wrap in span to fight issue where Chrome inserts divs)
		var newElem = document.createElement('div');
		var newElemWrapper = document.createElement('span');
		newElemWrapper.classList.add('any__tribute-wrapper');
		newElemWrapper.appendChild(newElem);
		
		// Give focus to clone if appropriate
		if(tributableIsFocused) {
			setTimeout(function() {
				placeCaretAtEnd(newElem);
			}, 0);
		}
		
		// Copy classes from original to clone, add tributing class, and remove unnecessary classes
		// (Doing this to make sure we don't mess up any specific JS that targets by class name. Prob not the best method?)
		newElem.classList = tributableElem.classList;
		newElem.classList.add('any--tributing');
		newElem.classList.forEach(function(className, index) {
			if(className.startsWith('any') || className.startsWith('input')) {
			}
			else {
				newElem.classList.remove(className);
			}
		});
		
		// Set other attributes of new element
		newElem.setAttribute('placeholder', tributableElem.getAttribute('placeholder') || '');
		newElem.setAttribute('data-name', tributableElem.getAttribute('name'));
		newElem.setAttribute('contenteditable', true);
		
		// Get text of original input, insert into clone
		var originalText = tributableElem.textContent;
		newElem.innerHTML = originalText;
		
		// Hide original input, throw active class on it
		tributableElem.style.display = 'none';
		tributableElem.classList.add('any--tributed');
		
		// Hide original input, mark original, show contenteditable clone, insert tokens into clone
		tributableElem.parentNode.insertBefore(newElemWrapper, tributableElem);
		newElem.innerHTML = insertTributeTokens(originalText);
		
		// Init tribute.js on clone
		defaultTribute.attach(newElem);
		
		// Watch clone for paste, and remove formatting from pasted content
		newElem.addEventListener('paste', function(event) {
			event.preventDefault();
			var text = event.clipboardData.getData('text/plain');
			text = insertTributeTokens(text);
			document.execCommand('insertHTML', false, text);
		});
		
		// If original input is cleared, clear clone
		tributableElem.addEventListener('change', function(event) {
			if(tributableElem.value == '') {
				newElem.innerHTML = '';
			}
		});
		
		// If we need to preview element, set listener here
		var elemNeedsPreview = tributableElem.dataset.isPreviewed;
		if(elemNeedsPreview) {
			newElem.addEventListener('keyup', debounce(() => {
				tributableElem.value = cleanTributingContent(newElem);
				tributableElem.dispatchEvent(new Event('change'));
			}, 400));
		}
	});
}


initTribute();