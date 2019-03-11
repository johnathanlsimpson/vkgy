var topButton = document.querySelectorAll(".footer__top")[0];
var hideButton, scrolledOnce;
window.addEventListener("scroll", function() {
	if(scrolledOnce) {
		topButton.classList.add("footer__top--visible");
		topButton.classList.add("footer__top--active");
		clearTimeout(hideButton);
		hideButton = setTimeout(function (){
			topButton.classList.remove("footer__top--active");
		}, 2000);
	}
	else {
		scrolledOnce = true;
	}
}, false);