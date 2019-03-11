/**
 * Update an album's rating
	*
	* !  requires inlineSubmit.js
	*
	* 1. On star click, get intended score, album ID. Pass to initializeInlineSubmit().
	* 2. Upon successful rating update, get new average rating and re-assign stars'
	*    classes to reflect the change (updateRating()).
	*/

function updateRating(clickedElement, returnedData) {
	var empty      = "symbol__star--empty";
	var full       = "symbol__star--full";
	var stars      = clickedElement.parents("ul").find(".rate__item");
	var yourRating = returnedData.user_rating;
	var avgRating  = returnedData.current_rating;
	
	if(returnedData.status === "success") {
		$.each(stars, function() {
			var score    = $(this).data("score");
			var rating   = $(this).hasClass("rate__link") ? yourRating : avgRating;
			
			$(this).removeClass(empty + " " + full);
			$(this).addClass((score <= rating ? full : empty));
		});
	}
}

$(document).on("click", ".rate__item", function(event) {
	event.preventDefault();
	
	initializeInlineSubmit($(this), "/releases/function-process_rating.php", {
		"preparedFormData"  : { "score" : $(this).data("score"), "release_id" : $(this).data("release_id") },
		"callbackOnSuccess" : function(clickedElement, returnedData) { updateRating(clickedElement, returnedData); }
	});
});