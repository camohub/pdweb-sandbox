
////////////////////////////////////////////////////////////////////////////////////////
// NETTE.AJAX.JS //////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

$.nette.init();

////////////////////////////////////////////////////////////////////////////////////////
// LIVE FORM VALIDATION ///////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

// Actually I dont know if I need whole live-form-validation code below. So try to use this simple form.
LiveForm.setupHandlers = function( el ) { }
// Necessary to switch off onchange/onblur validation and switch on submit validation.
// I dont know why but this code can not be in mainInit function.
/*LiveForm.setupHandlers = function( el )
{
	if ( this.hasClass( el, this.options.disableLiveValidationClass ) )
		return;

	// Check if element was already initialized
	if ( el.getAttribute( "data-lfv-initialized" ) )
		return;

	// Remember we initialized this element so we won't do it again
	el.setAttribute( 'data-lfv-initialized', 'true' );

	var handler = function( event )
	{
		event = event || window.event;
		Nette.validateControl( event.target ? event.target : event.srcElement );
	};

	var self = this;

	Nette.addEvent( el, "submit", handler );
	//Nette.addEvent(el, "change", handler);
	//Nette.addEvent(el, "blur", handler);
	Nette.addEvent( el, "keydown", function( event )
	{
		if ( ! self.isSpecialKey( event.which ) && (self.options.wait === false || self.options.wait >= 200) )
		{
			// Hide validation span tag.
			self.removeClass( self.getGroupElement( this ), self.options.controlErrorClass );
			self.removeClass( self.getGroupElement( this ), self.options.controlValidClass );

			var messageEl = self.getMessageElement( this );
			messageEl.innerHTML = '';
			messageEl.className = '';

			// Cancel timeout to run validation handler
			if ( self.timeout )
			{
				clearTimeout( self.timeout );
			}
		}
	} );
	Nette.addEvent( el, "keyup", function( event )
	{
		if ( self.options.wait !== false )
		{
			event = event || window.event;
			if ( event.keyCode !== 9 )  // 9 == Tab
			{
				if ( self.timeout ) clearTimeout( self.timeout );
				self.timeout = setTimeout( function()
				{
					handler( event );
				}, self.options.wait );
			}
		}
	} );
}*/

function mainInit()
{

}

window.addEventListener ? window.addEventListener( 'load', mainInit ) : window.attachEvent( 'onload', mainInit );

