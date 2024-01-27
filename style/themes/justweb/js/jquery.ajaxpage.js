
(function( $ ) {
	$.fn.ajaxpage = function() {
		var history = window.history; 
		var $ajaxpage = this; 
		var xhr = {}

		window.addEventListener('popstate', function (e) {
			var id = e.state ? e.state.count : localStorage.id - 1, dir = id - localStorage.id;
			var url = !e.state ? window.location.href : e.state.url; 

			if (url.search(/\#([A-z0-9]{32})$/i) !== -1 || url.search(/\#$/i) !== -1) {
				if (window.location.href.replace(/\#([A-z0-9]{32})$/, '') === url.replace(/\#([A-z0-9]{32})$/, '')) {
					return true;
				}
			}

			if (url && url.search(/\#/i) === 0) {
				return false;
			}
			
			$ajaxpage.AjaxPageLoad(url, true); 
		}, false);

		$(document).on('click', 'a[href],[data-href]', function(e) {

			if (e.ctrlKey === true) {
				return true; 
			}

			var url = $(this).attr('href') || $(this).attr('data-href'); 
			var target = $(this).attr('target') || false; 
			var media = $(this).attr('data-media') || false; 

			if (media == 'image' || media == 'video') {
				var jwModal = $(this).jwModal(); 

				if (jwModal === true) {
					return false; 
				}
			}

			if (target == '_blank') {
				return true; 
			}

			if (url && url !== '#' && url.search(/\#/i) === 0) {
				var tagScroll = $(url).offset() || false; 

				if (tagScroll) {
					$("html, body").animate({
				        scrollTop: tagScroll.top - 100
				    }, 700);  				
				}
				
				return false; 
			}

			var is_bad_link = false; 
			var domain = window.location.host; 

			if (url == '/' || url == '') { url = window.origin; }

			if (url && url.indexOf('://') !== -1 && url.indexOf(domain) === -1) {
				window.open(url);
				return false;
			}

			if (url == 'undefined' || !url || url.indexOf('javascript:') !== -1 || url == '#' || url.indexOf('/exit.php') !== -1) {
				is_bad_link = true; 
			}

			if (is_bad_link === false) { 
				$ajaxpage.AjaxPageLoad(url); 
				return false;    
			}
		}); 

		this.AjaxPageLoad = function(url, id) {
			if (url && url.search(/\#/i) === 0) {
				return false;
			}

			if (typeof NProgress == 'object' && id !== 10)
				NProgress.start();

			xhr = $.ajax({
				url: url,
				success: function(html, xhrStatus) {
					if (html.indexOf('ajax-meta') === -1) {
						window.open(url); 
					} else {
						$('#page_content').html(html);

						if (id == 301) {
							history.pushState(null, null, url);
						} else if (id === undefined && id !== 10) {
							localStorage.id = (parseInt(localStorage.id) || 1) + 1;
							history.pushState({url: url, title: document.title, count: localStorage.id}, document.title, url);   
						}

						$ajaxpage.RefreshElementsAjax(); 
					}
					$ajaxpage.xhrAbort(); 
				}, 
				error: function(jqXHR, textStatus, errorThrown) {
					if (textStatus == 'error') {
						window.location = url; 
						$ajaxpage.xhrAbort();
					}
				}
			});
		}

		this.xhrAbort = function() {
			if (xhr !== false) {
				xhr.abort(); 
				xhr = false; 

				if (typeof NProgress == 'object')
					NProgress.done();
			}
		}

		this.RefreshElementsAjax = function() {
			var meta = $('#ajax-meta'); 
			var redirect = meta.attr('data-redirect') || false; 
			var message = meta.attr('data-message') || false; 

			if (message !== false) {
				$('#ds_alerts').append($('<div/>', {
					class: 'alert alert-success', 
					append: message, 
					onload: function() {
						var alert = this; 
						$(alert).css('display', 'none').fadeIn(500).delay(2000).fadeOut(500); 
					}
				})); 
			}

			if (redirect !== false) {
				setTimeout(function() {
					$ajaxpage.AjaxPageLoad(redirect, 301);					
				}, 100); 

				return false; 
			}

			var title = meta.attr('data-title') || ''; 
			var bodyClass = meta.attr('data-body') || ''; 

			if (bodyClass.search('ds-page-mail') !== -1) { 
				setTimeout(function() {
					lastMessageScroll('auto'); 
				}, 200); 

			} else {
			    $("html, body").animate({
			        scrollTop: 0
			    }, 500);    
			}

			$('body').attr('class', bodyClass); 
			document.title = title;

			$('#ajax-stylesheet > span').each(function(key, element) {
				var style_id = $(element).attr('data-id'); 
				var style_href = $(element).attr('data-href');
				var elementStyle = $('link#' + style_id); 

				if (elementStyle.length == 0) {
					$('head').prepend('<link rel="stylesheet" id="' + style_id + '"  href="' + style_href + '"  media="all" />'); 
				}
			}); 
			
			$('#ajax-stylesheet').remove();

			$('#ajax-style > span').each(function(key, element) {
				var style_id = $(element).attr('data-id'); 
				var elementStyle = $('style#' + style_id); 

				if (elementStyle.length == 0) {
					$('head').append('<style type="text/css" id="' + style_id + '" media="all">' + $(element).html() + '</style>'); 
				}
			});  

			$('#ajax-style').remove(); 


			$('[data-scroll="true"]').bind('scroll', function() {
			    clearTimeout(is_scrolling); 
			    is_scrolling = setTimeout(function() {
			    	is_scrolling = false; 
			    }, 300); 
			}); 
		}

		return this; 
	};
})(jQuery);