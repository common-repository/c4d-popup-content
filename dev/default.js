(function($){
	"use strict";
	$(document).ready(function(){
		$('.c4d-wppu__dont-show').on('click', function(event){
			event.preventDefault();
			$(this).toggleClass('active');
			var id = $(this).parents('.c4d-wppu__site').attr('data-id'),
			name = 'c4d-wppu-' + id;
			if ($(this).hasClass('active')) {
				$.cookie(name, 1, {expires: 30, path:'/'});	
			} else {
				$.removeCookie(name, { path: '/' });
			}
			return false;
		});

		$(".c4d-wppu__no-thank").on('click', function(){
			$.fancybox.close();
		});
		$('.c4d-wppu__wrapper').each(function(){
			var self = this,
			openLink = $(self).find('.c4d-wppu__open-link');
			openLink.fancybox({
				'type': 'inline',
				'transitionIn'	:	'elastic',
				'transitionOut'	:	'elastic',
				'speedIn'		:	600, 
				'speedOut'		:	200, 
				'overlayShow'	:	false,
				'width'           : '100%',
	        	'height'          : '100%',
	        	'scrolling'		: 'yes',
	        	'wrapCSS' : 'c4d-wppu'
			});	
			
			// auto open popup
			var allow = $.cookie('c4d-wppu-' + $(self).find('.c4d-wppu__site').attr('data-id')),
			delayTime = $(self).find('.c4d-wppu__site').attr('data-delay-time');
			if (typeof allow == 'undefined') {
				setTimeout(function(){
					$(openLink).trigger('click');
				}, delayTime);
			}

			$(self).find('form').on('submit', function(){
				$.fancybox.close();
			});
		});
	});
})(jQuery);