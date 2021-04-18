// Init inputmask() on appropriate elements
$(":input").inputmask();


// Auto-size textareas
autosize($(".autoresize"));


// Show hidden elements on click
$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();
	showElem($(this), "edit__hidden any--hidden");
});

$(document).on("click", ".edit__hidden", function() {
	var classList = $(this).attr("class").split(" ");
	
	for(var i = 0; i < classList.length; i++) {
		$("[data-show=" + classList[i] + "]").trigger("click");
	}
});


// Log changes made
var formElement = document.getElementById('form__edit');
var changesElement = document.getElementById('form__changes');
formElement.addEventListener('change', function(event) {
	changesElement.value = changesElement.value + (changesElement.value ? ',' : '') + event.target.name;
});

// Log names of elements which had changes (for contenteditable elements)
formElement.addEventListener('contenteditable-change', function(event) {
	changesElement.value = changesElement.value + (changesElement.value ? ',' : '') + event.target.getAttribute('data-name');
});


// Handle submit
initializeInlineSubmit($("[name=form__edit]"), "/artists/function-edit.php", {
	"submitOnEvent" : "submit",
	"callbackOnSuccess" : function() {
		document.getElementById('form__changes').value = '';
	}
});


// Handle artist deletion
initDelete($("[name=delete]"), "/artists/function-delete.php", { id : $("[name=id]").val() }, function() {
	$("body").removeClass("any--pulse").addClass("any--pulse");
});


// Handle musician deletion
$(".edit__delete-musician").each(function() {
	var elem = $(this);
	initDelete(elem, "/musicians/function-delete.php", { id : elem.data("id") }, function(elem) {
		elem.parents(".edit__musician").addClass("any--fade-out");
		setTimeout(function() {
			elem.parents(".edit__musician").addClass("any--hidden");
		}, 300);
	});
});


// Preview bio
previewBio($(".edit__history"), $(".edit__history-preview"), $("[name=id]").val());
$(".edit__history").on("change", function(event) {
	previewBio($(this), $(".edit__history-preview"), $("[name=id]").val());
});


// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();


// ========================================================
// Links
// ========================================================

// Set up URL elems
let linksForm = document.querySelector('#form__links');
let linksElem = document.querySelector('.links__container');
let addLinksElem = linksElem.querySelector('.links__add');
let linksStatusElem = linksElem.querySelector('.links__status');
let linksResultElem = linksElem.querySelector('.links__result');

// When pasting link into textarea, trigger change so we can easily grab it
addLinksElem.addEventListener('paste', function(event) {
	
	// Get pasted text, set it to the textarea's value then trigger change, so we can access the value easily
	let pastedText = event.clipboardData.getData('Text');
	event.preventDefault();
	addLinksElem.value = pastedText;
	addLinksElem.dispatchEvent(new Event('change', {bubbles:true}));
	
});

// Listen for any link changes
linksElem.addEventListener('change', function(event) {
	
	// If change was adding link, trigger that function
	if( event.target.classList.contains('links__add') ) {
		addLinks();
	}
	
	// If something else changed, save whole form
	else {
		updateLinks();
	}
	
});

// Save button to manually trigger update and add any new links in textarea
let saveLinksElem = document.querySelector('.links__save');
saveLinksElem.addEventListener('click', function() {
	
	// Update old links
	updateLinks();
	saveLinksElem.blur();
	
	// If textarea has anything, also add it
	if( addLinksElem.innerHTML && addLinksElem.innerHTML.length > 0 ) {
		addLinks();
	}
	
});

// Handle deletion
let deleteLinkElems = document.querySelectorAll('.link__delete');
deleteLinkElems.forEach(function(deleteLinkElem) {
	initLinkDeleteElem( deleteLinkElem );
});

// Grab links from textarea, insert into database, then chuck back out into form
function addLinks() {
	
	// Clear result from last time
	linksResultElem.innerHTML = '';
	
	initializeInlineSubmit( $(linksForm), '/artists/function-update_links.php', {
		'preparedFormData': {
			'action': 'add',
			'artist_id': document.querySelector('[name="id"]').value,
			'add_links': addLinksElem.value,
		},
		'statusContainer': $(linksStatusElem),
		'resultContainer': $(linksResultElem),
		'callbackOnSuccess': function(formElem, returnedData) {
			
			// Loop through links and add to page
			if( returnedData.links && returnedData.links.length > 0 ) {
				returnedData.links.forEach(function(link) {
					
					// Grab URL template as HTML, filter out {attributes}
					let urlTemplate = document.querySelector('#template-url').innerHTML;
					urlTemplate = urlTemplate.replace(/{.+?}/g, '');
					
					// Create div so we can turn URL template into node
					let newUrl = document.createElement('div');
					newUrl.innerHTML = urlTemplate;
					
					// Get node
					let linkElem = newUrl.firstElementChild;
					
					// Set values
					linkElem.querySelector('[name="url_content[]"]').value = link.url;
					linkElem.querySelector('[name="url_id[]"]').value = link.id;
					linkElem.querySelector('[name="url_type[]"] option[value="' + link.type + '"]').selected = true;
					linkElem.querySelector('[name="url_is_active[]"]').checked = true;
					linkElem.querySelector('[name="url_is_active[]"]').name = 'url_is_active[' + link.id + ']';
					linkElem.querySelector('.link__delete').dataset.linkId = link.id;
					
					// Initialize deletion
					initLinkDeleteElem( linkElem.querySelector('.link__delete') );
					
					// Musician may or may not be set
					if( link.musician_id && link.musician_id > -1 ) {
						linkElem.querySelector('[name="url_musician_id[]"]').innerHTML += '<option value="' + link.musician_id + '" selected>(tagged)</option>';
					}
					
					// Make fade in
					linkElem.classList.add('any--fade-in');
					
					// Insert new node before last element (add button) of parent wrapper
					linksElem.insertBefore(linkElem, addLinksElem.closest('li'));
					
					// Init selectize on new URL container and url_is_retired dummy checkboxes
					lookForSelectize();
					
				});
			}
			
			// Clear textarea
			addLinksElem.value = '';
			
			// Clear status after a sec
			setTimeout(function() {
				linksStatusElem.classList.remove('symbol__success', 'symbol__error', 'loading');
			}, 1000);
			
		},
		'callbackOnError': function(formElem, returnedData) {
		}
	});
	
}

// Update links
function updateLinks() {
	
	initializeInlineSubmit( $(linksForm), '/artists/function-update_links.php', {
		'statusContainer': $(linksStatusElem),
		'resultContainer': $(linksResultElem),
		'callbackOnSuccess': function(formElem, returnedData) {
			
			console.log('success');
			console.log(returnedData);
			
			// Clear status after a sec
			setTimeout(function() {
				linksStatusElem.classList.remove('symbol__success', 'symbol__error', 'loading');
			}, 2500);
			
		},
		'callbackOnError': function(formElem, returnedData) {
			console.log('error');
			console.log(returnedData);
		}
	});

}

// Initialize link deletion buttons
function initLinkDeleteElem( deleteLinkElem ) {
	
	initDelete( $(deleteLinkElem), '/artists/function-update_links.php', { 'action': 'delete', 'link_id': deleteLinkElem.dataset.linkId, }, function() {
		
		// Fade out link then remove
		deleteLinkElem.closest('li').classList.add('any--fade-out');
		
		setTimeout(function() {
			deleteLinkElem.closest('li').remove();
		}, 300);
		
	});
	
}