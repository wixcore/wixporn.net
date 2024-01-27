var keyUploadCurrent = false; 

function maxUploadFileSize() {
	var maxUploadFileSize = document.getElementById('upload_max_size').value || false; 
	return maxUploadFileSize; 
}

var isAdvancedUpload = function() {
  var div = document.createElement('div');
  return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}();

function MediaManager() {
	this.files = new Map(); 
	this.history = new Map(); 

	this.historyAdd = function() {
		var key = $media.history.size; 
		var params = {
			title: $media.params.title, 
			type: $media.params.type, 
			term: $media.params.term, 
			page: $media.params.page, 
		}

		$media.history.set(key, params); 

		var historyNav = $('<div/>'); 

		$media.history.forEach((value, key, map) => {
			$('<div/>', {
				text: value.title,
			}).appendTo(historyNav); 
		});
	}

	this.getSize = function(fileSizeInBytes) {
	    var i = -1;
	    var byteUnits = ['Kb','Mb','Gb','Tb','Pb','Eb','Zb','Yb'];
	    do {
	        fileSizeInBytes = fileSizeInBytes / 1024;
	        i++;
	    } while (fileSizeInBytes > 1024);

	    return Math.max(fileSizeInBytes, 0.1).toFixed(1) + byteUnits[i];
	}

	this.historyGo = function(go) {
		var key = ($media.history.size + go); 
		var params = $media.history.get(key); 
		$media.params = $.extend($media.params, params); 
		$media.history.delete(key); 
		$media.getDataFiles($media);

		if (key === -1) {
			$media.openClose($media);
		}
	}

	this.managerUploadStart = function() {
		var uid = $('.choose-upl').data('uniquie'); 
		if (!uid) return ; 

		var data = {
			term_type: $media.params.type, 
			term_id: $media.params.term || 0, 
		}

		$media.upload({
			file: $media.getFile(uid), 
			fileType: 'files', 
			url: '/ds-ajax/?action=ds_files_upload', 
			data: data, 
			progress: function(percent, e) {
				console.log(percent); 
			}, 
			success: function(data) {
				var json = JSON.parse(data.currentTarget.responseText); 
				var divItem = $media.templateFile(json.file, '1'); 
				$('.choose-upl[data-uniquie="' + uid + '"]').replaceWith(divItem); 
				$media.checked.set(json.file.file_id, json.file); 
				var elemSelected = $media.manager.find('[data-selected]'); 
					elemSelected.attr('data-selected', $media.checked.size); 

				$media.managerUploadStart(); 
			}, 
			error: function(e) {
				console.log(e);
			}
		}); 
	}

	this.getDataFiles = function($media) { 
		var elemContent = $media.manager.find('.choose-manager-ajax'); 
			elemContent.html('Loading..'); 
		var elemSelected = $media.manager.find('[data-selected]'); 

		$.ajax({
			url: '/ds-ajax/',
			dataType: 'json',
			data: {
				action: 'ds_media_manager', 
				type: $media.params.type, 
				term: $media.params.term, 
				page: $media.params.page, 
			},
			success: function(json) {
				elemContent.empty(); 
				$media.manager.find('.choose-manager-panel .title').text(json.title); 

				console.log(json); 

				var pagination = $('<div/>', {
					class: 'choose-nav',
					append: [
						$('<button/>', {
							class: 'choose-nav-prev', 
							html: '&#8249;', 
							click: function() {
								if ($media.params.page > 1) {
									$media.params.page -= 1;
									$media.getDataFiles($media); 					
								}
								return false; 
							}
						}), 
						$('<span/>', {
							class: 'choose-nav-info', 
							text: json.filesPage + ' из ' + json.filesPages, 
						}), 
						$('<button/>', {
							class: 'choose-nav-next', 
							html: '&#8250;', 
							click: function() {
								if ($media.params.page < json.filesPages) {
									$media.historyAdd(); 
									$media.params.page += 1;
									$media.getDataFiles($media); 					
								}
								return false; 
							}
						}), 
					]
				}); 

				$media.manager.find('.choose-pagination').html(pagination).prepend($('<div/>', {
					class: 'choose-upload',
					append: $('<label/>', {
						append: [
							$('<input/>', {
								class: 'choose-input-upload', 
								type: 'file',
								accept: json.accept, 
								multiple: true, 
								change: function() {
									$.each(this.files, function(k, file) {
										var uid = $media.addFile(file); 
										var tmp = $('<a/>', {
											class: 'choose-item-file choose-upl', 
											'data-uniquie': uid, 
											'data-type': json.files_type, 
											'data-checked': 1, 
											append: [
												$('<i/>', {
													class: 'fa fa-refresh fa-spin', 
												}),				
												$('<span/>', {
													class: 'choose-item-title', 
													text: file.name, 
													append: [$('<span/>', {
														class: 'choose-file-size', 
														text: $media.getSize(file.size), 
													})]
												}) 
											], 
										}); 

										$('.choose-list-files').prepend(tmp); 
									});

									$media.managerUploadStart(); 
								}
							}), $('<div/>', { 
								class: 'button',
								append: [$('<i/>', {
									class: 'fa fa-cloud-upload',  
								}), $('<span/>', {
									text: json.labels.upload_file,  
								})], 
							})
						]
					})
				})); 

				if (json.folders.length > 0) {
					var folders = $('<div/>', {
						class: 'choose-list-folders', 
						'data-type': json.files_type, 
					}); 
					json.folders.forEach(function(item) {
						$('<div/>', {
							class: 'choose-item-folder', 
							'data-type': json.files_type, 
							'data-term': item.term_id, 
							append: [
								$('<i/>', {
									class: 'fa fa-folder'
								}), 
								$('<span/>', {
									class: 'choose-item-title', 
									text: item.title, 
								}), 
								$('<span/>', {
									class: 'choose-item-counter', 
									text: item.count, 
								}), 
							], 
							click: function() {
								$media.historyAdd(); 
								$media.params = $.extend($media.params, {
									title: item.title, 
									term: item.term_id, 
								}); 

								$media.getDataFiles($media); 
							}
						}).appendTo(folders); 
					}); 

					elemContent.append(folders); 
				}

				var files = $('<div/>', {
					class: 'choose-list-files', 
					'data-type': json.files_type, 
					'data-empty': json.labels.page_empty, 
				}); 

				if (json.files.length > 0) {
					json.files.forEach(function(item, indx) {
						var divItem = $media.templateFile(item, $media.checked.has(item.file_id) ? '1' : '0'); 
							divItem.appendTo(files); 
					}); 
				}

				elemContent.append(files); 
			}, 
			error: function(e) {
				console.log(e); 
			}
		});
	}

	this.templateFile = function(item, checked) {
		var elemSelected = $media.manager.find('[data-selected]'); 
		var template = $('<a/>', {
			class: 'choose-item-file', 
			'data-type': $media.params.type, 
			'data-term': $media.params.term, 
			'data-checked': checked, 
			append: [
				$('<i/>', {
					class: item.icon, 
				}), 
				$('<span/>', {
					class: 'choose-item-title', 
					text: item.title, 
					append: [$('<span/>', {
						class: 'choose-file-size', 
						text: item.size, 
					})]
				}), 
			], 
			click: function() {
				if ($(this).attr('data-checked') === '0') {
					$(this).attr('data-checked', '1'); 
					$media.checked.set(item.file_id, item); 
				} else {
					$(this).attr('data-checked', '0'); 
					$media.checked.delete(item.file_id); 
				}

				elemSelected.attr('data-selected', $media.checked.size); 
			}
		});

		if (item.thumbnail) {
			template.prepend($('<span/>', {
				class: 'choose-thumbnail', 
				append: $('<img/>', {
					src: item.thumbnail, 
				})
			})); 
		}

		return template; 
	}

	this.open = function(params) {
		$(params.selector).empty(); 

		params = $.extend({
			title: '', 
			page: 1, 
		}, params);

		var $media = $.extend(this, {
			checked: new Map(), 
			params: params, 
		});

		$media.manager = $('<div/>', {
			class: 'choose-manager', 
			'data-history': 1,
			append: $('<div/>', {
				class: 'choose-manager-content', 
				append: [
					$('<div/>', {
						class: 'choose-manager-panel', 
						append: [
							$('<span/>', {
								class: 'choose-back', 
								append: $('<i/>', {
									class: 'fa fa-chevron-left', 
								}), 
								click: function() {
									$media.historyGo(-1); 
								}
							}),
							$('<span/>', {
								class: 'title', 
								text: params.title, 
							}),
							$('<span/>', {
								class: 'choose-checked', 
								'data-selected' : 0, 
								text: 'Выбрано: ', 
							}),
							$('<span/>', {
								class: 'choose-insert', 
								'data-selected' : 0,
								click: function() {
									var array = []; 
									$media.checked.forEach(function(key, item) {
										array.push(key); 
									}); 
									params.onSelected(array); 
									$media.openClose($media); 
								}
							})
						], 
					}), 
					$('<div/>', {
						class: 'choose-manager-ajax',
						text: 'Loading..'
					}), 
					$('<div/>', {
						class: 'choose-pagination',
					}), 
					$('<div/>', {
						class: 'choose-manager-close',
						click: function() {
							$media.openClose($media); 
						}
					})
				]
			})
		}).appendTo(params.selector); 

		$media.getDataFiles($media); 
	}

	this.openClose = function($media) {
		$media.manager.remove(); 
		delete $media; 
	}

	this.getFiles = function() {
		return this.files.entries(); 
	}

	this.getFile = function(uid) {
		return this.files.get(uid); 
	}

	this.upload = function(params) {
		var xhr = new XMLHttpRequest();

		xhr.upload.onprogress = function(e) {
			var percent = e.loaded / e.total * 100;  
			if (typeof params.progress == 'function') {
				params.progress(percent, e); 
			}
		}

		xhr.upload.onerror = function(e) {
			if (typeof params.error == 'function') {
				params.error(e); 
			}
		}

		xhr.onload = function(e) {
			if (typeof params.success == 'function') {
				params.success(e); 
			}
		}

		var form = new FormData();
	    form.append("file", params.file);

	    if (typeof params.data == 'object') {
	    	for(var k in params.data) {
				form.append(k, params.data[k]);
	    	}
	    }
	    
	    xhr.open("POST", params.url, true);
	    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

	    xhr.send(form);
	}

	this.preview = function(uid, file) {
	    var selectorPreview = '.ds-files-list';
	    if (!$(selectorPreview).length) {
	    	return ; 
	    }

	    var ras = file.name.replace(/.*\./, ''); 
	    var errors = []; 
	    var upload_status = 'unloaded'; 

	    var classesAttachment = 'attachment'; 

	    if (file.size > maxUploadFileSize() && maxUploadFileSize() !== false) {
	    	upload_status = 'error'; 
	    	classesAttachment += ' attachment-error '; 
	    	errors.push('The file size is too large'); 
	    }

	    var template = '<div class="' + classesAttachment + '" data-uid="' + uid + '" data-status="' + upload_status + '">';

    	template += '<span class="attachment-image"><i class="ds-icon ds-icon-'+ras+'"></i></span>';
    	template += '<span class="attachment-title">' + file.name + '</b></span>';
    	template += '<div class="progress"><div class="progress-bar"></div></div>';
    	if (errors) {
    		template += '<div class="attachment-error-message">'; 
    		for(var erKey in errors) {
    			template += errors[erKey] + '<br />'; 
    		}
    		template += '</div>'; 
    	}
    	template += '</div>'; 

        $(selectorPreview).append(
        	template
        );
	}

	this.getUniquieID = function() {
        return "yxxy-xyxxyy-xxxyxx-xxxxxx-yyxx".replace(/[xy]/g, function(e) {
            var t = 16 * Math.random() | 0;
            return ("x" == e ? t : 3 & t | 8).toString(16)
        })
    }

    this.addFile = function(file) {
		var uniquie = $media.getUniquieID();
		this.files.set(uniquie, file); 
		this.preview(uniquie, file); 
		return uniquie; 
    }

	return this;
}

