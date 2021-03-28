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

// Add URL on click
let urlContainer = document.querySelector('.url__wrapper');
let urlAddButton = document.querySelector('.url__add');
urlAddButton.addEventListener('click', function(event) {
	
	// Grab URL template as HTML, filter out {attributes}
	let urlTemplate = document.querySelector('#template-url').innerHTML;
	urlTemplate = urlTemplate.replace(/{.+?}/g, '');
	
	// Create div so we can turn URL template into node
	let newUrl = document.createElement('div');
	newUrl.innerHTML = urlTemplate;
	
	// Insert new node before last element (add button) of parent wrapper
	urlContainer.insertBefore(newUrl.firstElementChild, urlContainer.lastElementChild);
	
	// Init selectize on new URL container and url_is_retired dummy checkboxes
	lookForSelectize();
	initUrlRetiredDummies();
});

// Since 'url_is_retired' won't return data if checked, we use a dummy element
// so we need to init that when dummy element is checked, actual url_is_retired
// shows 1 and otherwise 0
function initUrlRetiredDummies() {
	
	// Get dummy checkboxes
	let dummyElems = document.querySelectorAll('.url__retired:not(.url__retired--active)');
	dummyElems.forEach(function(dummyElem) {
		
		// Add active class so we don't init these again
		dummyElem.classList.add('url__retired--active');
		
		// Get dummy siblings (text box containing actual value)
		let dummySibling = dummyElem.nextSibling;
		
		// On change dummy checkbox, update actual value to 0 or 1
		dummyElem.addEventListener('change', function(event) {
			dummySibling.value = dummyElem.checked ? '1' : '0';
		});
		
	});
}

// Init url_is_retired dummy checkboxes
initUrlRetiredDummies();