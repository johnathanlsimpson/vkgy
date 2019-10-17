// Autosize
autosize($(".autosize"));



// Submit
initializeInlineSubmit($("[name=form__edit]"), "/lives/function-update_live.php", {
	submitOnEvent : "submit",
	showEditLink : true,
	callbackOnSuccess : function(formElement, returnedData) {
		/*var e = new Event('item-id-updated');
		e.details = {
			'id' : returnedData.id
		};
		document.dispatchEvent(e);*/
	}
});

// Delete

// Change page state if adding or editing
function changePageState(state) {
	var hideClass = "any--hidden";
	var attnClass = "any--pulse";
	
	$("body").removeClass(attnClass);
	
	/*if(state === "add") {
		
		
		$("h1").addClass(hideClass);
		$("h2").html("Add release");
		$("[data-role=delete]").addClass(hideClass);
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		$("[name=friendly]").attr("value", "");
		$("[name=id]").attr("value", "");
		
		
		
		document.querySelector('[name=image_item_id]').value = null;
		document.querySelector('[name=image_item_name]').value = null;
		var images = document.querySelectorAll('.image__results .image__template');
		if(images && images.length) {
			images.forEach(function(image) {
				image.remove();
			});
		}
		
		
		history.pushState(null, null, "/releases/add/");
	}
	else if(state === "edit") {
		$("h1").removeClass(hideClass);
		$("h2").html("Edit release");
		$("[data-role=delete]").removeClass(hideClass);
		$("[data-role=status]").removeClass();
		$("[data-role=result").html("");
		$("[data-role=submit-container]").removeClass(hideClass);
		$("[data-role=result-container]").addClass(hideClass);
		$("[data-role=edit-container]").addClass(hideClass);
		history.pushState(null, null, $("[data-get=url]").attr("href") + "edit/");
	}*/
	
	setTimeout(function() {
		$("body").addClass(attnClass);
	}, 1);
}