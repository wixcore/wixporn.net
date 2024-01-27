
var ds_events = {}
var ds_audio = new Audio(); 
var ds_playlist = {}
var ds_smiles = null; 
var ds_loading = true; 
var is_scrolling = false; 

var eventAudio = new Audio(); 
function playAudioMessage(name) {
	console.log(name); 
	if (!eventAudio.src)
		eventAudio.src = theme_uri + '/audios/' + name + '.mp3'; 
	eventAudio.play(); 
}

function lastMessageScroll(behavior) {
	var element = document.querySelector('.mail_Scroll-helper');
	if (!element) return ; 
	
	element.scrollIntoView({
		behavior: behavior || 'auto',
		block: 'end',
	});
}

function appendMessage(msg) {
	$('#ds-messages-mail').append(msg); 
	lastMessageScroll('smooth');

	setTimeout(function() {
		lastMessageScroll('smooth');
	}, 200); 
}

lastMessageScroll(); 
var intervalScroll = setInterval(function() {
	lastMessageScroll(); 
}, 50); 

window.addEventListener("load", function() {
	clearInterval(intervalScroll); 
})

window.addEventListener('resize', function() {
	lastMessageScroll(); 
});

function swiperEvent(type, callback) 
{
	var start = {};
	window.addEventListener('touchstart', function(e) {
		start = e.changedTouches[0]; 
	});

	window.addEventListener('touchend', function(e) {
		if (is_scrolling !== false) 
			return ;
		
		let end = e.changedTouches[0]; 
		let diffX = Math.abs(start.pageX - end.pageX); 
		let diffY = Math.abs(start.pageY - end.pageY); 

		if (diffX >= 120 && diffY <= 30) {
			if (type == 'left' && start.pageX < end.pageX) {
				callback(); 
			} else if (type == 'right' && start.pageX > end.pageX) {
				callback(); 
			}
		}
	});	
}


