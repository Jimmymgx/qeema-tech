(function () {
	// WebMCP (https://developer.chrome.com/docs/ai/webmcp) declarative
	// attributes are plain HTML attributes with no meaning to browsers that
	// don't implement the proposal yet, so setting them via JS after
	// Elementor renders the form is exactly as safe as writing them into the
	// markup - this just avoids patching Elementor Pro's own form-render code
	// (which a plugin update would overwrite).
	var FIELD_DESCRIPTIONS = {
		name: 'Full name of the person requesting a quote.',
		email: 'Email address to send the quote/response to.',
		phone: 'Phone/WhatsApp number, digits only (the country dial code is chosen separately in the country field).',
		country: 'Country the requester is contacting from, used to select the dialing code shown next to the phone field.',
		services: 'Which Qeematech service the request is about: website design, mobile app development, digital marketing, SEO, e-commerce store design, web hosting, CRM, ODOO ERP, corporate branding, custom web development, content writing, or technical support.',
		project_type: 'Type of project: website, mobile app, online store, custom software, CRM/ERP, or other.',
		message: 'Free-text description of the project or request.'
	};

	function annotate() {
		var form = document.querySelector( '.elementor-form' );
		if ( ! form || form.hasAttribute( 'toolname' ) ) {
			return;
		}
		form.setAttribute( 'toolname', 'submit_contact_request' );
		form.setAttribute( 'tooldescription', 'Submit a project inquiry to Qeematech, a digital agency offering web design, mobile app development, e-commerce, SEO, digital marketing, hosting, CRM, and Odoo ERP implementation, and request a free consultation.' );

		Object.keys( FIELD_DESCRIPTIONS ).forEach( function ( fieldId ) {
			// Elementor Pro names each rendered field form_fields[<custom_id>] -
			// intentionally left as-is rather than renamed, since the same
			// name attribute drives the plugin's own submit handling.
			var field = form.querySelector( '[name="form_fields[' + fieldId + ']"]' );
			if ( field ) {
				field.setAttribute( 'toolparamdescription', FIELD_DESCRIPTIONS[ fieldId ] );
			}
		} );
		// recaptcha_v3 is intentionally left un-annotated - it's an invisible
		// token field an agent has no business filling in.
	}

	document.addEventListener( 'DOMContentLoaded', annotate );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', annotate );
	}
})();
