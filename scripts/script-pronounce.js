function pronounce(text) {
	var pronunciationVoice = window.speechSynthesis;
	var pronunciationUtterance = new SpeechSynthesisUtterance(text);
	pronunciationUtterance.lang = "ja-JP";
	pronunciationVoice.speak(pronunciationUtterance);
}