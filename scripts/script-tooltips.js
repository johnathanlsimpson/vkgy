var currentTippyElem;
var tippyTimeout;

function attachArtistTooltips(artistLinks) {
	for(var i = 0, l = artistLinks.length; i < l; i++) {
		var currentArtistElem = artistLinks[i];
		
		currentArtistElem.addEventListener("mouseenter", function() {
				if(!this.classList.contains("tippy-active")) {
				currentTippyElem = this;
				tippyTimeout = setTimeout(function() {
					customShowTippy();
				}, 300);
			}
		});
		
		currentArtistElem.addEventListener("mouseleave", function() {
			clearTimeout(tippyTimeout);
		});
		
		currentArtistElem.addEventListener("click", function() {
			clearTimeout(tippyTimeout);
		});
		
		currentArtistElem.dataset.hoverable = true;
	}
}

var artistLinks = document.querySelectorAll(".artist[data-name]:not([data-name='']):not([data-hoverable])");
attachArtistTooltips(artistLinks);

function customShowTippy() {
	var template = document.querySelector("#artistTooltip");
	var elem = currentTippyElem;
	var clonedTemplate = template.cloneNode(true);
	var friendly = elem.dataset.friendly ? elem.dataset.friendly : elem.href.match(/\/(?:artists|releases)\/([A-z0-9\-]+)/)[1];
	
	var quickName = elem.dataset.quickname ? elem.dataset.quickname : (elem.firstChild.innerHTML > 0 ? elem.firstChild.textContent : elem.textContent);
	var name = elem.dataset.name && elem.dataset.name != quickName ? elem.dataset.name : "";
	
	clonedTemplate.querySelector(".quick-name").innerHTML = quickName;
	clonedTemplate.querySelector(".quick-name").href = "/artists/" + friendly + "/";
	clonedTemplate.querySelector(".name").innerHTML = name;
	clonedTemplate.querySelector(".profile").href = "/artists/" + friendly + "/";
	clonedTemplate.querySelector(".edit").href = "/artists/" + friendly + "/edit/";
	clonedTemplate.querySelector(".news").href = "/blog/artist/" + friendly + "/";
	clonedTemplate.querySelector(".releases").href = "/releases/" + friendly + "/";

	var tips = tippy(elem, {
		arrow: true,
		delay: [0, 500],
		dynamicTitle: false,
		html: clonedTemplate,
		interactive: true,
		interactiveBorder: 5,
		onShow: function() {
			clonedTemplate.parentNode.parentNode.className += " " + "any__obscure any__obscure--faint text text--notice text--compact";
			clonedTemplate.parentNode.parentNode.style.backgroundImage = "url(" + clonedTemplate.querySelector(".profile").href + "/main.small.jpg)";
		},
		onHidden: function(elem) {
			tips.destroyAll();
		},
	});
	
	elem._tippy.show();
}