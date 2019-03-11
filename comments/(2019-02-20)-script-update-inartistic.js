console.log('test');

class CommentHandler {
	constructor() {
		// Get username from signed-in element at top of page. Doesn't actually go into DB or anything, just for visual of adding new comment.
		const usernameElem = document.querySelector('.head__user span');
		this.dummyUsername = usernameElem && usernameElem.innerHTML ? usernameElem.innerHTML : 'anonymous';
		
		// Set up blank templates
		this.commentateTemplate = document.querySelector('#commentate-template');
		this.commentTemplate = document.querySelector('#comment-template');
		this.commentThreadTemplate = document.querySelector('#comment-thread-template');
	}
	
	
	populateComment(commentElem, commentData) {
		/*let userElem = commentElem.querySelector('.comment__user');
		let avatarElem = commentElem.querySelector('.comment__avatar');
		let dateElem = commentElem.querySelector('.comment__date');
		let contentElem = commentElem.querySelector('.comment__content');
		let nameElem = commentElem.querySelector('.comment__name');
		let commentateContentElem = commentElem.querySelector('[name="content"]');
		
		let threadIdInput = commentElem.querySelector('[name="thread_id"]');
		let itemIdInput = commentElem.querySelector('[name="item_id"]');
		let itemTypeInput = commentElem.querySelector('[name="item_type"]');
		let commentIdInput = commentElem.querySelector('[name="comment_id"]');
		
		let itemIdData = commentElem.querySelector('[data-item-id]');
		let itemTypeData = commentElem.querySelector('[data-item-type]');
		let commentIdData = commentElem.querySelector('[data-comment-id]');
		let isAdminData = commentElem.querySelector('[data-is-admin]');
		let isLikedData = commentElem.querySelector('[data-is-liked]');
		let numLikesData = commentElem.querySelector('[data-num-likes]');
		let isUserData = commentElem.querySelector('[data-is-user]');
		let isApprovedData = commentElem.querySelector('[data-is-approved]');
		let isDeletedData = commentElem.querySelector('[data-is-deleted]');
		let markdownData = commentElem.querySelector('[data-markdown]');
		
		let username = (commentData.username ? commentData.username : (this.dummyUsername ? this.dummyUsername : 'anonymous'));
		
		if(avatarElem) {
			avatarElem.style.backgroundImage = 'url("/usericons/avatar-' + username + '.png")';
			avatarElem.querySelector('.comment__avatar-link').href = '/users/' + username + '/';
		}
		if(userElem) {
			userElem.href = '/users/' + username + '/';
			userElem.innerHTML = username;
		}
		if(dateElem) {
			dateElem.innerHTML = commentData.dateOccurred ? commentData.dateOccurred : new Date(Date.now()).toISOString().substring(0, 19).replace('T', ' ');
		}
		if(nameElem) {
			nameElem.innerHTML = commentData.name ? commentData.name : '';
		}
		if(contentElem) {
			contentElem.innerHTML = commentData.content ? commentData.content : null;
		}
		if(commentateContentElem) {
			commentateContentElem.innerHTML = commentData.content ? commentData.content : null;
		}
		if(threadIdInput) {
			//threadIdInput.value = commentData.threadId ? commentData.threadId : null;
		}
		if(itemIdInput) {
			//itemIdInput.value = commentData.itemId ? commentData.itemId : null;
		}
		if(itemTypeInput) {
			itemTypeInput.value = commentData.itemType ? commentData.itemType : null;
		}
		if(commentIdInput) {
			commentIdInput.attr = commentData.itemId ? commentData.itemId : null;
		}
		
		if(itemIdData) {
			itemIdData.dataset.itemId = commentData.itemId ? commentData.itemId : null;
		}
		if(itemTypeData) {
			itemTypeData.dataset.itemType = commentData.itemType ? commentData.itemType : null;
		}
		if(commentIdData) {
			commentIdData.dataset.commentId = commentData.commentId ? commentData.commentId : null;
			commentIdData.id = 'comment-' + (commentData.commentId ? commentData.commentId : null);
		}
		if(isAdminData) {
			isAdminData.dataset.isAdmin = commentData.isAdmin ? commentData.isAdmin : 0;
		}
		if(isLikedData) {
			isLikedData.dataset.isLiked = commentData.isLiked ? commentData.isLiked : 0;
		}
		if(numLikesData) {
			numLikesData.dataset.numLikes = commentData.numLikes ? commentData.numLikes : 0;
		}
		if(isDeletedData) {
			isDeletedData.dataset.isDeleted = commentData.isDeleted ? commentData.isDeleted : 0;
		}
		if(isUserData) {
			isUserData.dataset.isUser = commentData.isUser ? commentData.isUser : 0;
		}
		if(isApprovedData) {
			isApprovedData.dataset.isApproved = commentData.isApproved ? commentData.isApproved : 1;
		}
		if(markdownData) {
			markdownData.dataset.markdown = commentData.markdown ? commentData.markdown : null;
		}*/
		
		return commentElem;
	}
	
	
	getCommentElemParent(parentType, childElem) {
		if(childElem) {
			let elemParent = childElem;
			let parentIsFound = false;
			let parentClass;
			
			if(parentType === 'thread') {
				parentClass = 'comment__thread';
			}
			else if(parentType === 'commentate') {
				parentClass = 'commentate__container';
			}
			else if(parentType === 'comment') {
				parentClass = 'comment__container';
			}
			
			while(!parentIsFound) {
				if(elemParent.classList.contains(parentClass)) {
					parentIsFound = true;
				}
				else {
					elemParent = elemParent.parentNode;
				}
			}
			
			return elemParent;
		}
	}
	
	
	approveInline(approveButton) {
		let commentParent = this.getCommentElemParent('comment', approveButton);
		
		initializeInlineSubmit($(approveButton), "/comments/function-approve.php", {
			statusContainer : $(approveButton),
			preparedFormData : {
				'comment_id' : commentParent.dataset.commentId,
			},
			callbackOnSuccess : function(form, data) {
				if(data.status === "success") {
					commentParent.querySelector('.comment__moderation').classList.add('any--fade-out');
					
					setTimeout(function() {
						commentParent.dataset.isApproved = 1;
					}, 300);
				}
			}
		});
	}
	
	
	deleteInline(deleteButton) {
		let commentParent = this.getCommentElemParent('comment', deleteButton);
		let commentContent = commentParent.querySelector('.comment__content');
		
		if(deleteButton.innerHTML.slice(-1) === '?') {
			initializeInlineSubmit($(deleteButton), "/comments/function-delete.php", {
				statusContainer : $(deleteButton),
				preparedFormData : {
					'comment_id' : commentParent.dataset.commentId,
				},
				callbackOnSuccess : function(form, data) {
					if(data.status === "success") {
						commentContent.classList.add('any--fade-out');
						deleteButton.classList.add('any--fade-out');
						
						setTimeout(function() {
							commentContent.innerHTML = '';
							commentContent.dataset.markdown = '';
							commentParent.dataset.isDeleted = '1';
							commentParent.querySelector('.comment__deleted').classList.add('any--fade-in');
						}, 300);
					}
				}
			});
		}
		else {
			deleteButton.innerHTML = deleteButton.innerHTML + '?';
		}
	}
	
	
	editInline(editButton) {
		let commentContainer = this.getCommentElemParent('comment', editButton);
		let commentContentElem = commentContainer.querySelector('.comment__content');
		let commentContent = commentContentElem.dataset.markdown;
		let newCommentateTemplate = document.importNode(this.commentateTemplate.content, true);
		
		// Decode original markdown version of comment
		commentContent = window.atob(commentContent);
		
		newCommentateTemplate.querySelector('[name="item_type"]').value = 'testing...';
		newCommentateTemplate.querySelector('[name="item_id"]').value = 'testing...';
		newCommentateTemplate.querySelector('[name="thread_id"]').value = 'testing...';
		newCommentateTemplate.querySelector('[name="content"]').value = 'testing...';
		newCommentateTemplate.querySelector('[name="comment_id"]').value = 'testing...';
		commentContentElem.parentNode.insertBefore(newCommentateTemplate, commentContentElem.nextSibling);
		
		/*let editCommentData = {
			'commentId' : commentContainer.dataset.commentId,
			'content' : commentContent,
			'itemId' : this.getCommentElemParent('comment', editButton).dataset.itemId,
			'itemType' : this.getCommentElemParent('comment', editButton).dataset.itemType,
		};
		
		// Populate commentate container
		let newCommentateTemplate2 = this.populateComment(newCommentateTemplate, editCommentData);
		
		// Attach handler to new submit button
		this.initCommentButton('submit', newCommentateTemplate2.querySelector('.comment__submit'));
		
		// Place commentate container appropriately
		editButton.classList.add('any--hidden');
		commentContentElem.parentNode.insertBefore(newCommentateTemplate2, commentContentElem.nextSibling);
		
		// Focus on appropriate input
		if(commentContainer.dataset.isApproved === "0") {
			commentContainer.querySelector('[name="name"]').focus();
		}
		else {
			commentContainer.querySelector('.commentate__content').focus();
		}*/
	}
	
	
	likeInline(likeButton) {
		let commentParent = this.getCommentElemParent('comment', likeButton);
		let action = (commentParent.dataset.isLiked === '0' ? 'add' : 'remove');
		let additionalCommentNum = (action === 'add' ? 1 : -1);
		
		initializeInlineSubmit($(likeButton), "/comments/function-like.php", {
			preparedFormData : {
				'comment_id' : commentParent.dataset.commentId,
				'action' : action,
			},
			callbackOnSuccess : function(form, data) {
				commentParent.dataset.isLiked = (action === 'add' ? 1 : 0);
				commentParent.dataset.numLikes = parseInt(commentParent.dataset.numLikes) + additionalCommentNum;
				commentParent.classList.add('comment--liked');
				
				likeButton.disabled = true;
				
				setTimeout(function() {
					commentParent.classList.remove('comment--liked');
					likeButton.disabled = false;
				}, 500);
			}
		});
	}
	
	
	replyInline(replyButton) {
		let threadContainer = this.getCommentElemParent('thread', replyButton);
		let newCommentTemplate = document.importNode(this.commentTemplate.content, true);
		let newCommentContent = newCommentTemplate.querySelector('.comment__content');
		let newCommentateTemplate = document.importNode(this.commentateTemplate.content, true);
		
		let newCommentData = {
			'threadId' : threadContainer.querySelector('.comment__container:first-of-type').dataset.commentId,
			'itemId' : this.getCommentElemParent('comment', replyButton).dataset.itemId,
			'itemType' : this.getCommentElemParent('comment', replyButton).dataset.itemType,
		};
		
		// Activate submit button in commentate container
		this.initCommentButton('submit', newCommentateTemplate.querySelector('.comment__submit'), 'reply');
		
		// Merge commentate container into new comment container
		newCommentContent.replaceWith(newCommentateTemplate);
		
		// Populate inputs in new comment and new commentate container
		newCommentTemplate = this.populateComment(newCommentTemplate, newCommentData);
		
		// Append new comment/commentate container to parent and focus
		threadContainer.appendChild(newCommentTemplate);
		threadContainer.querySelector('.comment__container:last-of-type .commentate__content').focus();
	}
	
	
	submitComment(submitButton, commentMethod = 'new') {
		/*let parentThread;
		let self = this;
		let commentateElem = this.getCommentElemParent('commentate', submitButton);
		let newThreadTemplate = document.importNode(this.commentThreadTemplate.content, true);
		let newCommentTemplate = document.importNode(this.commentTemplate.content, true);
		
		// Call inline submit function (until that's rewritten w/out jQuery dependency, must wrap commentateElem as a jQuery object)
		initializeInlineSubmit($(commentateElem), "/comments/function-update.php", {
			callbackOnSuccess : function(form, data) {
				// If we signed in during comment (new username =/= old username), set hash for refresh
				let newHash;
				if(data.username.length && self.dummyUsername != data.username) {
					newHash = '#' + 'comment-' + data.commentId;
				}
				
				// Re-set username if signed in during comment
				self.dummyUsername = data.username ? data.username : self.dummyUsername;
				
				// Get parsed comment data
				let newCommentData = {
					'username' : self.dummyUsername,
					'content' : data.content,
					'itemType' : data.item_type,
					'itemId' : data.item_id,
					'commentId' : data.comment_id,
					'isAdmin' : data.is_admin,
					'isUser' : data.is_user,
					'isApproved' : data.is_approved,
					'markdown' : window.btoa(data.markdown),
					'name' : data.name,
				};
				
				// Un-focus submit button, empty content area
				submitButton.blur();
				commentateElem.querySelector('.commentate__content').value = null;
				
				// Get template for new comment, populate with returned data from new comment, then initialize reply/edit buttons
				newCommentTemplate = self.populateComment(newCommentTemplate, newCommentData);
				newCommentTemplate.querySelector('.comment__container').classList.add('comment--new');
				self.initCommentButton('reply', newCommentTemplate.querySelector('.comment__reply'));
				self.initCommentButton('edit', newCommentTemplate.querySelector('.comment__edit'));
				self.initCommentButton('delete', newCommentTemplate.querySelector('.comment__delete'));
				
				// Attach new comment at appropriate location
				if(commentMethod === 'new') {
					
					// If not signed in, initialize edit button so name can be added
					if(data.is_approved === '0') {
						newCommentTemplate.querySelector('.comment__edit').click();
					}
					
					// Append new comment to parent thread
					parentThread = self.getCommentElemParent('thread', submitButton);
					newThreadTemplate.querySelector('.comment__thread').appendChild(newCommentTemplate);
					parentThread.parentNode.insertBefore(newThreadTemplate, parentThread.nextSibling);
				}
				else {
					parentThread = self.getCommentElemParent('comment', submitButton);
					parentThread.replaceWith(newCommentTemplate);
				}
				
				// If new hash was set (= newly signed in), let's refresh (we can also go to new hash, but normal refresh seems enough)
				if(newHash) {
					window.location.reload();
				}
			}
		});*/
	}
	
	
	initCommentButton(buttonType, targetElem = null, commentMethod = null) {
		if(['reply', 'delete', 'edit', 'submit', 'approve', 'like'].indexOf(buttonType) >= 0) {
			let targetClass = '.comment__' + buttonType;
			let targetElems;
			let self = this;
			
			if(targetElem) {
				targetElems = [ targetElem ];
			}
			else {
				targetElems = document.querySelectorAll(targetClass);
			}
			
			targetElems.forEach(function(elem) {
				elem.addEventListener('click', function(event) {
					event.preventDefault();
					
					if(buttonType === 'reply') {
						self.replyInline(elem);
					}
					else if(buttonType === 'edit') {
						self.editInline(elem);
					}
					else if(buttonType === 'approve') {
						self.approveInline(elem);
					}
					else if(buttonType === 'like') {
						self.likeInline(elem);
					}
					else if(buttonType === 'delete') {
						self.deleteInline(elem);
					}
					else if(buttonType === 'submit') {
						self.submitComment(elem, commentMethod);
					}
				});
			});
		}
	}
}

const commentHandler = new CommentHandler();

commentHandler.initCommentButton('submit', null, 'new');
commentHandler.initCommentButton('reply');
commentHandler.initCommentButton('edit');
commentHandler.initCommentButton('like');
commentHandler.initCommentButton('approve');
commentHandler.initCommentButton('delete');