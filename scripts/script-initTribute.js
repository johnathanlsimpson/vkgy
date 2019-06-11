// Setup options for tribute.js
function tributeSetup(tributeType) {
	var optionList, selectLinkTemplate, selectTextTemplate, trigger, valuesx;
	var optionListIsParsed;
	
	// Depending on collection type, use different source and return different string
	if(tributeType == 'artist') {
		optionList = document.querySelector('[data-contains="artists"]');
		selectLinkTemplate = function(item) {
			return '' + '<span contenteditable="false">' + '<a class="any__tribute symbol__artist" href="/artists/' + item.original[1] + '/" data-text="' + item.original[2].split(' (')[0] + '">' + '(' + item.original[0] + ')/' + item.original[2].split(' (')[0] + '/' + '</a>' + '</span>';
		}
		selectTextTemplate = function(item) {
			return '(' + item.original[0] + ')/' + item.original[1] + '/';
		}
		trigger = '/';
	}
	else if(tributeType === 'label') {
		optionList = document.querySelector('[data-contains="labels"]');
		selectLinkTemplate = function(item) {
			return '' + '<span contenteditable="false">' + '<a class="any__tribute symbol__company" href="/labels/' + item.original[1] + '/" data-text="' + item.original[2].split(' (')[0] + '">' + '{' + item.original[0] + '}=' + item.original[2].split(' (')[0] + '=' + '</a>' + '</span>';
		}
		selectTextTemplate = function(item) {
			return '{' + item.original[0] + '}=' + item.original[1] + '=';
		}
		trigger = '=';
	}
	else if(tributeType === 'musician') {
		optionList = document.querySelector('[data-contains="musicians"]');
		selectLinkTemplate = function(item) {
			return '' + '<span contenteditable="false">' + '<a class="any__tribute symbol__musician" href="/musicians/' + item.original[1] + '/" data-text="' + item.original[2].split(' (')[0] + '">' + '{' + item.original[0] + '}=' + item.original[2].split(' (')[0] + '=' + '</a>' + '</span>';
		}
		selectTextTemplate = function(item) {
			return item.original[2];
		}
		trigger = ':';
	}
	
	var tributeOptions = {
		lookup: '2',
		
		requireLeadingSpace: true,
		
		selectTemplate: function(item) {
			if(typeof item === 'undefined') {
				return null;
			}
			else if(this.range.isContentEditable(this.current.element)) {
				return selectLinkTemplate(item);
			}
			
			return selectTextTemplate(item);
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

initTribute();