var jwm = new Map(); 
var jw_modal = $(document).find('.jw-modal'); 

jQuery.fn.jwModal = function() {
	var $this = this; 

	if (!jw_modal.length) {
		jw_modal = $('<div/>', {
			class: 'jw-modal', 
			append: [
				$('<div/>', {
					class: 'jw-modal-wrapper-close',
					click: function() {
						$(jw_modal).hide(); 
						$this.clean(); 
					}
				}), 
				$('<div/>', {
					class: 'jw-modal-container', 
					append: [
						$('<span/>', {
							class: 'jw-modal-close',
							click: function() {
								$(jw_modal).hide(); 
								$this.clean(); 
							}, 
							html: '&times;', 
						}), 
						$('<div/>', {
							class: 'jw-modal-content', 
						}), 
						$('<div/>', {
							class: 'jw-modal-info', 
						}), 
					], 
				}), 
			]
		}); 

		$('body').append(jw_modal); 		
	}

	this.clean = function() {
		var args = {
			content: '', 
		}

		$this.set(args, {}); 
	}

	this.set = function(data, args) {
		jw_modal.find('.jw-modal-content').html(data.content); 
		jw_modal.find('.jw-modal-info').html($('<a/>', {
			href: data.url, 
			text: data.title, 
			click: function() {
				$(jw_modal).hide();  
				$this.clean(); 
			}
		})); 

		if (args.media == 'video') {
			var player = jw_modal.find('video'); 
			if (player) {
				player.play(); 
			}
		}
	}

	this.open = function(args) {
		if (args.type == 'ds_files') {
			if (!jwm.has('ds-' + args.file_id + '-file')) {
				$.ajax({
					type: "POST",
					url: '/ds-ajax/',
					data: {
						action: 'jw_modal_file', 
						file_id: args.file_id, 
					},
					dataType: 'JSON', 
					success: function(e) {
						jwm.set('ds-' + args.file_id + '-file', e); 
						$this.set(e, args); 
					}
				});
			} else {
				var e = jwm.get('ds-' + args.file_id + '-file'); 
				$this.set(e, args); 
			}
		}

		jw_modal.show(); 
	}

	var media = $(this).attr('data-media') || false; 

	if (media) {
		var file_id = $(this).attr('data-file') || false; 

		this.open({
			type: 'ds_files',
			media: media,
			file_id: file_id,  
		}); 
	}

	return true; 
}
