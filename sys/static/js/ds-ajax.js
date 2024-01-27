
/**
* Разработано для CMS-Social v3
* Project URL:  https://cms-social.ru
*
* Система интервального опроса сервера
* Позволяет регистрировать различные типы уведомлений с гибкой 
* настройкой callback параметров в момент опроса
* И при получении данных от сервера выполнять пользовательскую callback функцию
* 
* Author:       alex-borisi
* E-Mail:       alex-borisi@ya.ru
* Author URL:   https://andryushkin.ru
*/ 

function DS_Ajax_Events() 
{
	this.ds_events = {}; 

	/**
	* $events.setEvents(eventName, {data, success})
	* eventName (string) Название (идентификатор) события 
	* data (function | [string, object, ..]) Данные для отправки на сервер
	* success (function) Функция будет отработана если пришел ответ с результатом
	*/ 

	this.setEvents = function(eventName, eventArgs) {
		let obj = {
			data: true, 
		}

		// Если нет данных для передачи
		eventArgs = Object.assign(obj, eventArgs);

		Object.defineProperty(this.ds_events, eventName, {
			value: {
				eventName : eventName, 
				eventArgs : eventArgs, 
			}, 
			enumerable: true, 
			configurable: true, 
		}); 
	}

	/**
	* Получить все зарегистрированные запросы
	* $events.getEvents()
	* @return object
	*/ 

	this.getEvents = function() {
		return this.ds_events; 
	}

	/**
	* $events.doEvent() 
	* Запускает callback функцию при получении результа
	*/ 

	this.doEvent = function(eventName, eventResult) {
		var objectEvent = Object.getOwnPropertyDescriptor(this.ds_events, eventName); 

		if (typeof objectEvent == 'object') {
			objectEvent.value.eventArgs.success(eventResult);  
		}
	}

	/**
	* $events.delete(eventName) 
	* Удаляет зарегистрированное уведомление
	*/ 

	this.delete = function(eventName) {
		console.log('Event remove: ' + eventName); 
		delete this.ds_events[eventName]; 
	}
}

/**
* Глобальный объект Ajax уведомлений
* $events.setEvents()
* $events.getEvents()
* $events.doEvent()
*/ 

var $events = new DS_Ajax_Events();  

/**
* Система работает в связке с jQuery, поэтому регистрировать собственные обработчики
* лучше всего, когда jQuery был инициализирован. 
*/ 


var ds_user = {
	active: 1, 
	update: Date.now(), 
}

document.addEventListener("mousemove", ds_user_update);
document.addEventListener("click", ds_user_update);
document.addEventListener("touchstart", ds_user_update);
document.addEventListener("keydown", ds_user_update);
document.addEventListener("scroll", ds_user_update);

function ds_user_update() {
	ds_user.update = Date.now(); 
	ds_user.active = 1; 
}

window.onblur = function() { ds_user.active = 0; }
window.onfocus = function () { ds_user.active = 1; }

jQuery(function($) {
	var intervalTime = 1000; 
	if (!$('body').hasClass('logged-in')) {
		intervalTime = 3000; 
	}

	setInterval(function() {
		var events = $events.getEvents(); 

		/**
		* С каждым опросом сервера, формируем данные для отправки на сервер
		*/ 
		var ajax_data_setup = {}; 

		for(let key in events) {
			if (typeof events[key].eventArgs.data == 'function') {
				var data = events[key].eventArgs.data(); 
			} else {
				var data = events[key].eventArgs.data; 
			}

			/** Если callback_data вернет false то данный тип не будет отправлен **/
			if (data !== false) {
				Object.defineProperty(ajax_data_setup, events[key].eventName, {
					value: data, 
					enumerable: true, 
				});			
			}
		}

		var ds_prints = -1;
		if (window.ds_user_prints !== undefined) {
			ds_prints = Date.now() - window.ds_user_prints; 
		} 

		$.ajax({
			type: "POST",
			url: '/ds-ajax/?action=ds_events',
			data: {
				json: JSON.stringify(ajax_data_setup), 
				user: {
					active: ds_user.active, 
					update: (Math.abs(Date.now() - ds_user.update) / 1000), 
					prints: ds_prints, 
					hash: window.ds_hash_prints || '', 
					request: window.location.href, 
				}, 
			},
			dataType: 'json',
			success: function(events) {

				/**
				* Ответ содержит массив данных, с ключом ie равным идентификатору 
				* зарегистрированного типа уведомлений, и event с данными ответа.
				*/ 
				$.each(events, function(ie, event) {
					$events.doEvent(ie, event); 
				});
			}
		}); 
	}, intervalTime); 
}); 