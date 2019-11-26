// Shim for matchAll, from https://stackoverflow.com/questions/432493/
function* matchAll(str, regexp) {
	const flags = regexp.global ? regexp.flags : regexp.flags + "g";
	const re = new RegExp(regexp, flags);
	let match;
	while((match=re.exec(str))) {
		yield match;
	}
}

// Given a string, find all Markdown matches
function insertTributeTokens(inputString) {
	
	// Markdown patterns
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
			'&VeryThinSpace;<span contenteditable="false">' +
				'<' + (url ? 'a href="' + url + '" target="_blank"' : 'span') + ' class="any__tribute ' + symbol + '" data-text="' + dataText + '">' +
					innerText + 
				'</' + (url ? 'a' : 'span') + '>' +
			'</span>&VeryThinSpace;';
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

// Attach tribute.js to appropriate inputs
function initTribute() {
	defaultTribute.detach(document.querySelectorAll('.any--tributable'));
	
	setTimeout(function() {
		defaultTribute.attach(document.querySelectorAll('.any--tributable'));
	}, 100);
}

// Detach tribute.js
function detachTribute() {
}

// Find inputs which use tribute.js and replace with contenteditable clones that can actually use it
function cloneTributableElems() {
	
	// Get elements which use the tribute.js script, but ignore clones
	var tributableElems = document.querySelectorAll('.any--tributable:not(.any--tributing)');
	
	// For each tributable input, clone it as a contenteditable div
	tributableElems.forEach(function(tributableElem, index) {
		
		// Create empty clone element (& wrap in span to fight issue where Chrome inserts divs)
		var newElem = document.createElement('div');
		var newElemWrapper = document.createElement('span');
		newElemWrapper.classList.add('any__tribute-wrapper');
		newElemWrapper.appendChild(newElem);
		
		// Set classes and attributes for clone
		newElem.classList = tributableElem.classList;
		newElem.classList.remove('autosize');
		newElem.classList.add('any--tributing');
		newElem.setAttribute('placeholder', tributableElem.getAttribute('placeholder') || '');
		newElem.setAttribute('data-name', tributableElem.getAttribute('name'));
		newElem.setAttribute('contenteditable', true);
		
		// Get text of original input, change references to tribute tokens, insert into clone
		var originalText = tributableElem.textContent;
		
		// Hide original input and show contenteditable clone
		tributableElem.style.display = 'none';
		tributableElem.parentNode.insertBefore(newElemWrapper, tributableElem);
		newElem.innerHTML = originalText;
		newElem.innerHTML = insertTributeTokens(originalText);
		
		// We might want a keyup listener at some point?
		/*newElem.addEventListener('keyup', debounce(() => {
			tributableElem.value = newElem.textContent;
		}, 1000)); */
	});
}

cloneTributableElems();
initTribute();