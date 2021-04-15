// Look for dropdowns, apply selectize() when appropriate
lookForSelectize();

// Quick jump
var artistSelect = document.getElementById("artist_jump");
artistSelect.onchange = function() {
	var friendly = this.value;
	if(friendly) {
		window.location = '/artists/' + friendly + '/';
	}
}