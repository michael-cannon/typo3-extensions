var rgsnSlide;

window.addEvent('domready', function() {

rgsnSlide = new Fx.Slide('rgsn').hide();

$('rgsnsendlink').addEvent('click', function(e){
	e = new Event(e);
	rgsnSlide.toggle();
	e.stop();
});



$('rgsnform').addEvent('submit', function(e) {
	/**
	 * Prevent the submit event
	 */
	new Event(e).stop();
 
	/**
	 * This empties the log and shows the spinning indicator
	 */
	var log = $('log').empty().addClass('ajax-loading');
 
	/**
	 * send takes care of encoding and returns the Ajax instance.
	 * onComplete removes the spinner from the log.
	 */
	this.send({
		update: log,
		onComplete: function() {
			log.removeClass('ajax-loading');
			//rgsnSlide.toggle();
		}
	});
});

});


