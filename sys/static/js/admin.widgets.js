jQuery(function($) {

	function updateWidgetsData() 
	{
		var data = []; 
		$('.widgets-area .widget-ui').each(function(index, elem) {
			var widget_id = $(elem).data('widget_id'); 
			data.push({
				id: $(elem).data('widget'), 
				widget_id: widget_id, 
			}); 
		}); 

		var widgets = JSON.stringify(data); 

		$('[name="widgets_area_content"]').val(widgets); 

	    $.ajax({
	        url: '/ds-ajax/',  
	        type: "POST", 
	        dataType: "json", 
	        data: {
	        	action: 'widgets_area_save', 
	        	area_id: $('.widgets-area').data('area'), 
	        	widgets: widgets, 
	        }, 
	        success: function(json) {
	        	console.log(json);
	    	},
	    	error: function(response) { 
	            console.log('Ошибка. Данные не отправлены.');
	    	}
	 	});
	}

	function sortableWidgetTo(e, to) {
		var widget = $(e).closest('.widget-ui'); 

		if (to === -1) {
			widget.insertBefore(widget.prev());
		} else {
			widget.insertAfter(widget.next());
		}
		updateWidgetsData(); 
	}

	$(document).on('click', '.widget-area-add', function() {
		var widgetsList = $('.widgets-list'); 

		if (widgetsList.hasClass('active')) {
			widgetsList.removeClass('active');
			$('.widget-area-add-close').remove(); 
		} else {
			widgetsList.addClass('active');
			$('body').append('<div class="widget-area-add-close widget-area-add"></div>'); 
		}
	}); 

	$(document).on('click', '.widget-add', function() {
		var widget = $(this).closest('.widget-ui'); 

		var widgetWith = $('<div/>', {
			class: 'widget-ui widget-ui-loaded', 
			'data-widget': widget.data('widget'), 
			'data-loaded': 0, 
			append: [
				widget.find('.ui-move-handle').clone(), 
				$('<div/>', {
					class: 'widget-content', 
					append: [
						$('<div/>', {
							class: 'widget-title',
							text: widget.find('.widget-title').text(),  
						}), 
						$('<div/>', {
							class: 'widget-links',
							append: [
								$('<a/>', {
									text: 'Редактировать', 
									class: 'widget-ui-edit',
								}), 
								$('<span/>', {
									text: ' | ', 
								}), 
								$('<a/>', {
									text: 'Удалить', 
									class: 'ds-link-delete widget-ui-remove', 
								}), 
							]
						}), 
						$('<div/>', {
							class: 'widget-editor',
							append: $('<span/>', {
								class: 'button-process', 
							}),  
						}), 
					], 
				}), 
				$('<div/>', {
					class: 'widget-action', 
					append: [
						$('<a/>', {
							class: 'widget-up', 
							append: $('<i/>', {
								class: 'fa fa-chevron-up', 
							})
						}), 
						$('<a/>', {
							class: 'widget-down', 
							append: $('<i/>', {
								class: 'fa fa-chevron-down', 
							})
						}), 
					]
				})
			], 
		});

		$('.widgets-area').append(widgetWith); 

        $.ajax(ajax_url, {
            data: { 
            	action: 'widget_edit_form', 
            	widget_name: widget.data('widget'), 
            	widget_area: $(widgetWith).closest('.widgets-area').data('area'), 
            	widget_id: 0, 
            }, 
        	method: 'POST', 
            success: function(resp) {
                var widget_id = $(resp).find('[name="widget_id"]').val();
                $(widgetWith).find('.widget-editor').addClass('active').html(resp);
                $(widgetWith).attr('data-loaded', 1); 
                $(widgetWith).attr('data-widget_id', widget_id); 
                updateWidgetsData(); 
            }
        }); 

		$('.widgets-list').removeClass('active');
		$('.widget-area-add-close').remove(); 
	}); 

	$(document).on('click', '.widget-up', function() {
		sortableWidgetTo(this, -1);
	}); 

	$(document).on('click', '.widget-down', function() {
		sortableWidgetTo(this, 1);
	}); 

	$(document).on('click', '.widget-ui-remove', function() {
		$(this).closest('.widget-ui').remove(); 
		updateWidgetsData(); 
	}); 

	$(document).on('click', '.widget-ui-edit', function() {
		var widget = $(this).closest('.widget-ui'); 
		var editor = widget.find('.widget-editor'); 
		var widget_area = widget.closest('.widgets-area').data('area') || 0; 
		var widget_id = widget.data('widget_id') || 0; 
		var widget_name = widget.data('widget') || false; 

		if (editor.hasClass('active')) {
			editor.removeClass('active'); 
		} else {
			$('.widget-editor').removeClass('active'); 
			editor.addClass('active');
		}

		var loaded = widget.data('loaded') || false; 

		if (loaded === false) {
	        $.ajax(ajax_url, {
	            data: { 
	            	action: 'widget_edit_form', 
	            	widget_name: widget_name, 
	            	widget_area: widget_area, 
	            	widget_id: widget_id, 
	            }, 
	        	method: 'POST', 
	            success: function(resp) {
	                editor.addClass('active').html(resp);
	                widget.attr('data-loaded', 1); 
	                updateWidgetsData(); 
	            }
	        }); 
		}
	}); 

	$(document).on('submit', '.widget_form', function() {
		event.preventDefault();

		var submit = $(this).find('[type="submit"]'); 

		var alert = $(this).find('.widget-alert'); 
			alert.addClass('text-process show').text('Сохранение..').removeClass('hide text-success text-error'); 

	    $.ajax({
	        url: '/ds-ajax/?action=widget_edit_save', 
	        type: "POST", 
	        dataType: "json", 
	        data: $(this).serialize(), 
	        success: function(json) {
	        	setTimeout(function() {
					alert.text('Успешно').addClass('text-success hide').removeClass('show text-process'); 
	        	}, 1000); 
	    	},
	    	error: function(response) { 
				alert.text('Ошибка. Данные не отправлены.').addClass('text-error').removeClass('text-process'); 
	    	}
	 	});

		return false; 
	}); 

	$('.sortable-ui').sortable({ 
		revert: true, 
		placeholder: "ui-placeholder", 
		handle: ".ui-move-handle",
		update: function(event, ui) {
			var widget_name = $(ui.item).data('widget');
			var widget_id = $(ui.item).data('widget_id') || 0;
			var loaded = $(ui.item).data('loaded');

			if (loaded !== -1) {
				updateWidgetsData(); 
				return ;
			}

			var widgetWith = $('<div/>', {
				class: 'widget-ui widget-ui-loaded', 
				'data-widget': widget_name, 
				'data-loaded': 0, 
				append: [
					$(ui.item).find('.ui-move-handle'), 
					$('<div/>', {
						class: 'widget-content', 
						append: [
							$('<div/>', {
								class: 'widget-title',
								text: $(ui.item).find('.widget-title').text(),  
							}), 
							$('<div/>', {
								class: 'widget-links',
								append: [
									$('<a/>', {
										text: 'Редактировать', 
										class: 'widget-ui-edit',
									}), 
									$('<span/>', {
										text: ' | ', 
									}), 
									$('<a/>', {
										text: 'Удалить', 
										class: 'ds-link-delete widget-ui-remove', 
									}), 
								]
							}), 
							$('<div/>', {
								class: 'widget-editor',
								append: $('<span/>', {
									class: 'button-process', 
								}),  
							}), 
						], 
					}), 
					$('<div/>', {
						class: 'widget-action', 
						append: [
							$('<a/>', {
								class: 'widget-up', 
								append: $('<i/>', {
									class: 'fa fa-chevron-up', 
								})
							}), 
							$('<a/>', {
								class: 'widget-down', 
								append: $('<i/>', {
									class: 'fa fa-chevron-down', 
								})
							}), 
						]
					})
				], 
			});

			$(ui.item).replaceWith(widgetWith); 

	        $.ajax(ajax_url, {
	            data: { 
	            	action: 'widget_edit_form', 
	            	widget_name: widget_name, 
	            	widget_area: $(widgetWith).closest('.widgets-area').data('area'), 
	            	widget_id: widget_id, 
	            }, 
	        	method: 'POST', 
	            success: function(resp) {
	                var widget_id = $(resp).find('[name="widget_id"]').val();
	                $(widgetWith).find('.widget-editor').addClass('active').html(resp);
	                $(widgetWith).attr('data-loaded', 1); 
	                $(widgetWith).attr('data-widget_id', widget_id); 
	                updateWidgetsData(); 
	            }
	        }); 
		}
	});

	$('.widget-ui[data-draggable="1"]').draggable({ 
		helper: "clone", 
		handle: ".ui-move-handle",
		connectToSortable: '.sortable-ui',
	});

	$('.widgets-area').droppable();
}); 