$(function() {
	
	$('.round').corner("round 8px");
	
	var shadow_css_attrs = ["-moz-box-shadow", "-webkit-box-shadow", "box-shadow"];
	var i = 0;
	for (i=0; i < shadow_css_attrs.length; i++) {
		$('.shadow').css(shadow_css_attrs[i], '3px 3px 16px #666');
	}
	
});

(function() {
	if (!("console" in window) || !("firebug" in console)) {
		var names = ["log", "debug", "info", "warn", "error", "assert", "dir",
				"dirxml", "group", "groupEnd", "time", "timeEnd", "count",
				"trace", "profile", "profileEnd"];

		window.console = {};
		for (var i = 0; i < names.length; ++i)
			window.console[names[i]] = function() {
			};
	}
}

)();

$(function() {
	$("#nav").attr('role', 'menu');
	$("#nav").attr('aria-live','polite'); 
	$("#nav").attr('aria-atomic','false'); 
	$("#nav").attr('relevant','all');
	$("#nav").attr('aria-label','Menu');
	$("#Main").attr('role', 'document');
	$("#Main").attr('aria-live','polite'); 
	$("#Main").attr('aria-atomic','false');
	$("#Main").attr('relevant','all');
	$("#Main").attr('aria-label','Obszar roboczy aplikacji');	
});
