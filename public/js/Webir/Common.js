/**
 * WebiR -- The Web Interface to R
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://escsa.eu/license/webir.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to firma@escsa.pl so we can send you a copy immediately.
 *
 * @category   Webir
 * @package    Webir.Common
 * @copyright  Copyright (c) 2010 ESC S.A. (http://www.escsa.pl/)
 * @license    http://escsa.eu/license/webir.txt     New BSD License
 * @version    $Id$
 */

Ext.ns('Webir.Common');

Ext.data.DataProxy.on('exception', function(proxy, type, action,options,response,args) {
	if(!Ext.isEmpty(response.raw) && !Ext.isEmpty(response.raw.error)) {
		Webir.Common.msgboxFailure(response.raw.error,'Wystąpił błąd');	
	}
});

/**
 * @author Daniel Bojdo <daniel.bojdo@escsa.pl>
 */
Webir.Common.ComboRenderer = function(value, metaData, record, rowIndex, colIndex, store) {
	var editor = this.getCellEditor(rowIndex).field;
	var i = editor.getStore().find(editor.valueField,value);
	return i > -1 ? editor.getStore().getAt(i).get(editor.displayField) : value;
};

/**
 * Checks whether variable is defined.
 * 
 * @param {Mixed} variable
 * @return {Boolean} Zwraca true, jeśli zmienna istnieje, w przeciwnym razie false
 * @type Boolean
 */
Webir.isset = function(variable) {
	return typeof variable !== 'undefined';
};


/**
 * Displays error message window.
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @param {String} Wyświetlana właściwa treść komunikatu błędu
 * @param {String} Tytuł okna zawierającego komunikat błędu
 */
Webir.Common.msgboxFailure = function(message, title) {
	Ext.Msg.show({
		title : title,
		msg : message,
		buttons : Ext.Msg.OK,
		icon : Ext.MessageBox.ERROR
	});
};

/**
 * Displays informational message window.
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 * @param {String} Wyświetlana właściwa treść komunikatu informacyjnego
 * @param {String} Tytuł okna zawierającego komunikat informacyjny
 */
Webir.Common.msgboxInfo = function(message, title) {
	Ext.Msg.show({
		title : title,
		msg : message,
		buttons : Ext.Msg.OK,
		icon : Ext.MessageBox.INFO
	});
};

/**
 * Gets stack trace information on handled browsers (currently supports Firefox only).
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 */
Webir.Common.getStackTrace = function(e) {
	var mode = e.stack ? 'Firefox' : window.opera ? 'Opera' : 'Other';
	switch (mode) {
		case 'Firefox' :
			return e.stack.replace(/^.*?\n/, '').replace(/(?:\n@:0)?\s+$/m, '').replace(/^\(/gm, '{anonymous}(').split("\n");
		default :
			return [];
	}
};

/**
 * Displays error window with client exception information.
 * 
 * @author Jakub Argasiński <jakub.argasinski@escsa.pl>
 */
Webir.Common.exceptionMessage = function(exception) {
	var message = [];
	// Wspólne własności wyjątku
	message.push('<strong>Typ:</strong> ' + exception.name);
	message.push('<strong>Treść:</strong> ' + exception.message);
	// Roszerzenia specyficzne dla różnych przeglądarek
	if (Ext.isDefined(exception.fileName)) {
		message.push('<strong>Plik:</strong> ' + exception.fileName);
	}
	if (Ext.isDefined(exception.lineNumber)) {
		message.push('<strong>Wiersz:</strong> ' + exception.lineNumber);
	}
	if (Ext.isDefined(exception.number)) {
		message.push('<strong>Numer:</strong> ' + exception.number);
	}
	if (Ext.isDefined(exception.description)) {
		message.push('<strong>Opis:</strong> ' + exception.description);
	}
	if (Ext.isDefined(exception.stack)) {
		var stacktrace = Webir.Common.getStackTrace(exception);
		stacktrace = stacktrace.join("\n");
		console.error(stacktrace);
	}

	Webir.Common.msgboxFailure(message.join('<br/>'), 'Wyjątek krytyczny po stronie klienta');

};


/**
 * ExtJS 3.1.1: fixes the scrollbars showing when Ext.Window is displayed.  
 * @return {}
 */
Ext.lib.Dom.getViewportWidth = function() {
	var doc = document;
	return !Ext.isStrict && !Ext.isOpera || Ext.isGecko3 ? doc.body.clientWidth :
	Ext.isIE ? doc.documentElement.clientWidth : self.innerWidth;
}