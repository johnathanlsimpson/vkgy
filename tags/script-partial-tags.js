/*// Tag items
let tagElems = document.querySelectorAll('.tag__wrapper .any__tag');
if(tagElems && tagElems.length) {
	tagElems.forEach(function(tagElem) {
		
		let itemId     = tagElem.dataset.id;
		let itemType   = tagElem.dataset.itemType;
		let tagId      = tagElem.dataset.tagId;
		let action     = tagElem.dataset.action;
		
		initializeInlineSubmit($(tagElem), "/tags/function-tag.php", {
			//submitButton: $(thisTagButton),
			//statusContainer: $(thisTagButton),
			submitOnEvent: "click",
			preparedFormData: { "action" : action, "id" : itemId, "tag_id" : tagId, 'item_type': itemType },
			callbackOnSuccess: function(formElement, returnedData) {
				//if(returnedData.is_checked) {
					//$(formElement).addClass("symbol__tag any__tag--selected");
				//}
				//else {
					//$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
				//}
			},
			callbackOnError: function(formElement, returnedData) {
			}
			
		});
		
	});
}*/

// Listen for permanent deletions
let deleteTagElems = document.querySelectorAll('.tag__wrapper .any__tag[data-action="permanent_delete"]');
if(deleteTagElems && deleteTagElems.length) {
	
	deleteTagElems.forEach(function(deleteTagElem) {
		
		// If list choice input was found, watch it for changes (a.k.a. clicks to parent)
		if(deleteTagElem) {
			deleteTagElem.addEventListener('click', handleTag.bind(null, deleteTagElem));
		}
		
	});
	
}

// Listen for clicks on list buttons
let tagElems = document.querySelectorAll('.tag__wrapper .any__tag');
if(tagElems && tagElems.length) {
	
	// Watch checkbox inside each list button, trigger handleTag when it changes
	tagElems.forEach(function(tagElem) {
		
		// List choice input is probably inside the list button, but could also be outside
		let tagChoiceElem = tagElem.querySelector('.input__choice');
		if(!tagChoiceElem) {
			tagChoiceElem = document.querySelector( '#' + tagElem.getAttribute('for') );
		}
		
		// If list choice input was found, watch it for changes (a.k.a. clicks to parent)
		if(tagChoiceElem) {
			tagChoiceElem.addEventListener('change', handleTag.bind(null, tagElem, null));
		}
		
	});
	
}

// Handle clicks on list buttons
function handleTag(tagElem, tagChoiceElem, event) {
	
	let tagId        = tagElem.dataset.tagId;
	let itemId       = tagElem.dataset.id;
	let itemType     = tagElem.dataset.itemType;
	let action       = tagElem.dataset.action;
	let statusElem   = tagElem.querySelector('.tag__status');
	let itemIsTagged;
	
	if(tagChoiceElem) {
		itemIsTagged = tagChoiceElem.checked ? 1 : 0;
	}
	
	// Set status classes
	if(statusElem) {
		statusElem.classList.remove('success');
		statusElem.classList.add('loading');
	}

	initializeInlineSubmit($(tagElem), '/tags/function-tag.php', {
		preparedFormData: {
			'action': action,
			'id': itemId,
			'tag_id': tagId,
			'item_type': itemType
		},
		
		callbackOnSuccess: function(formElem, returnedData) {
			
			// Reset status classes
			if(statusElem) {
				statusElem.classList.remove('loading');
			}
			if(statusElem && !itemIsTagged) {
				statusElem.classList.remove('symbol__success');
			}
			
			// If permanent deletion, hide tag element
			if(action === 'permanent_delete') {
				tagElem.classList.remove('any__tag--selected');
				
				setTimeout(function() {
					tagElem.classList.add('any--fade-out');
				}, 1000);
				
				setTimeout(function() {
					tagElem.style.width = '0px';
					tagElem.style.marginRight = '-13px';
					tagElem.style.whiteSpace = 'nowrap';
				}, 1300);
				
			}
			
		},
		
		callbackOnError: function(formElem, returnedData) {
		}
	});

}



/*$('.tag__wrapper .any__tag').on("click", function() {
	$(this).removeClass("symbol__tag symbol__loading symbol__error");
});

for(var i = 0; i < $(".any__tag").length; i++) {
	var thisTagButton = $(".any__tag").eq(i);
	var itemId = $(thisTagButton).attr("data-id");
	var itemType = $(thisTagButton).attr('data-item-type');
	var tagId = $(thisTagButton).attr("data-tag-id");
	var action = $(thisTagButton).attr("data-action");
	
	initializeInlineSubmit($(thisTagButton), "/tags/function-tag.php", {
		submitButton: $(thisTagButton),
		statusContainer: $(thisTagButton),
		submitOnEvent: "click",
		preparedFormData: { "action" : action, "id" : itemId, "tag_id" : tagId, 'item_type': itemType },
		callbackOnSuccess: function(formElement, returnedData) {
			if(returnedData.is_checked) {
				$(formElement).addClass("symbol__tag any__tag--selected");
			}
			else {
				$(formElement).removeClass("symbol__success symbol__loading any__tag--selected").addClass("symbol__tag");
			}
		},
		callbackOnError: function(formElement, returnedData) {
		}
		
	});
}*/