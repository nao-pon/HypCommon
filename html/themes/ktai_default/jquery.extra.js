(function ($){

$(document).ready(function (){
	$('textarea').bind('focus', function (){
		$(this).css('min-height', '15em');
	});


	$("#keitaiblockmenu a, #keitaifixedbar_main").bind('tap', function(e){
		e.stopPropagation();
		e.preventDefault();
		$.keitaiShowBlock( $.mobile.path.stripHash($(this).attr('href')) );
		//return $.keitaiShowBlock( $.mobile.path.stripHash($(this).attr('href')) );
	});

	$("#keitaifixedbar_block").bind('tap', function(e){
		e.stopPropagation();
		e.preventDefault();
		$('#keitaiblockmenu').toggle();
		//return false;
	});
});

$.extend({
	keitaiShowBlock: function(id) {
		$('#keitaiblockmenu').hide();
		var target = $('#'+id);
		if (target) {
			var offset = target.offset();
			if (offset == null) {
				$.mobile.silentScroll(2);
			} else {
				target.trigger('expand');
				//top = offset.top;
				$.mobile.silentScroll(offset.top);
			}
		}
		return false;
	},

	keitaiSwitchToPc: function() {
		var expires = new Date();
		expires.setDate(expires.getDate() + 7);
		document.cookie = "_hypktaipc=1;expires=" + expires.toUTCString() + ";path=/";
		if (location.href.match('_hypktaipc=0')) {
			location.href = location.href.replace(/[?&]_hypktaipc=0/, '');
		} else {
			location.reload(true);
		}
		return false;
	}
});

})(jQuery);