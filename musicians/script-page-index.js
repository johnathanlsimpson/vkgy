var musicianItemTemplate = document.querySelector(".musician-list__item");
var musicianListElems = document.querySelectorAll(".musician-list__container");

musicianListElems.forEach(musicianList => {
	loadMusicianList(musicianList);
});

function loadMusicianList(musicianListElem) {
	var letter = musicianListElem.dataset.letter;
	var musicianListUrl = "/musicians/function-get_musician_list.php" + "?letter=" + letter;
		
	fetch(musicianListUrl)
	.then((resp) => resp.json())
	.then(function(data) {
		musicianListElem.removeChild(musicianListElem.querySelector(".loading"));
		
		data.forEach(musician => {
			var musicianItemClone = musicianItemTemplate.cloneNode(true);
			
			musicianItemClone.querySelector(".musician-list__link").href = "/musicians/" + musician.id + "/" + musician.friendly + "/";
			musicianItemClone.querySelector(".musician-list__link").dataset.name = musician.name;
			musicianItemClone.querySelector(".musician-list__name").innerHTML = musician.quick_name;
			musicianItemClone.querySelector(".musician-list__hint").innerHTML = (musician.hints ? musician.hints.join(" ") : null);
			musicianItemClone.querySelector(".musician-list__jp").innerHTML = (musician.romaji ? musician.name : null);
			
			musicianListElem.appendChild(musicianItemClone);
		});
		
	})
	.catch(function(error) {
		console.log(error);
	});
}