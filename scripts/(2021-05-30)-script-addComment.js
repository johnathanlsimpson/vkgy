/**
 * Add comment to album's page
	*
	* !  requires inlineSubmit.js
	*
	* 1. Initialize inlineSubmit() on submition of comment.
	* 2. If comment successfully added, make copy of comment template, insert
	*    new comment, then prepend to comment display area.
	* 3. Empty textarea.
	*/

// Reply function
$(document).on("click", ".comment__reply", function(event) {
	event.preventDefault();
	
	var parentElem = $(this).parents(".comment__template");
	var threadId = $(parentElem).attr("data-thread-id");
	var commentId = $(parentElem).find("[data-comment-id]").attr("data-comment-id");
	var textarea = $(".comment__textarea");
	var comment = $(textarea).val();
	var replyTxt = ">" + (threadId && threadId !== "undefined" ? threadId : commentId);
	
	if(comment.match(/^\>\d+$/m)) {
		comment = comment.replace(/^\>\d+$/m, replyTxt);
	}
	else {
		comment = replyTxt + "\r\n" + comment;
	}
	
	$(textarea).val(comment);
});






function showComment(formElement, returnedData) {
	var commentContainer = $(".comment__container");
	var commentTemplate = document.querySelector(".comment__template");
	var newComment = commentTemplate.outerHTML.replace("comment__template", "comment__template any--fade-in");
	var commentId = returnedData.comment_id;
	var oldComment = $(".comment__container [data-comment-id=" + commentId + "]").parents(".comment__template");
	var threadId = returnedData.thread_id;
	
	$("[name=comment_id]").val("");
	$(".comment__textarea").val("");
	
	if(oldComment.length) {
		$(oldComment).replaceWith(newComment);
	}
	else if(threadId) {
		var threadContainer = $('.comment__container [data-thread-id="' + threadId + '"]');
		var parentComment = $(".comment__container [data-comment-id=" + threadId + "]").parents(".comment__template");
		
		if(threadContainer.length) {
			$(threadContainer).after(newComment);
		}
		else {
			$(parentComment).after(newComment);
		}
	}
	else {
		$(commentContainer).prepend(newComment);
	}
}






$(document).on("click", ".comment__delete", function() {
	var parentElem = $(this).parents(".comment__template");
	var deleteButton = $(this);
	var id = $(parentElem).find("[data-comment-id]").attr("data-comment-id");
	
	initDelete(deleteButton, "/php/function-delete_comment.php", { "id" : id }, function() { $(parentElem).addClass("any--fade-out"); setTimeout(function() { $(parentElem).remove(); }, 300); }, true);
});



$(document).on("click", ".comment__edit", function() {
	var parentElem = $(this).parents(".comment__template");
	var id = $(parentElem).find("[data-comment-id]").attr("data-comment-id");
	var idElem = $("[name=comment_id]");
	var markdown = $(parentElem).find("[data-get=markdown]").html();
	var textarea = $(".comment__textarea");
	
	textarea.val(markdown);
	idElem.val(id);
});






initializeInlineSubmit($("[name=form__comment]"), "/php/function-add_comment.php", {
	callbackOnSuccess : showComment,
	submitOnEvent     : "submit"
});