// Firefox doesn't properly handle breaks within contenteditable, deletion of tokens, backspacing of tokens, cursor around tokens, etc
// Wasn't able to find a pratical solution for handling all of these issues, so just disabling for FF.
var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;


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
	
	// Markdown patterns, minus [DisplayName] portion, and minus lookbehind since FF doesn't support it
	var patterns = {
		artist: /([^\w\/]|^)(?:\((\d+)\))?\/(?! )([^\/\n]+)(?! )\/(?=\W|$)/g,
		label: /([^\w\/\=]|^)(?:\{(\d+)\})?\=(?! )([^\=\/\n]+)(?! )\=(?=\W|$)/g
	};
	
	// For each pattern, get matches
	Object.entries(patterns).forEach(([patternType, pattern]) => {
		var matches = inputString.matchAll(pattern);
		var replacedMatches = [];
		
		for(var match of matches) {
			
			// Set match data
			var fullMatch = match[0];
			var matchData = {
				prevChar: match[1],
				id: match[2],
				name: match[3] || null,
				displayName: match[4] || null
			}
			
			// Given match data, get token that will replace it
			var matchReplacement = matchData.prevChar + getTributeToken(matchData, patternType);
			
			// Replace original text with token (if we haven't done so already)
			// Splitting and rejoining since replace only grabs first, and using regex here is a mess
			if(!replacedMatches.includes(fullMatch)) {
				inputString = inputString.split(fullMatch).join(matchReplacement);
				//inputString = inputString.split('\n<span').join('\n&VeryThinSpace;<span');
				//inputString = inputString.split(/^<span/g).join('▒<span');
				//inputString = inputString.split('\n<span').join('\n▒<span');
				//inputString = inputString.split(/<\/span>$/g).join('</span>▒');
				replacedMatches.push(fullMatch);
			}
		}
	});
	
	return inputString;
}


// Given string, format into tribute token
var tributeTokenNum = 0;
function getTributeToken(input, tributeType, returnType = 'rich') {
	
	tributeTokenNum++;
	
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
			(isFirefox || !isFirefox ? '﻿' : '') +
			'<span class="tribute__wrapper" contenteditable="false">' +
				'&VeryThinSpace;' +
				'<' + (url ? 'a' : 'span') + ' class="tribute__container" ' + (url ? ' href="' + url + '" target="_blank"' : '') + '>' +
					'<span class="any__tribute ' + symbol + '" data-text="' + dataText + '"></span>' + 
					'<span class="any__tribute-inner">' + innerText + '</span>' +
				'</' + (url ? 'a' : 'span') + '>' +
				'&VeryThinSpace;' +
			'</span>' +
			(isFirefox || !isFirefox ? '﻿' : '');
	}
	else {
		return innerText;
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
function cleanTributingContent(tributingElem, fullClean = true) {
	
	// Clean output
	var cleanedOutput = tributingElem.innerHTML;
	var dummyElem = document.createElement('p');

	// There's a chrome bug where display: block inserts divs for new lines, which fucks up artist bio (etc)
	// (But if we set it to inline-block, tribute.js has issues with the cursor)
	// So replace all divs with regular line breaks, remove residual divs, then set back as innerHTML so textContent will be right
	cleanedOutput = cleanedOutput.replace(/<div><br>/g, '\n');
	cleanedOutput = cleanedOutput.replace(/<div>/g, '\n');
	cleanedOutput = cleanedOutput.replace(/<\/div>|<br>/g, '');
	dummyElem.innerHTML = cleanedOutput;
	
	// Then we have to clean up the textContent and replace hard spaces with normal
	// And remove any VeryThinSpace's, which may or may not be used to prevent bugs with tribute.js
	if(fullClean) {
		cleanedOutput = dummyElem.textContent;
		cleanedOutput = cleanedOutput.replace(/&nbsp;/g, ' ');
		cleanedOutput = cleanedOutput.replace(/ |&VeryThinSpace;|&#8202;|&#x200A;/g, '');
	}
	
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
		var newElem = document.createElement('p');
		
		var useWrapper = true;
		var wrapperElem = document.createElement('div');
		if(useWrapper) {
			newElem.style.display = 'inline-block';
			wrapperElem.style.flex = '1';
			wrapperElem.appendChild(newElem);
			
			var x = document.createElement('div');
			//x.classList.add('symbol__help');
			x.classList.add('any--weaken');
			x.innerHTML = '' +
				//'keyboard shortcuts: ' +
				'<kbd style="border:1px solid currentColor;box-shadow:inset 0 -2px 0 currentColor;border-radius:3px;padding:2px 4px;">/</kbd> artist' +
				' &nbsp;&nbsp; ' + 
				'<kbd style="border:1px solid currentColor;box-shadow:inset 0 -2px 0 currentColor;border-radius:3px;padding:2px 4px;">=</kbd> company' +
				' &nbsp;&nbsp; ' + 
				'<kbd style="border:1px solid currentColor;box-shadow:inset 0 -2px 0 currentColor;border-radius:3px;padding:2px 4px;">@</kbd> user' +
				'';
			wrapperElem.appendChild(x);
			
			var y = document.createElement('label');
			y.classList.add('symbol__checked');
			y.textContent = 'Switch';
			y.style.position = 'absolute';
			y.style.right = 0;
			y.style.bottom = 0;
			wrapperElem.appendChild(y);
		}
		
		
		
		
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
		if(useWrapper) {
			tributableElem.parentNode.insertBefore(wrapperElem, tributableElem);
		}
		else {
			tributableElem.parentNode.insertBefore(newElem, tributableElem);
		}
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
		
		// If we're using Firefox, we need additional logic to handle moving around the tokens
		// Actually, since Chrome doesn't quite handle cursor before the token, let's just always use this :|
		if(isFirefox || !isFirefox) {
			newElem.addEventListener('keydown', function(event) {
				handleFirefoxMovement(event);
			});
		}
		
	});
}


