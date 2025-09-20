/**
 * Gravity Perks // Nested Forms // Duplicate Child Entry Multiple Times
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Prompts for a quantity and runs the duplication that many times.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Configure the snippet's fieldId, maxCopies and prompt
 */
(function( $ ) {

	// Configuration
	var CONFIG = {
		targets: [
			{
				fieldId: 1,      // Nested Form Field ID.
				// maxCopies: 5, // Optional cap per duplicate action.
				// prompt: '',   // Override the default prompt.
			},
		],
	};

	function init( gpnf ) {
		var config;

		// Match the active GPNestedForms instance to the configured targets.
		for ( var i = 0; i < CONFIG.targets.length; i++ ) {
			if ( CONFIG.targets[ i ].fieldId === gpnf.fieldId && matchesForm( gpnf ) ) {
				config = CONFIG.targets[ i ];
				break;
			}
		}

		if ( ! config || gpnf._multiDuplicateReady ) {
			return;
		}

		gpnf._multiDuplicateReady = true;

		// Wrap GPNF's duplicate endpoint in a promise so we can queue requests sequentially.
		var duplicateOnce = (function() {
			var nonce = window.GPNFData && window.GPNFData.nonces ? window.GPNFData.nonces.duplicateEntry : '';

			return function( entryId ) {
				return $.post( gpnf.ajaxUrl, {
					action: 'gpnf_duplicate_entry',
					nonce: nonce,
					gpnf_entry_id: entryId,
					gpnf_parent_form_id: gpnf.formId,
					gpnf_nested_form_field_id: gpnf.fieldId,
					gpnf_context: gpnf.ajaxContext
				} ).then( function( response ) {
					if ( ! response || ! response.success ) {
						return $.Deferred().reject( response && response.data ? response.data : 'Unable to duplicate entry.' );
					}

					if ( window.GPNestedForms && typeof window.GPNestedForms.loadEntry === 'function' ) {
						window.GPNestedForms.loadEntry( response.data );
					}

					if ( window.gform && typeof window.gform.doAction === 'function' ) {
						window.gform.doAction( 'gpnf_post_duplicate_entry', response.data.entry, response );
					}

					return response;
				} );
			};
		})();

		gpnf.duplicateEntry = function( entryId, $trigger ) {

			var message = config.prompt || 'How many times should this entry be duplicated?';
			var input = window.prompt( message, '1' );

			if ( input === null ) {
				return;
			}

			var copies = parseInt( input, 10 );

			if ( isNaN( copies ) || copies < 1 ) {
				copies = 1;
			}

			if ( typeof config.maxCopies === 'number' ) {
				copies = Math.min( copies, config.maxCopies );
			}

			var max = gpnf.entryLimitMax;

			if ( window.gform && gform.applyFilters ) {
				max = gform.applyFilters( 'gpnf_entry_limit_max', max, gpnf.formId, gpnf.fieldId, gpnf );
			}

			if ( max !== '' && max != null ) {
				max = parseInt( max, 10 );

				if ( ! isNaN( max ) ) {
					var current = gpnf.viewModel && gpnf.viewModel.entries ? gpnf.viewModel.entries().length : 0;
					copies = Math.min( copies, Math.max( max - current, 0 ) );
				}
			}

			if ( copies < 1 ) {
				return;
			}

			var disableTarget = $trigger && typeof $trigger.prop === 'function' ? $trigger : null;

			if ( disableTarget ) {
				disableTarget.prop( 'disabled', true );
			}

			var chain = $.Deferred().resolve();
			for ( var i = 0; i < copies; i++ ) {
				chain = chain.then( function() {
					return duplicateOnce( entryId );
				} );
			}

			chain.fail( function( message ) {
				if ( message ) {
					window.alert( message );
				}
			} ).always( function() {
				if ( disableTarget ) {
					disableTarget.prop( 'disabled', false );
				}
			} );
		};
	}

	function matchesForm( gpnf ) {
		if ( typeof gpnf.formId !== 'number' ) {
			return false;
		}

		if ( typeof GFFORMID !== 'undefined' ) {
			var currentFormId = parseInt( GFFORMID, 10 );

			if ( ! isNaN( currentFormId ) ) {
				return gpnf.formId === currentFormId;
			}
		}

		return true;
	}

	if ( window.gform && gform.addAction ) {
		gform.addAction( 'gpnf_session_initialized', init );
	}

	for ( var key in window ) {
		if ( key.indexOf( 'GPNestedForms_' ) === 0 ) {
			init( window[ key ] );
		}
	}

})( window.jQuery );
