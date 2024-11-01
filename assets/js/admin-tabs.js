(function($) {

	$(document).on( 'click', '.nav-tab-wrapper a', function() {
		var tact = $(this).attr('activate');
		if (tact == 'none') {
			return false;
		}
		else if (tact) {
			$('.xtra_tabbes').hide();
			$('.xtra_tabbes.xtra_tabbeID_'+tact).show();
			if (tact=='*') $('.xtra_tabbes').show();
			$('.xtra_opt_submit').show();
			if (tact.length>1) $('.xtra_opt_submit').hide();
			$('.nav-tab').removeClass('nav-tab-active');
			$('.nav-tab[activate="'+tact+'"]').addClass('nav-tab-active');
			$('.nav-tab[activate="'+tact+'"]').blur();
			$('#xtra_submit_last_seltab').val(tact);
			if ($(window).scrollTop() >= 80) $(window).scrollTop(80);
			else $(window).scrollTop(0);
			return false;
		}
		return true;
	})

})( jQuery );

(function ($) {
  $(function () {
    $('.xtra-color-picker').wpColorPicker();
  });
}(jQuery));

(function ($) {
	//var xtra_stickyOffset = $('.xtra_sticky').offset().top;
	//var xtra_stickyOffset = 80;
	$(window).scroll(function() {
		var xtra_stickyOffset = $('#wpadminbar').height()+$('#main_title').height()+10+10;
		if (!$('.xtra_sticky').length) return;
		var sticky = $('.xtra_sticky'),
		scroll = $(window).scrollTop();

		if (scroll >= xtra_stickyOffset) {
			sticky.addClass('xtra_fixed');
			sticky.css('top', $('#wpbody').offset().top);
			sticky.css('background', $('body').css('background'));
			sticky.css('z-index', '1');
			if ( $('.xtra_left').hasClass('vertical-tabs') && $(window).innerWidth() >= 560 ) {
				/* sticky on vertical tabs */
				var pwid = $('.xtra_left').width();
				sticky.css('width', pwid*0.109);
				var phig = $(window).innerHeight()-$('#wpadminbar').height()-10;
				sticky.css('height', phig);
				$('.xtra-tab-wrapper').css('margin-left', pwid*0.109);
				if ( $(window).innerWidth() <= 960 ) $('.xtra_right').css('margin-left', pwid*0.109).css('width', 'auto');
			}
			else {
				/* sticky on horizontal tabs */
				sticky.css('width', $('.xtra_left').css('width'));
				//$('#xtra_sticky_spacer').height(25);
				$('#xtra_sticky_spacer').height(sticky.outerHeight(true));
				$('#xtra_sticky_spacer').css('background', $('.xtra-tab-wrapper').css('background'));
			}
		}
		else {
			/* reset sticky */
			sticky.removeAttr("style");
			sticky.removeClass('xtra_fixed');
			$('.xtra-tab-wrapper').css('margin-left', 0);
			$('.xtra_right').removeAttr("style");
			$('#xtra_sticky_spacer').height(0);
		}
	});
})(jQuery);