// Init tribute elements
initTribute();


// Firefox fixes
function handleFirefoxMovement(event) {
	
	// Get keypress and set some vars
	var keyPressed = event.key;
	var debugOn = false;
	var tokenSeparator = '﻿';
	
	// Get our nodes and selections and stuff
	var parentNode = event.target;
	var currSelection = window.getSelection();
	var currNode = currSelection.anchorNode;
	var currNodeIndex = Array.prototype.indexOf.call(parentNode.childNodes, currNode);
	var currPosition = currSelection.anchorOffset;
	var numNodes = parentNode.childNodes.length;
	
	// Get previous and next nodes, if they exist
	var prevNode, prevPrevNode, nextNode, nextNextNode;
	if(currNodeIndex > 0) {
		prevNode = currNode.previousSibling || null;
		prevPrevNode = prevNode && currNodeIndex > 1 ? prevNode.previousSibling : null;
	}
	if(currNodeIndex + 1 < numNodes) {
		nextNode = currNode.nextSibling || null;
		nextNextNode = nextNode && currNodeIndex + 2 < numNodes ? nextNode.nextSibling : null;
	}
	
	// Only worry about deletion, backspace, left, and right
	// And ignore multicharacter selections (but might deal with them in the future)
	var weCare = ['ArrowLeft', 'ArrowRight', 'Backspace', 'Delete'].includes(keyPressed);
	var isCollapsed = currSelection.isCollapsed;
	
	// Set up some other variables
	var weStillCare;
	var cursorPosition, cursorPositionType;
	
	// Perform some tests to find where cursor is at
	if(weCare && isCollapsed) {
		
		// Normal text events
		if(currNode.nodeType === 3) {
			
			// LEFT ▒?|<span/>▒?
			if(nextNode && currPosition === currNode.length && nextNode.classList && nextNode.classList.contains('tribute__wrapper')) {
				cursorPositionType = 'LEFT';
				if(debugOn) { console.log('LEFT: ▒?|<span/>▒?'); }
			}
			
			// LEFT_ALT |▒<span/>▒?
			else if(nextNode && currNode.textContent[currPosition] === tokenSeparator && currPosition + 1 === currNode.length && nextNode.classList && nextNode.classList.contains('tribute__wrapper')) {
				cursorPositionType = 'LEFT_ALT';
				if(debugOn) { console.log('LEFT_ALT: |▒<span/>▒?'); }
			}
			
			// RIGHT ▒?<span/>|▒?
			else if(prevNode && currPosition === 0 && prevNode.classList && prevNode.classList.contains('tribute__wrapper')) {
				cursorPositionType = 'RIGHT';
				if(debugOn) { console.log('RIGHT: ▒?<span/>|▒?'); }
			}
			
			// RIGHT_ALT ▒?<span/>▒|
			else if(prevNode && currPosition === 1 && currNode.textContent[0] === tokenSeparator && prevNode.classList.contains('tribute__wrapper')) {
				cursorPositionType = 'RIGHT_ALT';
				if(debugOn) { console.log('RIGHT_ALT: ▒?<span/>▒|'); }
			}
			
		}
		
		// If we manage to get within a token
		else if(currNode.nodeType === 1) {
			
			// INSIDE ▒?<sp|an/>▒?
			if(currNode.classList.contains('tribute__wrapper')) {
				cursorPositionType = 'INSIDE';
				if(debugOn) { console.log('INSIDE: .▒?<sp|an/>▒?'); }
			}
			
			// INSIDE_ALT ▒?<sp<sp|an>an/>▒
			else if(currNode.classList.contains('any__tribute-inner') || currNode.classList.contains('any__tribute') || currNode.classList.contains('tribute__container')) {
				cursorPositionType = 'INSIDE_ALT';
				if(debugOn) { console.log('INSIDE_ALT: .▒?<sp<sp|an>an/>▒?'); }
			}
			
		}
		
		if(cursorPositionType) {
			weStillCare = true;
		}
	}
	
	// If we still care, decide which logic to perform
	if(weStillCare && isCollapsed) {
		if(debugOn) {
			event.preventDefault();
		}
		
		// Move [LEFT_ALT] -> LEFT
		if(cursorPositionType === 'LEFT_ALT') {
			if(keyPressed === 'Delete' || keyPressed === 'ArrowRight') {
				currSelection.collapse(currNode, currNode.length);
				handleFirefoxMovement(event);
			}
		}
		
		// Move [RIGHT_ALT] -> RIGHT
		if(cursorPositionType === 'RIGHT_ALT') {
			if(keyPressed === 'ArrowLeft' || keyPressed === 'Backspace') {
				currSelection.collapse(currNode, 0);
				handleFirefoxMovement(event);
			}
		}
		
		// Move [INSIDE_ALT] -> INSIDE
		if(cursorPositionType === 'INSIDE_ALT') {
			currSelection.collapse(currNode.parentNode, 0);
			handleFirefoxMovement(event);
		}
		
		// Move [INSIDE] -> LEFT or RIGHT
		if(cursorPositionType === 'INSIDE') {
			if(keyPressed === 'ArrowLeft' || keyPressed === 'Delete') {
				if(prevNode) {
					currSelection.collapse(prevNode, prevNode.length);
				}
				else {
					currSelection.collapse(currNode, 0);
				}
			}
			
			else if(keyPressed === 'ArrowRight' || keyPressed === 'Backspace') {
				if(nextNode) {
					currSelection.collapse(nextNode, 0);
				}
				else {
					currSelection.collapse(currNode, currNode.length);
				}
			}
			
			handleFirefoxMovement(event);
		}
		
		// Left or Backspace at [LEFT]
		if(cursorPositionType === 'LEFT' && (keyPressed === 'ArrowLeft' || keyPressed === 'Backspace')) {
			// If previous character ▒, include that
			if(currNode.textContent[currNode.length - 1] === tokenSeparator) {
				currSelection.collapse(currNode, currNode.length - 1);
			}
		}
		
		// Right at [LEFT]
		if(cursorPositionType === 'LEFT' && keyPressed === 'ArrowRight') {
			if(nextNextNode) {
				currSelection.collapse(nextNextNode, 0);
			}
			else {
				currSelection.collapse(currNode, currNode.length);
			}
		}
		
		// Delete at [LEFT]
		// If right after newline, and token followed by text node, cursor moves up for some reason
		// Could maybe stop Delete and trigger Backspace, but too complicated to worry about for now
		if(cursorPositionType === 'LEFT' && keyPressed === 'Delete') {
			// If previous character ▒, include that
			if(currNode.textContent[currNode.length - 1] === tokenSeparator) {
				currSelection.collapse(currNode, currNode.length - 1);
			}
			
			// If character after span is ▒, include that
			if(nextNextNode) {
				if(nextNextNode.textContent[0] === tokenSeparator) {
					currSelection.extend(nextNextNode, 1);
				}
				else {
					currSelection.extend(nextNextNode, 0);
				}
			}
			else {
				currSelection.extend(nextNode, nextNode.length);
			}
		}
		
		// Left at [RIGHT]
		if(cursorPositionType === 'RIGHT' && keyPressed === 'ArrowLeft') {
			if(prevPrevNode) {
				if(prevPrevNode.textContent[prevPrevNode.length] === tokenSeparator) {
					currSelection.collapse(prevPrevNode, prevPrevNode.length - 1);
				}
				else {
					currSelection.collapse(prevPrevNode, prevPrevNode.length);
				}
			}
			else {
				currSelection.collapse(prevNode, 0);
			}
		}
		
		// Backspace at [RIGHT]
		if(cursorPositionType === 'RIGHT' && keyPressed === 'Backspace') {
			if(currNode.textContent[0] === tokenSeparator) {
				currSelection.collapse(currNode, 1);
			}
			else {
				currSelection.collapse(currNode, 0);
			}

			if(prevPrevNode) {
				if(prevPrevNode.textContent[prevPrevNode.length - 1] === tokenSeparator) {
					currSelection.extend(prevPrevNode, prevPrevNode.length - 1);
				}
				else {
					currSelection.extend(prevPrevNode, prevPrevNode.length);
				}
			}
			else {
				currSelection.extend(prevNode, 0);
			}
		}
		
		// Right or Delete at [RIGHT]
		if(cursorPositionType === 'RIGHT' && (keyPressed === 'ArrowRight' || keyPressed === 'Delete')) {
			if(currNode.textContent[0] === tokenSeparator) {
				currSelection.collapse(currNode, 1);
			}
		}
		
	}
}