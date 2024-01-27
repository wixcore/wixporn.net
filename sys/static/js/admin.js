$(function() {
	$('.menu-toggle').click(function() {
		var hasActive = $(this).parent('.submenu-exists').hasClass('active'); 
		if (hasActive) {
			$('.submenu-exists.active').removeClass('active'); 
		} else {
			$('.submenu-exists.active').removeClass('active'); 
			$(this).parent('.submenu-exists').addClass('active'); 
		}
	}); 

	$('.link-toggle-menu').click(function() {
		var hasActive = $('body').hasClass('left-menu-active'); 
		if (hasActive) {
			$('body').removeClass('left-menu-active');
		} else {
			$('body').addClass('left-menu-active');
		}
	}); 
}); 