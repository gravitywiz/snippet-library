/**
 * Gravity Perks // Page Transitions // Scroll to Top for Long Pages
 * https://gravitywiz.com/
 * 
 * Instruction Video: https://www.loom.com/share/74b3371a4feb4d89b87740af76e202f2
 */
gform.addAction( 'gppt_before_transition', function() {
  window.scroll( {
    top: 0,
    left: 0,
    behavior: 'smooth'
  } );
} );
