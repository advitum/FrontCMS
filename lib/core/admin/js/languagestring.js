function languageString(defaultString) {
	if(typeof languageStrings != 'undefined' && typeof languageStrings[defaultString] != 'undefined') {
		return languageStrings[defaultString];
	}
	return defaultString;
}