/**
 * Like a comment on an album's page
	*
	* !  requires inlineSubmit.js
	*
	* 1. On click of like button, get ID of target comment and determine whether
	*    adding or removing like.
	* 2. Send data to inlineSubmit, set to activate immediately.
	* 3. If like successfully added to/removed from database, update like button
	*    accordingly.
	*/

$(document).on("click", ".comment__like", function(event) {
	event.preventDefault();

	var likeButton = $(this);
	var commentId  = $(this).parents("li").attr("data-comment-id");
	var likeAction = $(this).hasClass("tag--selected") ? "remove" : "add";

	initializeInlineSubmit($(this), "/releases/function-handle_comment.php", {
		statusContainer   : likeButton,
		hideClass         : "symbol__like",
		callbackOnSuccess : function(likeButton, returnedData) {
			likeButton.removeClass("symbol__success");
			returnedData.action === "add" ? likeButton.addClass("tag--selected") : likeButton.removeClass("tag--selected");
		},
		preparedFormData  : {
			"method"     : "like",
			"comment_id" : commentId,
			"action"     : likeAction
		}
	});
});