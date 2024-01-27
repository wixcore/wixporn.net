/**
* Plugin Name: CDN Manager
* Author: ua.lifesheets
*/ 

jQuery(function($) {

	$('.sss').click(function() {
		console.log('Start upload'); 
		$.ajax({
			type: "GET",
			url: '/test.php',
			success: function(data) {
				console.log(data); 
				$('.sss').click(); 
			}, 
			error: function(e) {
				console.log(e); 
			}
		});
	}); 

	$('#move_files_to_local').click(function() {
		$.ajax({
			dataType: "json",
			type: "POST",
			url: window.location,
			data: "move_files_storage=1&cdn_id=" + $(this).attr('data-id'),
			success: function(data) {
				if (data.files) {
					for(var key in data.files) {
						console.log(data.files[key].title); 
					}
				}

				$('.cdn-size-avail').text(data.total_avail); 
				$('.cdn-size-uses').text(data.total_uses); 
				$('.cdn-files-count').text(data.total_storage); 
				$('.cdn-uses-percent').text(data.total_percent + '%'); 
				$('.progress-bar').css('width', data.total_percent + '%'); 

				$('#move_files_to_local').click(); 
			}, 
			error: function(e) {
				console.log(e); 
			}
		});
	}); 

	$('#move_files_to_storage').click(function() {
		$.ajax({
			dataType: "json",
			type: "POST",
			url: window.location,
			data: "move_files_local=1&cdn_id=" + $(this).attr('data-id'),
			success: function(data) {
				if (data.files) {
					for(var key in data.files) {
						console.log(data.files[key].title); 
					}
				}
				$('.cdn-size-avail').text(data.total_avail); 
				$('.cdn-size-uses').text(data.total_uses); 
				$('.cdn-files-count').text(data.total_storage); 
				$('.cdn-uses-percent').text(data.total_percent + '%'); 
				$('.progress-bar').css('width', data.total_percent + '%'); 

				$('#move_files_to_storage').click(); 
			}, 
			error: function(e) {
				console.log(e); 
			}
		});
	}); 

}); 