/**
* Комментарии DCMS-Social v3
* Версия: 1
*/ 


jQuery(function($) {

	function get_template_attachment(file) {
		return $('<div/>', {
			style: 'background-image: url(' + file.thumbnail + ')', 
			class: 'attachments-item', 
			append: [
				$('<input/>', {
					type: 'hidden', 
					value: file.file_id, 
					name: 'attachments[]', 
				}), 
				$('<i/>', {
					class: file.icon, 
				}), 
				$('<span/>', {
					class: 'title', 
					text: file.title, 
				}), 
				$('<span/>', {
					class: 'remove', 
					html: '&times;', 
				}), 
			]
		});
	}

	$(document).on('click', '.attachments-item .remove', function() {
		var parent = $(this).closest('.attachments-item'); 
			parent.remove(); 
	});

	$(document).on('click', '.load-files', function() {
		var hash = $(this).data('hash') || ''; 
		var type = $(this).data('type') || false; 
		var term = $(this).data('term') || false; 
		var title = $(this).text(); 

		$media = new MediaManager(); 
		$media.open({
			selector: '.wrap-choose-manager[data-hash="' + hash + '"]', 
			title: title, 
			type: type, 
			term: term, 
			onSelected: function(obj) {
				var attachments = $('.attachments[data-hash="' + hash + '"]'); 
				obj.forEach(function(file, index) {				
					if (attachments.find('.attachments-item').length >= 12) {
						return ; 
					}
					let attach = get_template_attachment(file); 
					attachments.append(attach); 
				}); 
			}
		}); 
	});

	$(document).on('focus', '.ds-comment-form-textarea .ds-editor-textarea', function(e) {
		var hash = $(this).data('hash'); 
		window.ds_hash_prints = hash; 
	}); 

	$(document).on('paste', '.ds-comment-form-textarea .ds-editor-textarea', function(e) {
		var hash = $(this).data('hash'); 
		window.ds_hash_prints = hash; 

		$.each(e.originalEvent.clipboardData.files, function(k, file) { 
			var uid = $media.addFile(file);
			
			var attachments = $('.attachments[data-hash="' + hash + '"]'); 
			var comment_object = $('form[data-hash="' + hash + '"]').find('input[name="comments_object"]').val(); 
			var comment_object_id = $('form[data-hash="' + hash + '"]').find('input[name="comments_object_id"]').val(); 

			if (attachments.find('.attachments-item').length >= 12) {
				return ; 
			}

			$('.attachments[data-hash="' + hash + '"]').append($('<div/>', {
				class: 'attachments-item upl-progress', 
				append: [
					$('<i/>', {
						class: 'fa fa-refresh fa-spin', 
					}), 
					$('<span/>', {
						class: 'title', 
						text: file.name, 
					}), 
					$('<span/>', {
						class: 'remove', 
						text: '×', 
					}), 
				], 
				onload: function() {
					var tmp = $(this); 

					$media.upload({
						file: $media.getFile(uid), 
						fileType: 'files', 
						url: '/ds-ajax/?action=ds_files_upload', 
						data: {
							object: comment_object, 
							object_id: comment_object_id, 
						}, 
						progress: function(percent, e) {
							console.log(percent); 
						}, 
						success: function(data) {
							var json = JSON.parse(data.currentTarget.responseText); 
							var attach = get_template_attachment(json.file); 
							tmp.replaceWith(attach); 
						}, 
						error: function(e) {
							console.log(e);
						}
					}); 
				}
			})); 
		});
	}); 

	$(document).on('keyup', '.ds-comment-form-textarea .ds-editor-textarea', function(e) {
		window.ds_user_prints = Date.now(); 

		var id = $(this).attr('id'); 
		
		var msg = $(this).val(); 
			msg = msg.replace(new RegExp('\r?\n','g'), '<br>');

		var width = $(this).outerWidth(); 
		var tH = $(this).outerHeight(); 

		var h = $('.ds-editor-helper[data-hash="' + id + '"]'); 
			h.html(msg).css('width', width + 'px'); 

		var height = h.height(); 

		if (e.shiftKey && e.which === 13) { 	
			height += 19;
			if (e.keyCode == 13) {
				height += 16;
			}
		} else if (e.which === 13 && typeof window.orientation === 'undefined') {
			$(this).closest('form').submit(); 
			return ;
		}

		if (height >= 32 || tH > height)
			$(this).css('height', height + 'px'); 
	}); 

	$("form.form-ajax").on("submit", function() {
	    var formData = new FormData(this);

	    $.ajax({
			type: "POST",
			url: $(this).attr('action'),
			data:  formData,
			processData: false,
			contentType: false,
			success: function(data) {
				$('#log').html(data); 
			},
			error: function() {
				console.log('Error send'); 
			}
	     });

	    return false;
	});
}); 
