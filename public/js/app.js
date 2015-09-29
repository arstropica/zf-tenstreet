/**
 * Application JS
 */

$(function() {
	$('#leadimport .import-fields SELECT').on('change', function(e) {
		if ($(this).val() !== "") {
			$(this).addClass('custom');
		} else {
			$(this).removeClass('custom match');
		}
	});
	$('.table-collapse > TBODY > TR.collapsed').hover(function() {
		$(this).find('TD').attr('title', '[+] Expand');
	}, function() {
		$(this).find('TD').removeAttr('title');
	});
	$('.table-collapse > TBODY > TR:not(.collapsed)').hover(function() {
		$(this).find('TD').attr('title', '[-] Collapse');
	}, function() {
		$(this).find('TD').removeAttr('title');
	});
});