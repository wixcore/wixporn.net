
function tag(tagBefore, tagAfter, uid) {
	$('#ds_editor_modal').fadeOut(200).attr('data-active', false).html(''); 
	if ((document.selection)) {
		document.message.msg.focus();
		document.message.document.selection.createRange().text = tagBefore + document.message.document.selection.createRange().text + tagAfter;
	} 

	else if (document.getElementById(uid).selectionStart != undefined) {
		var element = document.getElementById(uid);
		var str = element.value;
		var start = element.selectionStart;
		var end = element.selectionEnd;
		var length = element.selectionEnd - element.selectionStart;
		element.value = str.substr(0, start) + tagBefore + str.substr(start, length) + tagAfter + str.substr(start + length);
		element.selectionStart = end + tagBefore.length;
		element.selectionEnd = end + tagBefore.length;
		element.focus(); 
	} else {
		document.getElementById(uid).value += tagBefore + tagAfter;
	}
	document.getElementById(uid).focus();
}

function emoji(uid, symbol) {
	$('#ds_editor_modal').fadeOut(200).attr('data-active', false).html(''); 
	if ((document.selection)) {
		document.message.msg.focus();
		document.message.document.selection.createRange().text = document.message.document.selection.createRange().text + symbol;
	} else if (document.getElementById(uid).selectionStart != undefined) {
		var element = document.getElementById(uid);
		var str = element.value;
		var start = element.selectionStart;
		var end = element.selectionEnd;
		var length = element.selectionEnd - element.selectionStart;
		element.value = str.substr(0, start) + str.substr(start, length) + symbol + str.substr(start + length);
		element.selectionStart = end + symbol.length;
		element.selectionEnd = end + symbol.length;
		element.focus(); 
	} else {
		document.getElementById(uid).value += symbol;
	}

	document.getElementById(uid).focus();

	return false; 
}

function colorpicker(uid, htmlTag) 
{
	var area = $('#ds_editor_modal'); 

	if (area.attr('data-active') == htmlTag + '-colorpicker') {
		area.attr('data-active', false).html('').fadeOut(200);
		return ; 
	} else if (area.attr('data-active') != htmlTag + '-colorpicker') {
		area.attr('data-active', false).html('').fadeOut(200); 
	}

	var htmlTag = htmlTag; 
	var htmlUid = uid; 

	area.attr('data-active', htmlTag + '-colorpicker').html('<input class="ds-colorpicker-' + uid + '" />'+
					'<div class="ds-colorpicker-toggle">Другой цвет</div>').fadeIn(200);

	$(".ds-colorpicker-" + uid).spectrum({
		preferredFormat: "hex",
		clickoutFiresChange: false,
	    flat: true,
	    showInput: true,
	    theme: "ds-theme", 
	    allowEmpty: false, 
	    showPalette: true,
	    showInitial: true, 
	    togglePaletteOnly: false,
	    togglePaletteMoreText: 'Палитра',
	    togglePaletteLessText: 'Скрыть',
        cancelText: "Отмена",
        chooseText: "Вставить",
        clearText: "Очистить выбранный цвет",
        noColorSelectedText: "Цвет не выбран",
	    //color: 'blanchedalmond',
	    palette: [
	        ["#000","#444","#666","#999","#ccc","#eee","#f3f3f3","#fff"],
	        ["#f00","#f90","#ff0","#0f0","#0ff","#00f","#90f","#f0f"],
	        ["#ea9999","#f9cb9c","#ffe599","#b6d7a8","#a2c4c9","#9fc5e8","#b4a7d6","#d5a6bd"],
	        ["#900","#b45f06","#bf9000","#38761d","#134f5c","#0b5394","#351c75","#741b47"]
	    ], 

	    change: function(color) {
	    	if (color) {
	    		tag('[' + htmlTag + '=' + color.toHexString() + ']', '[/' + htmlTag + ']', htmlUid); 
	    		$(".ds-colorpicker-" + uid).spectrum('destroy');
	    	}
		}
	});

	$('.ds-colorpicker-toggle').click(function() {
		if ($(".ds-colorpicker-" + uid).hasClass('ds-colorpicker-active')) {
			$(".ds-colorpicker-" + uid).removeClass('ds-colorpicker-active'); 
		} else {
			$(".ds-colorpicker-" + uid).addClass('ds-colorpicker-active')
		}
	}); 
}