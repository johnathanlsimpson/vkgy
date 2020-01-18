// Init autosize and submit
// --------------------------------------------------------
autosize($(".autosize"));

initializeInlineSubmit($("[name=form__update]"), "/vip/function-update.php", { "submitOnEvent" : "submit", "showEditLink" : true });


// Preview entry
// --------------------------------------------------------
function previewEntry() {
	var entryContent = $("[name=content]").val();
	var formData = new FormData();
	
	formData.append("content", entryContent);
	
	if(entryContent.length > 0) {
		initializeInlineSubmit($("[name=form__update]"), "/blog/function-preview_entry.php", {
			"preparedFormData" : formData,
			"statusContainer" : $(".update__preview-status"),
			"resultContainer" : $(".update__preview")
		});
	}
}

previewEntry();

var typingTimer;
$("[name=content]").on("input propertychange change paste", function() {
	clearTimeout(typingTimer);
	typingTimer = setTimeout(previewEntry, 200);
});


// Delete entry
// --------------------------------------------------------
var idElem = document.querySelector('[name="id"]');
var deleteId = idElem.value;
var deleteElem = document.querySelector('[data-role="delete"]');

initDelete($(deleteElem), '/vip/function-delete.php', { id : deleteId }, function() {
	$('body').removeClass('any--pulse').addClass('any--pulse');
	
	var formElems = document.querySelectorAll('[name="form__update"] input');
	formElems.forEach((elem) => {
		elem.value = '';
	});
	
	var formLink = document.querySelector('[name="form__update"] h2 a');
	formLink.setAttribute('href', '');
	formLink.innerHTML = '';
	
	var formTextareas = document.querySelectorAll('[name="form__update"] .input__textarea');
	formTextareas.forEach((elem) => {
		elem.value = '';
		elem.innerHTML = '';
	});
	
	var formPreview = document.querySelector('.update__preview');
	formPreview.innerHTML = '';
	
	deleteElem.innerHTML = '';
	deleteElem.classList.remove('symbol__success');
});