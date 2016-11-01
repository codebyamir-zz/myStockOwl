(function(){
    'use strict';
/*
	// Tabbing functionality	
	$('.nav a').on("click", function(e)
	{
		// Prevent default action
		e.preventDefault();
	
		console.log('Nav link event fired');
		$('.nav').find('.active').removeClass('active');
		$(this).parent().addClass('active');

		// Load file specific by link id attribute
        $('#content').load($(this).attr('id') + '.html');
	});

	$('#main').addClass('active');
    $('#content').load('main.html');
*/

	// Footer
	$('footer').html("myStockOwl &copy; " + new Date().getFullYear());

}());

