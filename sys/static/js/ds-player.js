var AudioPlayer = new Audio(); 
var AudioCurrentHash = ""; 
var AudioCurrentUniquie = ""; 
var AudioRepeat = false; 
var AudioShuffle = false; 
window.pageTitle = document.title; 

function getRandomInt(max) {
  return Math.floor(Math.random() * Math.floor(max));
}

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function removeCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function setPlayerSeeked(e) {
	if (!AudioPlayer.currentTime) {
		return ;
	}

    var event = event || window.event;
    var position = event.offsetX * 100 / $(e).width();
    var seek = AudioPlayer.duration / 100 * position;
    AudioPlayer.currentTime = seek;
}

function getTimeString(sec) 
{
	if (!sec) return '0:00'; 

	sec = Math.floor(sec); 

	var m = Math.floor(sec / 60);
	sec -= (m * 60); 

	if (sec < 10) sec = '0' + sec;
	var strtime = m + ':' + sec; 

	return strtime; 
}

function AudioPlayerPause() {
	$('.dpl').removeClass('ds-playing'); 
	document.title = window.pageTitle; 
}

function AudioPlayerStop() {
	$('.dpl-progress-bar').css('width', '0%'); 
	$('.dpl-progress-loaded').css('width', '0%'); 
	$('.dpl').removeClass('ds-playing'); 
	$('.dpl-time').text('0:00'); 
	document.title = window.pageTitle; 
}

jQuery(function($) {

	// Play audio in click Progess bar
	$(document).on('click', '.dpl-progress', function() {
		var parent = $(this).parents('.dpl');
		if (!parent.hasClass('ds-playing')) {
			$(parent).find('.dpl-toggle').click(); 
		}
	});

	// Repeat audio
	$(document).on('click', '.dpl-repeat', function() {
		var parent = $(this).closest('.dpl');
		var repeat = parent.attr('data-repeat') || '0'; 

		if (repeat == '0') {
			parent.attr('data-repeat', 1); 
			AudioRepeat = true; 
		} else {
			parent.attr('data-repeat', 0); 
			AudioRepeat = false; 
		}
	});

	// Repeat audio
	$(document).on('click', '.dpl-shuffle', function() {
		var parent = $(this).closest('.dpl');
		var shuffle = parent.attr('data-shuffle') || '0'; 

		if (shuffle == '0') {
			parent.attr('data-shuffle', 1); 
			AudioShuffle = true; 
		} else {
			parent.attr('data-shuffle', 0); 
			AudioShuffle = false; 
		}
	});

	// Toggle Player
	$(document).on('click', '.dpl-toggle', function() {
		var parent = $(this).parents('.dpl');
		var hash = parent.attr('data-hash'); 
		var uniquie = parent.attr('data-uniquie'); 
		var src = parent.attr('data-src'); 

		if (hash != AudioCurrentHash) {
			AudioPlayerStop(); 
			AudioPlayer.src = src; 
			AudioPlayer.play(); 

			if (AudioCurrentHash == "") {
				var currentPlay = getCookie('playerData'); 

				if (currentPlay) {
					var data = JSON.parse(currentPlay); 
					if (hash == data.hash) {
						AudioPlayer.currentTime = data.currentTime; 
					}

					if (data.volume) {
						AudioPlayer.volume = data.volume; 
					}
				}
			}

			AudioCurrentHash = hash; 
			AudioCurrentUniquie = uniquie; 
			parent.addClass('player-preload'); 

			document.title = parent.attr('data-title'); 
		
			$('.dpl[data-god="1"]').attr({
				'data-hash': hash, 
			}).find('.dpl-title').text(parent.attr('data-title')); 

			$(".dpl").clone().appendTo(".player-playlist");
		}

		else if (!AudioPlayer.paused) {
			AudioPlayer.pause(); 
		} else {
			AudioPlayer.play(); 
		}
	}); 

	// Change volume player
	$(document).on('click', '.dpl-volume', function(e) {
	    var event = event || window.event;
	    var X = event.offsetX; 
	    var width = $(this).width(); 
	    var volume = X * 100 / width; 

	    if (volume >= 95) {
	    	volume = 100; 
	    }
	    if (volume <= 5) {
	    	volume = 0; 
	    }

	    AudioPlayer.volume = (volume / 100); 
	    $('.dpl-volume-bar').css('width', volume + '%'); 
	}); 

	// Progress Bar and Timing
	AudioPlayer.addEventListener('timeupdate', function () {

		var players = $(document).find('.dpl[data-hash="' + AudioCurrentHash + '"]'); 
		players.each(function(i, p) {
			if (!$(p).hasClass('ds-playing')) {
				$(p).addClass('ds-playing')
			}
		})

		setCookie('playerData', JSON.stringify({
			hash: AudioCurrentHash, 
			currentTime: AudioPlayer.currentTime, 
			volume: AudioPlayer.volume, 
		}), 1); 

		// Time string playing 
		var curtime = getTimeString(AudioPlayer.currentTime);
		$('.dpl[data-hash="' + AudioCurrentHash + '"] .dpl-time').text(curtime);

		// Progress Playing audio 
		var progress = 100 * AudioPlayer.currentTime / AudioPlayer.duration; 
		$('.dpl[data-hash="' + AudioCurrentHash + '"] .dpl-progress-bar').css('width', progress + '%'); 

		// Buffer Loaded 
		if (AudioPlayer.buffered.length) {
			var buffered = 100 * AudioPlayer.buffered.end(0) / AudioPlayer.duration; 
			$('.dpl[data-hash="' + AudioCurrentHash + '"] .dpl-progress-loaded').css('width', buffered + '%'); 			
		}
	});

	// Playing Player
	AudioPlayer.addEventListener('playing', function () {
		var parent = $('.dpl[data-hash="' + AudioCurrentHash + '"]');
		var hash = parent.attr('data-hash'); 
		parent.addClass('ds-playing').removeClass('player-preload'); 
	});

	// Pause Player
	AudioPlayer.addEventListener('pause', function () {
		AudioPlayerPause(); 
	});

	// Volume Change
	AudioPlayer.addEventListener('volumechange', function() {
		var volume = AudioPlayer.volume; 
		$('.dpl-volume-bar').css('width', (volume * 100) + '%'); 
	});

	// Ended audio 
	AudioPlayer.addEventListener('ended', function () {
		AudioPlayerStop(); 

		// Repeater
		if (AudioRepeat == true) {
			setTimeout(function() {
				AudioPlayer.play(); 
			}, 1000); 
			return ;
		}

		// Shuffle
		if (AudioShuffle == true) {
			var playlist = $(document).find('.dpl'); 
			var randomInt = getRandomInt(playlist.length); 
				playlist.eq(randomInt).find('.dpl-toggle').click(); 

			return ;
		}

		var is_current = false; 
		$(document).find('.dpl').each(function(index, elem) {
			var hash = $(elem).attr('data-hash');
			var uniquie = $(elem).attr('data-uniquie');

			if (is_current) {
				$('.dpl[data-uniquie="' + uniquie + '"]').find('.dpl-toggle').click(); 
				is_current = false; 
			}
			
			else if (uniquie == AudioCurrentUniquie) {
				is_current = true; 
			}
		}); 
	});
}); 