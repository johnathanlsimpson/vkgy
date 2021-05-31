// Helper to trigger a change
function triggerChange(elem) {
	setTimeout(function() {
		elem.dispatchEvent( new Event( 'change', { bubbles:true } ) );
	},100);
}