jQuery(function($) {

	function showErrors(errors) {
		$.each(errors, function(index, message) {
			$('#ds_alerts').append($('<div/>', {
				class: 'alert alert-error', 
				append: message, 
				onload: function() {
					var alert = this; 
					$(alert).css('display', 'none').fadeIn(500).delay(2000).fadeOut(500); 
				}
			})); 
		}); 
	}

	var user_logged_in = $('body').hasClass('logged-in'); 

	/**
	* Инициализация Ajax переходов
	*/ 
	var $ajaxpage = $(document).ajaxpage(); 


	/** Подгрузка плейлиста **/

	$events.setEvents('playlist', {
		data: function() {
			if (!user_logged_in || ds_playlist.list != undefined) {
				$events.delete('playlist'); 
				return false; 
			}

			console.log('Events: Загрузка плейлиста..'); 
			return true; 
		}, 
		success: function(event) {
			ds_playlist = event;
			if (event.list) {
				$('.music_playlist').html(''); 
				$.each(event.list, function(indx, elem) {
					var attr = {
						'data-id' : elem.id, 
						'data-title' : elem.title, 
						'data-src' : elem.src, 
						'data-hash' : elem.hash, 
						'data-uniquie' : elem.uniquie, 
						'data-thumbnail' : elem.thumbnail, 
					}

					if (indx == 0 && !ds_audio.src) {
						$('.dpl[data-god="1"]').attr(attr).show().find('.dpl-title').text(elem.title); 
					}

					var currentPlay = getCookie('playerData'); 

					if (currentPlay) {
						var data = JSON.parse(currentPlay); 
						if (data.hash == elem.hash) {
							$('.dpl[data-god="1"]').attr(attr).show().find('.dpl-title').text(elem.title); 
						}
					}

					$('.music_playlist').append($('<div/>', $.extend({
						'class' : 'dpl', 
						append: [$('<div/>', {
							class: 'dpl-toggle', 
						}), $('<a/>', {
							class: 'dpl-title', 
							append: elem.title, 
							href: elem.url, 
						}), ]
					}, attr))); 
				}); 
			} 
			$events.delete('playlist'); 
		}
	}); 

	/** Подгрузка комментариев **/

	$events.setEvents('comments', {
		data: function() {
			var ds_comments = []; 
			$(document).find('[data-comments]').each(function(indx, elem) {
				if ($(elem).data('paged') == 1) {
					ds_comments.push({
						last_id: $(elem).attr('data-last'),  
						first_id: $(elem).attr('data-first'),  
						hash: $(elem).attr('data-comments'), 
					});

					window.ds_hash_prints = $(elem).attr('data-comments'); 
				}
			}); 

			if (ds_comments.length > 0) {
				//console.log('Events: Проверяем комментарии..'); 
				return ds_comments; 
			}

			return false; 
		}, 
		success: function(event) {
			$.each(event, function(i, data) {
				var commentsList = $(data.container);
				var first_id = commentsList.data('first'); 

				if (first_id == '-1') {
					commentsList.find('.comments-empty').remove(); 
				}

				var countPrints = 0; 
				if (data.prints) {
					$.each(data.prints, function(p, t) {
						if (t.print == 1) {
							countPrints++; 
						}

						var us = $('.comments-prints').find('[data-user="' + t.user_id + '"]'); 
						if (us.length == 0) {
							$('.comments-prints').prepend($('<span/>', {
								'data-user' : t.user_id, 
								'data-print' : t.print, 
								append: t.nick, 
							})); 
						} else {
							us.attr('data-print', t.print); 
						}
					}); 
				}

				$('.comments-prints').attr('data-prints', countPrints); 
			
				var readers = $('.comments-readers[data-hash="' + data.hash + '"]'); 
					readers.html(data.prints.length); 

				if (data.messages !== undefined) {
					commentsList.attr('data-last', data.last_id); 

					$('[data-comments-count="' + data.hash + '"]').text(data.count); 

					$.each(data.messages, function(mid, message) {
						if ($('.comment-' + data.last_id).length == 0) {
							commentsList.prepend(message.content); 
						}
					}); 
				}
			}); 
		}
	}); 

	/** Обновление контактов **/ 

	$events.setEvents('contacts', {
		data: function() {
			if (!user_logged_in) {
				$events.delete('contacts'); 
				return false; 
			}

			var contacts = []; 
			$(document).find('.mail-contacts .post').each(function(indx, elem) {
				contacts.push({
					user_id: $(elem).data('user'), 
					last_id: $(elem).data('last_id'),  
				}); 
			}); 

			if (contacts.length == 0) {
				return false; 
			}

			return contacts; 
		}, 
		success: function(event) {
			$.each(event, function(indx, elem) {
				console.log('read: ' + elem.last_msg_read + ' on: ' + elem.is_contact_mail + ' print: ' + elem.is_contact_print); 
				
				var msg = $('.post[data-user="' + indx + '"] .ds-contact-text');
				if (msg.hasClass('no-read') && elem.last_msg_read == '1') {
					msg.removeClass('no-read'); 
				}

				$('.post-contact[data-user="' + indx + '"]').find('.mc-print').attr('data-typing', elem.is_contact_print); 
			}); 
		}
	}); 

	/** Подгрузка почты **/

	$events.setEvents('mail', {
		data: function() {
			if (!user_logged_in) {
				$events.delete('mail'); 
				return false; 
			}

			var ds_mail = false; 
			$(document).find('.ds-messages').each(function(indx, elem) {
				ds_mail = {
					last_id: $(elem).attr('data-last'),  
					first_id: $(elem).attr('data-first'),  
					contact_id: $(elem).attr('data-contact'),  
					toread: $(elem).attr('data-toread') || 0,  
					previus: 0,
					last_active: (Math.abs(Date.now() - ds_user.update) / 1000),
				}

				var offset = $('.mail_Pagination-helper').offset(); 
				
				if (offset.top >= 0 && window.ds_loading === true) {
					$('.wrap-messages').scrollTop(1); 

					window.ds_loading = false; 
					ds_mail.previus = 1;
				}
			}); 

			return ds_mail; 
		}, 
		success: function(event) {
			console.log('on: ' + event.is_contact_mail + ' print: ' + event.is_contact_print); 

			$('.mc-print').attr('data-typing', event.is_contact_print); 

			$(document).find('.ds-messages').each(function(indx, elem) {
				var first_id = $(elem).attr('data-first');
				var last_id = $(elem).attr('data-first');

				$(elem).attr('data-last', event.last_id); 
				$(elem).attr('data-first', event.first_id); 

				if (event.toread == -1) {
					$(elem).attr('data-toread', 0); 
					$('.ds-msg-ank.no-read').removeClass('no-read').addClass('read'); 
				}
				
				if (event.prev) {
					$.each(event.prev, function(mid, message) {
						$(elem).prepend(message).attr('data-toread', 1); 
					}); 

					var el = document.querySelector('.mail_Scroll-helper');
					window.scrollTo(0, (el.scrollHeight + el.scrollTop));					
				}

				if (event.messages) {
					$.each(event.messages, function(mid, message) {
						$(elem).append(message).attr('data-toread', 1); 
						lastMessageScroll('smooth'); 
						setTimeout(function() {
							lastMessageScroll('smooth'); 
						}, 300); 
					}); 					
				}

				window.ds_loading = true; 
			}); 

			if (event.unread == 0) {
				$('.ds-msg-user.no-read').removeClass('no-read').addClass('read'); 
			}
		}
	}); 

	/** Обновление счетчиков **/ 
	$events.setEvents('counters', {
		success: function(event) {
			$.each(event, function(index, item) {
			    $(document).find('[data-count][data-type="' + index + '"]').each(function(i, el) {
			    	var count = Math.ceil($(el).attr('data-count')); 
			    	$('.mobile-sidebar-toggle').attr('data-' + index, count);

			    	if (count != item.count) {
				    	if (item.count > count) {
				    		if (index == 'mail') {
				    			playAudioMessage('message'); 
				    		}
			    			
			    			// Обновляем список контактов
			    			if (index == 'mail' && $('body').hasClass('mail-contacts')) {
			    				$ajaxpage.AjaxPageLoad(window.location.href, 10); 
			    			}
				    	}
			    		
			    		$(el).attr('data-count', item.count).text(item.count); 
			    	}
			    }); 
			}); 
		}
	}); 

	/** Обновление индикаторов онлайна **/ 
	$events.setEvents('users_is_online', {
		data: function() {
			if (ds_user.active === false) {
				return false; 
			}

			var users = []; 
			$(document).find('.wrapper-avatar').each(function(indx, elem) {
				users.push($(elem).data('user')); 
			}); 
			return users; 
		}, 
		success: function(event) {
			$.each(event, function(index, item) {
			    $('.wrapper-avatar[data-user="' + item.user_id + '"]').attr('data-active', item.active).attr('data-browser', item.browser); 
			}); 
		}
	}); 


	$(document).on('click', '.comment-reply', function() {
		var comments = $(this).closest('.ds-comments'); 
		var hash = comments.data('comments'); 
		var textarea = $('.ds-editor-textarea[data-hash="' + hash + '"]'); 
		var uid = $(this).data('id'); 
		var nick = $(this).data('nick'); 

		$('form[data-hash="' + hash + '"]').find('input[name="comments_reply"]').val(uid); 
		textarea.val(nick + ', ' + textarea.val()); 
		textarea.focus();
	}); 

	$(document).on('submit', '.comments-form', function() {
		var attachments = $(this).find('.attachments [name="attachments[]"]').length; 
		var msg = $(this).find('textarea[name="msg"]').val(); 

		if (!user_logged_in || (!msg && !attachments)) {
			return false; 
		}

		var data = $(this).serialize();
		var action = $(this).attr('action');

		$(this).find('.attachments').html(''); 
		$(this).find('textarea[name="msg"]').removeAttr('style').val(''); 
		$(this).find('input[name="comments_reply"]').val(0); 

		$.ajax({
			type: "POST",
			url: action,
			data: data,
			dataType: 'json',
			success: function(data) {
				if (!data.errors) {
					var commentsList = $(data.container);
						commentsList.attr('data-last', data.id); 
						
					var first_id = commentsList.data('first'); 

					if (first_id == '-1') {
						commentsList.find('.comments-empty').remove(); 
					}

					$('[data-comments-count="' + data.hash + '"]').text(data.count); 

					if (data.append == 'last') {
						$(data.container).append(data.msg); 
						lastMessageScroll('smooth'); 			
					} else {
						$(data.container).prepend(data.msg); 
					}
				} else {
					showErrors(data.errors); 
				}
			}
		});

		return false; 
	}); 

	$(document).mouseup(function(e) {
		var p = $('.header-player'); 
		if (!p.is(e.target) && p.has(e.target).length === 0 && $(e.target).closest('.header-music').length === 0) { 
			$('#header-player-toggle').prop('checked', false); 
		}
	});

	$(document).on('click', '[data-toggle]', function(e) {
		var uid = $(this).attr('data-toggle'); 
		var current = $(this).closest('.ds-editor').attr('data-panel');

		if (current == uid) {
			uid = ''; 
		}

		$(this).closest('.ds-editor').attr('data-panel', uid);
	});

	$(document).on('click', '.mobile-sidebar-toggle', function(e) {
		var body = $('body'); 
		if (body.hasClass('sidebar-active')) {
			body.removeClass('sidebar-active')
		} else {
			body.addClass('sidebar-active')
		}
	});

	swiperEvent('left', function() {
		var body = $('body'); 

		if (body.hasClass('mobile-player-active') && body.hasClass('logged-in')) {
			body.removeClass('mobile-player-active'); 
		} else if (!body.hasClass('sidebar-active')) {
			body.addClass('sidebar-active'); 
		}
	}); 

	swiperEvent('right', function() {
		var body = $('body'); 

		if (body.hasClass('sidebar-active')) {
			body.removeClass('sidebar-active')
		} else if (!body.hasClass('sidebar-active') && body.hasClass('logged-in')) {
			body.addClass('mobile-player-active'); 
		} 
	}); 

	$(document).on('click', '.more-feed', function() {
		var pages = Math.ceil($(this).attr('data-pages'));
		var paged = Math.ceil($(this).attr('data-paged')) + 1;
		var p_str = Math.ceil($(this).attr('data-p_str'));
		var selector = $(this).attr('data-container');

		if (pages < paged) {
			return ;
		}

		$.ajax({
			type: "POST",
			url: '/ds-ajax/',
			data: {
				action: 'feeds', 
				paged: paged, 
				p_str: p_str, 
			},
			success: function(html) {
				inProgress = false; 
				$(document).find('.more-feed').attr('data-paged', paged);
				$(document).find(selector).append(html);

				if (pages == paged) {
					$('.more-feed').hide(); 
				}
			}
		});
	});

	$('[data-scroll]').scroll(function() {
	    clearTimeout(is_scrolling); 
	    is_scrolling = setTimeout(function() {
	    	is_scrolling = false; 
	    }, 300); 
	});

	var inProgress = false; 
	$(document).on('scroll', function(e) {
		$(document).find('[data-ajaxtype="scroll"]').each(function(indx, elem) {
	    	if ($(window).scrollTop() + $(window).height() >= ($(document).height() - 300) && !inProgress) {
	    		console.log('Loading..'); 
	    		inProgress = true;  
	    		$(elem).click();  
	    	}
		});
	});
}); 