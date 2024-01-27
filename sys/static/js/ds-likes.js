jQuery(function() {
	$(document).on('click', '.ds-like-user', function(e) {
		var uid = $(this).data('uid'); 
		var type = $(this).data('type'); 
		var elem = $(this); 
		var elemCounter = $(this).find('.counter'); 

		var counter = Math.ceil(elemCounter.text()); 

		if (elem.hasClass('ds-i-liked')) {
			elem.removeClass('ds-i-liked'); 
			counter -= 1; 
		} else {
			elem.addClass('ds-i-liked'); 
			counter += 1;
		}

		elemCounter.text(counter); 

        $.ajax('/ds-ajax/', {
            data: {
            	action: 'ds-like',  
            	uid: uid, 
            	type: type, 
            }, 
            type: "POST",
            dataType: 'json', 
            success: function(resp) {
                console.log(resp); 
                if (resp.result == 'add') {
                	elem.addClass('ds-i-liked'); 
                } else {
                	elem.removeClass('ds-i-liked'); 
                }
                elemCounter.text(resp.count); 
            }, 
            error: function(xhr) {
            	console.log('error: like'); 
            }
        }); 
	}); 
});