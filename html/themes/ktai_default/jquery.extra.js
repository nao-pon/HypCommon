(function ($){
$(document).ready(function (){
	$('textarea').focus(function (){
		$(this).css('min-height', '15em');
	});
});
})(jQuery);