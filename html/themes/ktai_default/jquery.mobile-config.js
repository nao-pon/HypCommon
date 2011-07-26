jQuery.noConflict();
jQuery(document).bind("mobileinit", function(){
	jQuery.mobile.ajaxEnabled = false;
	jQuery.mobile.ajaxLinksEnabled = false;
	jQuery.mobile.ajaxFormsEnabled = false;
	jQuery.mobile.hashListeningEnabled = false;
});
