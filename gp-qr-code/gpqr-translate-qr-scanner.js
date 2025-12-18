/**
 * Gravity Perks // GP QR Code // Translate QR Scanner Interface to Spanish
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
( function( $ ) {

	const translations = new Map([
		["Request Camera Permissions", "Solicitar permisos de cámara"],
		["Requesting camera permissions...", "Solicitando permisos de cámara..."],
		["No camera found", "No se encontró ninguna cámara"],
		["Stop Scanning", "Detener escaneo"],
		["Start Scanning", "Iniciar escaneo"],
		["Scan an Image File", "Escanear un archivo de imagen"],
		["Scan using camera directly", "Escanear usando la cámara directamente"],
		["Choose Image", "Elegir imagen"],
		["Choose Another", "Elegir otra"],
		["No image choosen", "Ninguna imagen seleccionada"],
		["Choose Image - No image choosen", "Elegir imagen - Ninguna imagen seleccionada"],
		["Or drop an image to scan", "O arrastra una imagen para escanear"],
		["Camera based scan", "Escaneo basado en cámara"],
		["File based scan", "Escaneo basado en archivo"],
		["Powered by ", "Desarrollado por "],
		["Report issues", "Informar de problemas"],
		["Camera access is only supported in secure context like https or localhost.", "El acceso a la cámara solo está disponible en un entorno seguro como HTTPS o localhost."]
	]);

	/**
	 * Translate text inside a node
	 */
	function translateNode( node ) {

		// Text nodes
		if ( node.nodeType === Node.TEXT_NODE ) {
			const text = node.nodeValue.trim();
			if ( translations.has( text ) ) {
				node.nodeValue = node.nodeValue.replace( text, translations.get( text ) );
			}
			return;
		}

		if ( node.nodeType !== Node.ELEMENT_NODE ) {
			return;
		}

		// Translate common attributes
		["placeholder", "aria-label", "title", "value"].forEach( attr => {
			if ( node.hasAttribute && node.hasAttribute( attr ) ) {
				const value = node.getAttribute( attr );
				if ( translations.has( value ) ) {
					node.setAttribute( attr, translations.get( value ) );
				}
			}
		});

		// Translate direct text content (buttons, spans, divs)
		if ( node.childNodes && node.childNodes.length ) {
			node.childNodes.forEach( child => translateNode( child ) );
		}
	}

	/**
	 * Run translation on existing DOM
	 */
	function translateExistingDOM() {
		translateNode( document.body );
	}

	/**
	 * Observe dynamically added elements
	 */
	function observeDOM() {
		const observer = new MutationObserver( mutations => {
			mutations.forEach( mutation => {
				mutation.addedNodes.forEach( node => translateNode( node ) );
			});
		});

		observer.observe( document.body, {
			childList: true,
			subtree: true
		});
	}

	/**
	 * Init (works for Gravity Forms + dynamic popups)
	 */
	function initTranslator() {
		translateExistingDOM();
		observeDOM();
	}

	initTranslator();

}( jQuery ));
