$(function() {
	$('#Main').corner("round 8px");
	$('.main > .nav > li > a').corner("round 5px");
	$('#Main').addClass('shadow');
	
	$('a.result-show-more').toggle(
			function() {
				$('.result-more').show();
				$(this).attr('originaltext', $(this).text());
				$(this).text('Ukryj szczegóły...');
				return true;
			},
			function() {
				$('.result-more').hide();
				$(this).text($(this).attr('originaltext'));
				$(this).removeAttr('originaltext');
				return true;
			}
	);
	
	$('#debug-raw-show').toggle(
			function() {
				$('#debug-raw').show();
				$(this).attr('originaltext', $(this).text());
				$(this).text('Ukryj surowe wyjście R');
			},
			function() {
				$('#debug-raw').hide();
				$(this).text($(this).attr('originaltext'));
				$(this).removeAttr('originaltext');
			}
	);
	
});