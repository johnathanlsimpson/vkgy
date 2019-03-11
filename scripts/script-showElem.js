function showElem(inputElem, hideClass = "any--hidden") {
	var showClass   = null;
	var indirect    = false;
	
	if(!$.isPlainObject(inputElem) && inputElem.attr("data-show")) {
		showClass   = inputElem.attr("data-show").split(" ");
	}
	else if(inputElem !== undefined && inputElem.data_show !== undefined) {
		showClass   = inputElem.data_show.split(" ");
		indirect    = true;
	}

	if(showClass) {
		var revealClass  = "any--fade-in";
		var i;
		
		for(i = 0; i < showClass.length; i++) {
			if(showClass[i].indexOf("track--show-") !== -1) {
				$(".track").addClass(showClass[i]);
			}
			
			var showElem    = $("." + showClass[i].replace("track--show-", "track__"));
			
			showElem.removeClass(hideClass).addClass(revealClass);
		}
		
		$('[data-show="' + showClass.join(" ") + '"]').addClass("any--hidden");
	}
}