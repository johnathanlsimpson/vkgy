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
$(".edit__history").on("keyup", function(event) {
	previewBio($(this), $(".edit__history-preview"), $("[name=id]").val());
});


// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();