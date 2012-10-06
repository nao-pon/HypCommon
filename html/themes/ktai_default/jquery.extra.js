(function ($){

$(document).ready(function (){
	$('#ktai_theme_block_menu').attr('_height', $('#ktai_theme_block_menu').height());
	$('#keitaiblockmenu').hide();
	
	$('textarea').bind('focus', function (){
		$(this).css('min-height', '15em');
	});

	$("#keitaiblockmenu a, #keitaifixedbar_main").bind('tap', function(e){
		e.stopPropagation();
		e.preventDefault();
		$.keitaiShowBlock( $.mobile.path.stripHash($(this).attr('href')) );
		return false;
	});
	
	$("#keitaifixedbar_block").bind('tap', function(e){
		e.stopPropagation();
		e.preventDefault();
		$.androidDomStackEventFix.set($('#keitaiblockmenu'));
		$('#keitaiblockmenu').toggle();
		return false;
	});
});

$.extend({
	keitaiShowBlock: function(id) {
		$('#keitaiblockmenu').hide();

		var target = $('#'+id);
		var scrTo;
		var doc = $($.browser.safari ? 'body' : 'html');
		var speed = 600;

		if (target) {
			var offset = target.offset();
			if (offset == null) {
				scrTo = 2;
			} else {
				target.trigger('expand');
				scrTo = offset.top;
			}
			doc.stop().animate({ scrollTop: scrTo } , { duration : speed }, "linear", function(){$.androidDomStackEventFix.hideOverlay($('#keitaiblockmenu'));});
		}
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
	},
	
	androidDomStackEventFix: {
		checkedElements: [],
		touchedElements: [],
		set: function(target) {
			if (navigator.userAgent.indexOf('Android') > 0) {
				if (target.css('display') != 'none') {
					$.androidDomStackEventFix.hideOverlay(target);
				} else {
					$.androidDomStackEventFix.showOverlay(target);
				}
			}
		},
		showOverlay: function(target) {
			if (target.css('display') != 'none') return;
			var ancestor = function(e, name, deep) {
				if (e.nodeName == name) return e;
				if (e.parentNode) {
					if (deep < 0) return null;
					return ancestor(e.parentNode, name, deep - 1);
				} else {
					return null;
				}
			};
			var ye = $(window).height();
			var ys = ye - $('#ktai_theme_block_menu').attr('_height');
			ye -= $('#ktai_theme_block_menu').height();
			var xs = 0;
			var xe = $(window).width();
			
			$.androidDomStackEventFix.touchedElements = [];
			
			for (var y = ys; y < ye; y += 20) {
				for (var x = xs; x < xe; x += 20) {
					var e = document.elementFromPoint(x, y);
					if (!e) continue;
					if (e._checked) continue;

					if (e.nodeName == 'INPUT' || e.nodeName == 'TEXTAREA') {
						e._orig_disabled = e.disabled;
						e.disabled = true;
						$.androidDomStackEventFix.touchedElements.push(a);
					} else {
						if ((a = ancestor(e, 'A', 3))) {
							if (a._checked) continue;
							a._checked = true;
							a._orig_style = a.getAttribute('style');
							a.setAttribute('style', document.defaultView.getComputedStyle(a, "").cssText);
							a.setAttribute('xhref', a.getAttribute('href'));
							a.removeAttribute('href');
							$.androidDomStackEventFix.checkedElements.push(a);
							$.androidDomStackEventFix.touchedElements.push(a);
						}
					}
					e._checked = true;
					$.androidDomStackEventFix.checkedElements.push(e);
				}
			}
		},

		hideOverlay: function() {
			if (navigator.userAgent.indexOf('Android') > 0) {
				setTimeout(function(){
					var i, len, a;
					for (i = 0, len = $.androidDomStackEventFix.touchedElements.length; i < len; i++) {
						a = $.androidDomStackEventFix.touchedElements[i];
						if (a.nodeName == 'A') {
							a.setAttribute('href', a.getAttribute('xhref'));
							a.removeAttribute('xhref');
							a.setAttribute('style', a._orig_style);
						} else {
							a.disabled = a._orig_disabled;
						}
					}
					$.androidDomStackEventFix.touchedElements = [];
					
					for (i = 0, len = $.androidDomStackEventFix.checkedElements.length; i < len; i++) {
						$.androidDomStackEventFix.checkedElements[i]._checked = false;
					}
				}, 500);
			}
		}
	}
});

})(jQuery);