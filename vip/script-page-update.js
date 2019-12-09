autosize($(".autosize"));


initializeInlineSubmit($("[name=form__update]"), "/vip/function-update_entry.php", { "submitOnEvent" : "submit", "showEditLink" : true });


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