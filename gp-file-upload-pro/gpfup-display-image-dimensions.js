/**
 * Gravity Perks // File Upload Pro // Display Image Dimensions
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Displays image width/height next to the file size or filename in the file list.
 *
 * Instruction Video: https://www.loom.com/edit/ef7c047ada5641bd937365b3f29600bc
 *
 * Instructions:
 * 1. Install the free Custom JavaScript for Gravity Forms plugin.
 *    Download: https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Paste this snippet into the Custom JavaScript editor.
 */
gform.addAction( 'gpfup_uploader_ready', function ( gpfup ) {
	const store = gpfup.$store;
	if ( !store ) {
		return;
	}

	// Configuration: Update these settings to customize the display of image dimensions.
	const config = {
		displayMode: 'filename', // 'filesize' (2.11MB | 1024x1024 px) or 'filename' (Filename.jpg (1024x1024 px))
		enableTooltip: false,    // Show original dimensions on hover when image has been cropped
	};

	if ( !document.getElementById( 'gpfup-dimensions-style' ) ) {
		const style = document.createElement( 'style' );
		style.id = 'gpfup-dimensions-style';
		style.textContent = `
			.gpfup__dims-sep { margin: 0 5px; }
			.gpfup__dims-filename { color: #999; }
		`;
		document.head.appendChild( style );
	}

	function getDimensions( source ) {
		if ( !source?.width || !source?.height ) {
			return null;
		}

		return {
			width: Math.round( source.width ),
			height: Math.round( source.height ),
		};
	}

	function getAllFiles() {
		return store.getters?.allFiles || store.state.files || [];
	}

	function getFileInfoElement( fileId ) {
		const root = gpfup.vm?.$el || document;
		const fileEl = root.querySelector( `[data-file-id="${ fileId }"]` );
		return fileEl?.querySelector( '.gpfup__file-info' ) || null;
	}

	async function renderDimensions( file ) {
		if ( !file?.id || !file.type?.startsWith( 'image/' ) ) {
			return;
		}

		const editor = store.state.editor || {};
		const cropped = getDimensions( editor.cropperResults?.[ file.id ]?.coords );
		let original = getDimensions( editor.originals?.[ file.id ]?.size );

		if ( !original && store.state.storage?.getOriginal ) {
			const stored = await store.state.storage.getOriginal( file.id );
			original = getDimensions( stored?.size );
		}

		const display = cropped || original;
		if ( !display ) {
			return;
		}

		const infoEl = getFileInfoElement( file.id );
		if ( !infoEl ) {
			return;
		}

		const targetSelector =
			config.displayMode === 'filename'
				? '.gpfup__filename'
				: '.gpfup__filesize';

		const targetEl = infoEl.querySelector( targetSelector );
		if ( !targetEl ) {
			return;
		}

		if ( targetEl.dataset.gpfupDimsRendered === '1' ) {
			return;
		}

		const dimText = `${ display.width }x${ display.height } px`;

		let tooltip = '';
		if (
			config.enableTooltip &&
			cropped &&
			original &&
			( cropped.width !== original.width || cropped.height !== original.height )
		) {
			tooltip = `Original: ${ original.width }x${ original.height } px`;
		}

		const titleAttr = tooltip ? ` title="${ tooltip }"` : '';

		if ( !targetEl.dataset.gpfupOriginalHtml ) {
			targetEl.dataset.gpfupOriginalHtml = targetEl.innerHTML;
		}

		targetEl.innerHTML =
			config.displayMode === 'filename'
				? `${ targetEl.dataset.gpfupOriginalHtml } <span class="gpfup__dims-filename"${ titleAttr }>( ${ dimText } )</span>`
				: `${ targetEl.dataset.gpfupOriginalHtml }<span class="gpfup__dims-sep"> | </span><span class="gpfup__dims"${ titleAttr }>${ dimText }</span>`;

		targetEl.dataset.gpfupDimsRendered = '1';
	}

	function updateAll() {
		getAllFiles().forEach( renderDimensions );
	}

	updateAll();

	store.subscribe( function ( mutation ) {
		switch ( mutation.type ) {
			case 'ADD_FILE':
			case 'REPLACE_FILE':
			case 'STORE_ORIGINAL':
			case 'STORE_CROPPER_RESULTS':
				updateAll();
				break;
		}
	} );
} );
