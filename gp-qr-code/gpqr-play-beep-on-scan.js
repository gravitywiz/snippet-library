/**
 * Gravity Perks // GP QR Code // Automatically Play Beep Sound on Successful Scan
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 *
 * When using the QR scanner, automatically play beep after a successful scan.
 * 
 * Instruction Video: https://www.loom.com/share/7f413231517b423eba3ccc1bf3055b40
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-code-chest/
 */
function playBeep() {
	var context    = new( window.AudioContext || window.webkitAudioContext )();
	var oscillator = context.createOscillator();
	var gainNode   = context.createGain();

	oscillator.type = "square"; // Square wave for a more scanner-like sound.
	oscillator.frequency.setValueAtTime( 800, context.currentTime ); // Frequency in Hz.
	gainNode.gain.setValueAtTime( 0.5, context.currentTime ); // Adjust volume.

	oscillator.connect( gainNode );
	gainNode.connect( context.destination );

	oscillator.start();

	// Beep duration in milliseconds.
	setTimeout( () => {
		oscillator.stop();
	}, 100 );
}

gform.addAction( 'gpqr_on_scan_success', function( decodedText, decodedResult, gpqr ) {
	playBeep();
} );
