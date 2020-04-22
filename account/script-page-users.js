// Sort
$(document).on("click", "[data-sort]", function(event) {
	event.preventDefault();
	
	var sortType = $(this).data("sort");
	var sortDir = $(this).data("dir");
	var oppDir = sortDir === "asc" ? "desc" : "asc";
	var direction = sortDir === "asc" ? "up" : "down";
	var oppDirection = direction === "down" ? "up" : "down";
	var selected = $(this).hasClass("input__radio--selected");
	
	if(selected) {
		sortDir = oppDir;
		
		$(this).data("dir", oppDir);
		$(this).removeClass("symbol__" + direction + "-caret").addClass("symbol__" + oppDirection + "-caret");
	}
	
	$("[data-sort]").removeClass("input__radio--selected");
	$(this).addClass("input__radio--selected");
	
	tinysort($(".user__container"), {
		attr : "data-" + sortType,
		order : sortDir
	});
});



// Filter
$(document).on("click", "[data-filter]", function() {
	$("[data-filter]").removeClass("input__radio--selected");
	$(this).addClass("input__radio--selected");
});