var $media = new MediaManager(); 

jQuery(function($) {

	/**
	* Drag n Drop selected files
	*/ 
	if (isAdvancedUpload) {
		var $form = $('.ds-uploader-file');

		if (isAdvancedUpload) {
		  $form.addClass('has-advanced-upload');
		}

		$form.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
			e.preventDefault();
			e.stopPropagation();
		})
		.on('dragover dragenter', function() {
			$form.addClass('is-dragover');
		})
		.on('dragleave dragend drop', function() {
			$form.removeClass('is-dragover');
		})
		.on('drop', function(e) {
			$.each( e.originalEvent.dataTransfer.files, function(k, file) {
				$media.addFile(file); 
			});
		});
	}
	
	/**
	* Form input files
	*/
	$(document).on('change', '.upload-ajax', function() {
		$.each( this.files, function(k, file) { 
			$media.addFile(file); 
		});
	});

	/**
	* Form paste files
	*/
	window.addEventListener('paste', e => {
		$.each( e.clipboardData.files, function(k, file) { 
			$media.addFile(file);
		});
	});

	function startUpload() 
	{
		var uid = $('.attachment[data-status="unloaded"]').attr('data-uid'); 
		if (!uid) return ; 

		$('.attachment[data-status="process"]').attr('data-uid'); 
		$media.upload({
			file: $media.getFile(uid), 
			fileType: 'files', 
			url: window.location, 
			progress: function(percent, e) {
				var progress = $('.attachment[data-uid="' + uid + '"] .progress-bar'); 
				progress.css('width', percent + '%');  
				if (percent == 100) {
					$('.attachment[data-uid="' + uid + '"]').attr('data-status', 'wait'); 
				}
			}, 
			success: function(data) {
				var json = JSON.parse(data.currentTarget.responseText); 
				var file = json.file; 

				if (json.file && !json.file.error) {
					$('.attachment[data-uid="' + uid + '"]').attr('data-status', 'uploaded'); 
					$('.attachment[data-uid="' + uid + '"] .attachment-title').html('<a target="_blank" href="'+ file.full.permalink +'">' + file.title + '</a>'); 
				} else {
					$('.attachment[data-uid="' + uid + '"]').attr('data-status', 'error');
					for(var key in file.error) {
						$('.attachment[data-uid="' + uid + '"] .attachment-error-message').append(file.error[key] + '<br />'); 
					}
				}

				startUpload(); 
			}, 
			error: function(e) {
				$('.attachment[data-uid="'+uid+'"]').attr('data-status', 'error');  
				startUpload(); 
			}
		}); 
	}

	$(document).on("submit", "form.ds-uploader", function() {
		startUpload(); 
	    return false;
	});
}); 