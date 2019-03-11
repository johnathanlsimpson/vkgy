$(document).on("click", "[data-show]", function(event) {
	event.preventDefault();

	var hideClass = "any--hidden";
	var show = $(this).data("show");

	$(this).addClass(hideClass);
	$("." + show).removeClass(hideClass).addClass("any--fade-in");
});