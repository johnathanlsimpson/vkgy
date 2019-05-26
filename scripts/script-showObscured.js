// When .obscure__container clicked, trigger checkbox which will show contents
var obscureTriggerElems = document.querySelectorAll('.obscure__container');

if(obscureTriggerElems && obscureTriggerElems.length) {
	obscureTriggerElems.forEach(function(obscureTriggerElem) {
		obscureTriggerElem.addEventListener('click', function(event) {
			
			
			
			//event.preventDefault();
			
			var obscureInput = obscureTriggerElem.previousElementSibling;
			
			setTimeout(function() {
			obscureInput.checked = false; }, 100);
			
		});
	});
}