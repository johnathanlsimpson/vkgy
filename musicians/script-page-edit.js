// Init helpers 
$(':input').inputmask();
autosize($('.autoresize'));
lookForSelectize();

// Handle submit
initializeInlineSubmit($('[name="edit_musician"]'), '/musicians/function-edit.php', {
	
	'submitOnEvent': 'submit',
	'callbackOnSuccess': function() {
		document.querySelector('[name="changes"]').value = '';
	}
	
});

// Log changes
let formElem = document.querySelector('[name="edit_musician"]');
let changesElem = document.querySelector('[name="changes"]');

// Log changes for normal elems
formElem.addEventListener('change', function(event) {
	changesElem.value = changesElem.value + (changesElem.value ? ',' : '') + event.target.name;
});

// Log names of elements which had changes (for contenteditable elements)
formElem.addEventListener('contenteditable-change', function(event) {
	changesElem.value = changesElem.value + (changesElem.value ? ',' : '') + event.target.getAttribute('data-name');
});

// Handle deletion
let deleteElem = document.querySelector('[name="delete"]');

initDelete( $(deleteElem), '/musicians/function-delete.php', { id : deleteElem.dataset.id }, function(deleteElem) {
	
	$('body').removeClass('any--pulse').addClass('any--pulse');
	
	setTimeout(function() {
		location.href = '/musicians/add/';
	}, 300);
	
});