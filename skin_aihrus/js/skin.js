// Skin Specific JS goes here. The jQuery library has already been loaded by the core templates. So if you use jQuery, you are ready to go.
jQuery(document).ready(function() {
	// Make sure the page takes up at least the full browser height.
	currentContentHeight = jQuery('#pageWrap').height();
	windowHeight = jQuery(window).height();
	headerHeight = jQuery('#header').height();

	if (currentContentHeight < headerHeight) {
		jQuery('#pageWrap').height(headerHeight);
	} else if (currentContentHeight < windowHeight) {
		jQuery('#pageWrap').height(windowHeight);
	}

	// Clear default search text on click.
	if (jQuery('#siteSearchInput').length > 0) {
		defaultSearchValue = jQuery('#siteSearchInput')[0].value;
		jQuery('#siteSearchInput').focus(function() {
			if (jQuery(this)[0].value === defaultSearchValue) {
				jQuery(this)[0].value = '';
			}
		});
		jQuery('#siteSearchInput').blur(function() {
			if (jQuery(this)[0].value === '') {
				jQuery(this)[0].value = defaultSearchValue;
			}
		});
	}
});