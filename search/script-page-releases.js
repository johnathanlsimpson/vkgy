lookForSelectize();

$(":input").inputmask();

///
$(document).on("click", "[data-sort]", function(event) {
	var sort      = "data-" + $(this).data("sort");
	var dir       = $(this).data("dir");
	var oppDir    = { down : "up", up : "down" };
	var dirAlias  = { up : "asc", down : "desc" };
	var target    = $("." + $(this).data("target"));
	var isChecked = $(this).prev(".input__choice").is(":checked");
	
	dir = !isChecked ? dir : oppDir[dir];
	
	$(this).find("span").removeClass("symbol__" + dir + "-caret" + " " + "symbol__" + oppDir[dir] + "-caret").addClass("symbol__" + dir + "-caret");
	$(this).data("dir", dir);
	
	tinysort(target, {
		"attr"  : sort,
		"order" : dirAlias[dir]
	});
});