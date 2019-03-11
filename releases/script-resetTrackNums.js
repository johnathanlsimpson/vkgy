function resetTrackNums() {
	var i = 0;
	var n = 0;

	$(".track").each(function(index) {
		if($(this).hasClass("track--show-disc")) {
			i = 0;
			n = 0;
		}

		if($(this).hasClass("track--show-song")) {
			i++;
			$(this).find(".track__num").attr("data-count", i);
		}
	});
}