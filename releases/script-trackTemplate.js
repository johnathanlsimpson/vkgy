function trackTemplate(templateType, repeat = 1) {
	var tmpTemplate = $(".track__template").html().replace("?class", "track--show-" + templateType);
	var template    = "";
	var i;

	for(i = 0; i < repeat; i++) {
		template = template + tmpTemplate;
	}

	return template;
}